<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Empresa;
use App\Models\CategoriaProduto;
use App\Models\TamanhoPizza;
use App\Models\PadraoTributacaoProduto;
use App\Models\ProdutoPizzaValor;
use App\Models\MovimentacaoProduto;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use App\Imports\ProdutoImport;
use App\Models\Marca;
use App\Models\GaleriaProduto;
use App\Models\ProdutoVariacao;
use App\Models\VariacaoModelo;
use App\Models\ListaPreco;
use App\Models\ItemListaPreco;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\MercadoLivreConfig;

class ProdutoController extends Controller
{

    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $tipo = $request->tipo;
        $data = Produto::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->when(!empty($request->codigo_barras), function ($q) use ($request) {
            return $q->where('codigo_barras', 'LIKE', "%$request->codigo_barras%");
        })
        ->when(!empty($tipo), function ($q) use ($tipo) {
            if($tipo == 'composto'){
                return $q->where('composto', 1);
            }
            if($tipo == 'variavel'){
                return $q->where('variacao_modelo_id', '!=', null);
            }
        })
        ->paginate(env("PAGINACAO"));
        return view('produtos.index', compact('data'));
    }

    public function create(Request $request)
    {
        $empresa = Empresa::findOrFail(request()->empresa_id);

        $listaCTSCSOSN = Produto::listaCSOSN();
        if ($empresa->tributacao == 'Regime Normal') {
            $listaCTSCSOSN = Produto::listaCST();
        }

        $padroes = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->get();
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();
        $cardapio = 0;
        if (isset($request->cardapio)) {
            $cardapio = 1;
        }

        $delivery = 0;
        if (isset($request->delivery)) {
            $delivery = 1;
        }

        $ecommerce = 0;
        if (isset($request->ecommerce)) {
            $ecommerce = 1;
        }

        $mercadolivre = 0;
        if (isset($request->mercadolivre)) {
            $mercadolivre = 1;
        }
        $marcas = Marca::where('empresa_id', request()->empresa_id)->get();

        $variacoes = VariacaoModelo::where('empresa_id', $request->empresa_id)
        ->where('status', 1)->get();

        $padraoTributacao = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->where('padrao', 1)
        ->first();

        $configMercadoLivre = MercadoLivreConfig::where('empresa_id', $request->empresa_id)
        ->first();
        return view('produtos.create', 
            compact('listaCTSCSOSN', 'padroes', 'categorias', 'cardapio', 'marcas', 'delivery', 'variacoes', 
                'padraoTributacao', 'ecommerce', 'configMercadoLivre', 'mercadolivre'));
    }

    public function edit(Request $request, $id)
    {
        $item = Produto::findOrFail($id);
        __validaObjetoEmpresa($item);
        $empresa = Empresa::findOrFail(request()->empresa_id);

        $listaCTSCSOSN = Produto::listaCSOSN();
        if ($empresa->tributacao == 'Regime Normal') {
            $listaCTSCSOSN = Produto::listaCST();
        }
        $padroes = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->get();
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();
        $cardapio = 0;
        if (isset($request->cardapio)) {
            $cardapio = 1;
        }

        $delivery = 0;
        if (isset($request->delivery)) {
            $delivery = 1;
        }

        $ecommerce = 0;
        if (isset($request->ecommerce)) {
            $ecommerce = 1;
        }

        $mercadolivre = 0;
        if (isset($request->mercadolivre)) {
            $mercadolivre = 1;
        }

        $marcas = Marca::where('empresa_id', request()->empresa_id)->get();
        $variacoes = VariacaoModelo::where('empresa_id', $request->empresa_id)
        ->where('status', 1)->get();

        $configMercadoLivre = MercadoLivreConfig::where('empresa_id', $request->empresa_id)
        ->first();
        return view('produtos.edit', 
            compact('item', 'listaCTSCSOSN', 'padroes', 'categorias', 'cardapio', 'marcas', 'delivery', 'variacoes',
                'ecommerce', 'mercadolivre', 'configMercadoLivre'));
    }

    public function store(Request $request)
    {
        // dd($request);
        $this->__validate($request);
        $produto = null;
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/produtos');
            }

            $request->merge([
                'valor_unitario' => __convert_value_bd($request->valor_unitario),
                'valor_compra' => $request->valor_compra ? __convert_value_bd($request->valor_compra) : 0,
                'imagem' => $file_name,
                'codigo_anp' => $request->codigo_anp ?? '',
                'perc_glp' => $request->perc_glp ? __convert_value_bd($request->perc_glp) : 0,
                'perc_gnn' => $request->perc_gnn ? __convert_value_bd($request->perc_gnn) : 0,
                'perc_gni' => $request->perc_gni ? __convert_value_bd($request->perc_gni) : 0,
                'valor_partida' => $request->valor_partida ? __convert_value_bd($request->valor_partida) : 0,
                'unidade_tributavel' => $request->unidade_tributavel ?? '',
                'quantidade_tributavel' => $request->quantidade_tributavel ? __convert_value_bd($request->quantidade_tributavel) : 0,
                'adRemICMSRet' => $request->adRemICMSRet ? __convert_value_bd($request->adRemICMSRet) : 0,
                'pBio' => $request->pBio ? __convert_value_bd($request->pBio) : 0,
                'pOrig' => $request->pOrig ? __convert_value_bd($request->pOrig) : 0,
                'indImport' => $request->indImport ?? '',
                'cUFOrig' => $request->cUFOrig ?? '',
                'cardapio' => $request->cardapio ? 1 : 0,
                'delivery' => $request->delivery ? 1 : 0,
                'ecommerce' => $request->ecommerce ? 1 : 0,
                'estoque_minimo' => $request->estoque_minimo ? __convert_value_bd($request->estoque_minimo) : 0,
                'mercado_livre_valor' => $request->mercado_livre_valor ? __convert_value_bd($request->mercado_livre_valor) : 0,
            ]);

            if ($request->cardapio) {
                $request->merge([
                    'valor_cardapio' => $request->valor_cardapio ? __convert_value_bd($request->valor_cardapio) :
                    $request->valor_unitario
                ]);
            }

            if ($request->delivery) {
                $request->merge([
                    'valor_delivery' => $request->valor_delivery ? __convert_value_bd($request->valor_delivery) :
                    $request->valor_unitario,
                    'hash_delivery' => Str::random(50),
                ]);
            }

            if ($request->ecommerce) {
                $request->merge([
                    'valor_ecommerce' => $request->valor_ecommerce ? __convert_value_bd($request->valor_ecommerce) :
                    $request->valor_ecommerce,
                    'hash_ecommerce' => Str::random(50),
                    'texto_ecommerce' => $request->texto_ecommerce ?? ''
                ]);
            }else{
                $request->merge([
                    'texto_ecommerce' => ''
                ]);
            }
            $produto = DB::transaction(function () use ($request) {
                $produto = Produto::create($request->all());

                if($request->variavel){
                    for($i=0; $i<sizeof($request->valor_venda_variacao); $i++){
                        $file_name = '';
                        if(isset($request->imagem_variacao[$i])){
                        // requisição com imagem
                            $imagem = $request->imagem_variacao[$i];
                            $file_name = $this->util->uploadImageArray($imagem, '/produtos');
                        }

                        $dataVariacao = [
                            'produto_id' => $produto->id,
                            'descricao' => $request->descricao_variacao[$i],
                            'valor' => __convert_value_bd($request->valor_venda_variacao[$i]),
                            'codigo_barras' => $request->codigo_barras_variacao[$i],
                            'referencia' => $request->referencia_variacao[$i],
                            'imagem' => $file_name
                        ];
                        ProdutoVariacao::create($dataVariacao);
                    }
                }

                $this->insereEmListaDePrecos($produto);

                if($request->mercadolivre){
                    $resp = $this->criaAnuncio($request, $produto);
                    if(isset($resp['erro'])){
                        DB::rollBack();
                        return [
                            'erro' => 1,
                            'msg' => $resp['msg']
                        ];
                    }else{
                        $resp = $resp['retorno'];
                        $produto->mercado_livre_link = $resp->permalink;
                        $produto->mercado_livre_id = $resp->id;
                        $produto->save();
                    }
                }
                return $produto;
            });

            if(isset($produto['erro'])){
                session()->flash("flash_error", $produto['msg']);
                return redirect()->back();
            }
            session()->flash("flash_success", "Produto cadastrado!");

            if ($request->composto == true) {
                session()->flash("flash_success", "Produto cadastrado, informe a composição!");
                return redirect()->route('produto-composto.create', [$produto->id]);
            }
        } catch (\Exception $e) {
            echo $e->getLine();
            die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }

        if ($produto->categoria && $produto->categoria->tipo_pizza) {
            return redirect()->route('produtos.tamanho-pizza', [$produto->id]);
        }

        if (isset($request->redirect_cardapio)) {
            return redirect()->route('produtos-cardapio.index');
        }

        if (isset($request->redirect_delivery)) {
            return redirect()->route('produtos-delivery.index');
        }

        if (isset($request->redirect_ecommerce)) {
            return redirect()->route('produtos-ecommerce.index');
        }
        if (isset($request->redirect_mercadolivre)) {
            return redirect()->route('mercado-livre-produtos.index');
        }
        return redirect()->route('produtos.index');
    }

    private function insereEmListaDePrecos($produto){
        $listas = ListaPreco::where('empresa_id', $produto->empresa_id)
        ->get();
        foreach($listas as $l){
            $valor = 0;
            if($l->ajuste_sobre == 'valor_venda'){
                if($l->tipo == 'incremento'){
                    $valor = $produto->valor_unitario + ($produto->valor_unitario*($l->percentual_alteracao/100));
                }else{
                    $valor = $produto->valor_unitario - ($produto->valor_unitario*($l->percentual_alteracao/100));
                }

            }else{
                if($l->tipo == 'incremento'){
                    $valor = $produto->valor_compra+ ($produto->valor_compra*($l->percentual_alteracao/100));
                }else{
                    $valor = $produto->valor_compra - ($produto->valor_compra*($l->percentual_alteracao/100));
                }
            }
            ItemListaPreco::create([
                'lista_id' => $l->id,
                'produto_id' => $produto->id,
                'valor' => $valor,
                'percentual_lucro' => $l->percentual_alteracao
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $item = Produto::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $file_name = $item->imagem;

            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/produtos');
                $file_name = $this->util->uploadImage($request, '/produtos');
            }

            $request->merge([
                'valor_unitario' => __convert_value_bd($request->valor_unitario),
                'valor_compra' => __convert_value_bd($request->valor_compra),
                'imagem' => $file_name,
                'codigo_anp' => $request->codigo_anp ?? '',
                'perc_glp' => $request->perc_glp ? __convert_value_bd($request->perc_glp) : 0,
                'perc_gnn' => $request->perc_gnn ? __convert_value_bd($request->perc_gnn) : 0,
                'perc_gni' => $request->perc_gni ? __convert_value_bd($request->perc_gni) : 0,
                'valor_partida' => $request->valor_partida ? __convert_value_bd($request->valor_partida) : 0,
                'unidade_tributavel' => $request->unidade_tributavel ?? '',
                'quantidade_tributavel' => $request->quantidade_tributavel ? __convert_value_bd($request->quantidade_tributavel) : 0,
                'adRemICMSRet' => $request->adRemICMSRet ? __convert_value_bd($request->adRemICMSRet) : 0,
                'pBio' => $request->pBio ? __convert_value_bd($request->pBio) : 0,
                'pOrig' => $request->pOrig ? __convert_value_bd($request->pOrig) : 0,
                'indImport' => $request->indImport ?? '',
                'cUFOrig' => $request->cUFOrig ?? '',
                'cardapio' => $request->cardapio ? 1 : 0,
                'delivery' => $request->delivery ? 1 : 0,
                'ecommerce' => $request->ecommerce ? 1 : 0,
                'estoque_minimo' => $request->estoque_minimo ? __convert_value_bd($request->estoque_minimo) : 0,
                'mercado_livre_valor' => $request->mercado_livre_valor ? __convert_value_bd($request->mercado_livre_valor) : 0,
            ]);

            if ($request->cardapio) {
                $request->merge([
                    'valor_cardapio' => $request->valor_cardapio ? __convert_value_bd($request->valor_cardapio) :
                    $request->valor_unitario,
                ]);
            }

            if ($request->delivery) {
                $request->merge([
                    'valor_delivery' => $request->valor_delivery ? __convert_value_bd($request->valor_delivery) :
                    $request->valor_unitario,
                    'hash_delivery' => $item->hash_delivery != null ? $item->hash_delivery : Str::random(50),
                ]);
            }

            if ($request->ecommerce) {
                $request->merge([
                    'valor_ecommerce' => $request->valor_ecommerce ? __convert_value_bd($request->valor_ecommerce) :
                    $request->valor_ecommerce,
                    'hash_ecommerce' => $item->hash_ecommerce != null ? $item->hash_ecommerce : Str::random(50),
                    'texto_ecommerce' => $request->texto_ecommerce ?? ''
                ]);
            }

            $item->fill($request->all())->save();

            if($request->variavel){

                for($i=0; $i<sizeof($request->valor_venda_variacao); $i++){

                    $dataVariacao = [
                        'produto_id' => $item->id,
                        'descricao' => $request->descricao_variacao[$i],
                        'valor' => __convert_value_bd($request->valor_venda_variacao[$i]),
                        'codigo_barras' => $request->codigo_barras_variacao[$i],
                        'referencia' => $request->referencia_variacao[$i],        
                    ];
                    if(isset($request->variacao_id[$i])){
                        $variacao = ProdutoVariacao::findOrfail($request->variacao_id[$i]);

                        $file_name = $variacao->imagem;
                        
                        if(isset($request->imagem_variacao[$i])){

                            if($file_name != null){
                                $this->util->unlinkImage($variacao, '/produtos');
                            }
                            // requisição com imagem
                            $imagem = $request->imagem_variacao[$i];
                            $file_name = $this->util->uploadImageArray($imagem, '/produtos');

                        }
                        $dataVariacao['imagem'] = $file_name;

                        $variacao->fill($dataVariacao)->save();
                    }else{
                        $file_name = '';
                        if(isset($request->imagem_variacao[$i])){
                            // requisição com imagem
                            $imagem = $request->imagem_variacao[$i];
                            $file_name = $this->util->uploadImageArray($imagem, '/produtos');
                        }
                        $dataVariacao['imagem'] = $file_name;
                        
                        ProdutoVariacao::create($dataVariacao);
                    }
                }
            }else{
                ProdutoVariacao::where('produto_id', $item->id)->delete();
            }

            if($request->mercadolivre){
                if($item->mercado_livre_id == null){
                    $resp = $this->criaAnuncio($request, $item);
                }else{
                    $resp = $this->atualizaAnuncio($request, $item);
                }
                if(isset($resp['erro'])){
                    session()->flash("flash_error", $resp['msg']);

                }else{
                    $resp = $resp['retorno'];
                    $item->mercado_livre_link = $resp->permalink;
                    $item->mercado_livre_id = $resp->id;
                    $item->save();
                    session()->flash("flash_success", "Produto atualizado!");
                }
            }else{
                session()->flash("flash_success", "Produto atualizado!");
            }

            if ($request->composto == true) {
                session()->flash("flash_success", "Produto atualizado, informe a composição!");
                return redirect()->route('produto-composto.create', [$item->id]);
            }
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        if (isset($request->redirect_cardapio)) {
            return redirect()->route('produtos-cardapio.index');
        }
        if (isset($request->redirect_delivery)) {
            return redirect()->route('produtos-delivery.index');
        }
        if (isset($request->redirect_ecommerce)) {
            return redirect()->route('produtos-ecommerce.index');
        }
        if (isset($request->redirect_mercadolivre)) {
            return redirect()->route('mercado-livre-produtos.index');
        }
        return redirect()->route('produtos.index');
    }

    public function destroy($id)
    {
        $item = Produto::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $item->variacoes()->delete();
            $item->itemLista()->delete();
            $item->itemNfe()->delete();
            $item->itemNfce()->delete();
            $item->itemCarrinhos()->delete();
            $item->movimentacoes()->delete();
            $item->composicao()->delete();
            $item->itemPreVenda()->delete();
            if($item->estoque){
                $item->estoque->delete();
            }
            $item->delete();

            $this->util->unlinkImage($item, '/produtos');

            session()->flash("flash_success", "Produto removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = Produto::findOrFail($request->item_delete[$i]);
            try {
                $item->variacoes()->delete();
                $item->itemLista()->delete();
                $item->itemNfe()->delete();
                $item->itemNfce()->delete();
                $item->movimentacoes()->delete();
                $item->composicao()->delete();
                $item->itemPreVenda()->delete();
                if($item->estoque){
                    $item->estoque->delete();
                }
                $item->delete();
                $this->util->unlinkImage($item, '/produtos');

                $removidos++;
            } catch (\Exception $e) {
                session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                return redirect()->route('produtos.index');
            }
        }

        session()->flash("flash_success", "Total de itens removidos: $removidos!");
        return redirect()->route('produtos.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            // 'codigo_barras' => 'required',
            'ncm' => 'required',
            'descricao' => 'max:255',
            'descricao_en' => 'max:255',
            'descricao_es' => 'max:255',
            'unidade' => 'required',
            'perc_icms' => 'required',
            'perc_pis' => 'required',
            'perc_cofins' => 'required',
            'perc_ipi' => 'required',
            'cst_csosn' => 'required',
            'cst_pis' => 'required',
            'cst_cofins' => 'required',
            'cst_ipi' => 'required',
            'valor_unitario' => 'required',
        ];

        $messages = [
            'nome.required' => 'Campo Obrigatório',
            'codigo_barras.required' => 'Campo Obrigatório',
            'ncm.required' => 'Campo Obrigatório',
            'cest.required' => 'Campo Obrigatório',
            'unidade.required' => 'Campo Obrigatório',
            'perc_icms.required' => 'Campo Obrigatório',
            'perc_pis.required' => 'Campo Obrigatório',
            'perc_cofins.required' => 'Campo Obrigatório',
            'perc_ipi.required' => 'Campo Obrigatório',
            'cst_csosn.required' => 'Campo Obrigatório',
            'cst_pis.required' => 'Campo Obrigatório',
            'cst_cofins.required' => 'Campo Obrigatório',
            'cst_ipi.required' => 'Campo Obrigatório',
            'valor_unitario.required' => 'Campo Obrigatório',
            'descricao.max' => 'Máximo de 255 caracteres',
            'descricao_es.max' => 'Máximo de 255 caracteres',
            'descricao_en.max' => 'Máximo de 255 caracteres',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function import()
    {
        return view('produtos.import');
    }

    public function downloadModelo()
    {
        return response()->download(public_path('files/') . 'import_products_csv_template.xlsx');
    }

    public function storeModelo(Request $request)
    {
        if ($request->hasFile('file')) {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            $rows = Excel::toArray(new ProdutoImport, $request->file);
            $retornoErro = $this->validaArquivo($rows);
            $cont = 0;
            if ($retornoErro == "") {

                foreach ($rows as $row) {
                    foreach ($row as $key => $r) {

                        if ($r[0] != 'NOME') {

                            try {
                                $data = $this->preparaObjeto($r, $request->empresa_id);
                                $item = Produto::create($data);
                                $cont++;
                            } catch (\Exception $e) {
                                session()->flash('flash_error', $e->getMessage());
                            }
                        }
                    }
                }
                session()->flash('flash_success', 'Total de produtos importados: ' . $cont);
                return redirect()->back();
            } else {
                session()->flash('flash_error', $retornoErro);
                return redirect()->back();
            }
        } else {
            session()->flash('flash_error', 'Nenhum Arquivo!!');
            return redirect()->back();
        }
    }

    private function mask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }

        return $maskared;
    }

    private function preparaObjeto($linha, $empresa_id)
    {
        $ncm = __mask((string)$linha[4], '####.##.##');

        $categoria = CategoriaProduto::where('empresa_id', $empresa_id)
        ->where('nome', $linha[1])->first();
        $data = [
            'empresa_id' => $empresa_id,
            'nome' => $linha[0],
            'codigo_barras' => $linha[5],
            'ncm' => $ncm,
            'cest' => $linha[6],
            'unidade' => $linha[17] != '' ? $linha[17] : 'UN',
            'perc_icms' => $linha[20] != '' ? __convert_value_bd($linha[20]) : 0,
            'perc_pis' => $linha[21] != '' ? __convert_value_bd($linha[21]) : 0,

            'perc_cofins' => $linha[22] != '' ? __convert_value_bd($linha[22]) : 0,
            'perc_ipi' => $linha[23] != '' ? __convert_value_bd($linha[23]) : 0,
            'cst_csosn' => $linha[7],
            'cst_pis' => $linha[8],
            'cst_cofins' => $linha[9],
            'cst_ipi' => $linha[10],
            'valor_unitario' => __convert_value_bd($linha[2]),
            'origem' => $linha[12] != '' ? $linha[12] : 1,
            'perc_red_bc' => $linha[11] != '' ? __convert_value_bd($linha[11]) : 0,
            'cfop_estadual' => $linha[14],
            'cfop_outro_estado' => $linha[15],
            'cEnq' => $linha[13],
            'categoria_id' => $categoria != null ? $categoria->id : null,
            'gerenciar_estoque' => $linha[19] != '' ? $linha[19] : 0,
            'codigo_beneficio_fiscal' => $linha[16],
            'valor_compra' => __convert_value_bd($linha[3])
        ];
        return $data;
    }

    private function validaArquivo($rows)
    {
        $cont = 0;
        $msgErro = "";
        foreach ($rows as $row) {
            foreach ($row as $key => $r) {

                $nome = $r[0];
                $valorVenda = $r[2];
                $ncm = $r[4];
                $cstCsosn = $r[7];
                $cstPis = $r[8];
                $cstCofins = $r[9];
                $cstIpi = $r[10];
                $cfopEstado = $r[14];
                $cfopOutroEstado = $r[15];

                if (strlen($nome) == 0) {
                    $msgErro .= "Coluna nome em branco na linha: $cont | ";
                }

                if (strlen($valorVenda) == 0) {
                    $msgErro .= "Coluna valor venda em branco na linha: $cont | ";
                }

                if (strlen($ncm) == 0) {
                    $msgErro .= "Coluna NCM em branco na linha: $cont | ";
                }

                if (strlen($cstCsosn) == 0) {
                    $msgErro .= "Coluna CST/CSOSN em branco na linha: $cont | ";
                }
                if (strlen($cstPis) == 0) {
                    $msgErro .= "Coluna CST/PIS em branco na linha: $cont | ";
                }
                if (strlen($cstCofins) == 0) {
                    $msgErro .= "Coluna CST/COFINS em branco na linha: $cont | ";
                }
                if (strlen($cstIpi) == 0) {
                    $msgErro .= "Coluna CST/IPI em branco na linha: $cont | ";
                }

                if (strlen($cfopEstado) == 0) {
                    $msgErro .= "Coluna CFOP estado em branco na linha: $cont | ";
                }
                if (strlen($cfopOutroEstado) == 0) {
                    $msgErro .= "Coluna CFOP outro estado em branco na linha: $cont | ";
                }

                if ($msgErro != "") {
                    return $msgErro;
                }
                $cont++;
            }
        }

        return $msgErro;
    }

    public function gerarCodigoEan()
    {
        try {
            $rand = rand(11111, 99999);
            $code = $this->incluiDigito('7891000' . $rand);
            return response()->json($code, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    private function incluiDigito($code)
    {
        $weightflag = true;
        $sum = 0;
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightflag ? 3 : 1);
            $weightflag = !$weightflag;
        }
        return $code . (10 - ($sum % 10)) % 10;
    }


    public function show($id){
        $item = Produto::findOrFail($id);
        __validaObjetoEmpresa($item);
        $data = MovimentacaoProduto::where('produto_id', $id)
        ->orderBy('id', 'desc')
        ->get();
        return view('produtos.show', compact('item', 'data'));
    }

    public function movimentacao($id){
        $item = MovimentacaoProduto::findOrFail($id);
        __validaObjetoEmpresa($item);
        if($item->tipo_transacao == 'venda_nfe'){
            return redirect()->route('nfe.show', [$item->codigo_transacao]);
        }
        if($item->tipo_transacao == 'venda_nfce'){
            return redirect()->route('nfce.show', [$item->codigo_transacao]);
        }
        if($item->tipo_transacao == 'alteracao_estoque'){
            return redirect()->route('estoque.index', ['produto='.$item->produto->nome]);
        }
        if($item->tipo_transacao == 'compra'){
            return redirect()->route('compras.show', [$item->codigo_transacao]);
        }
    }

    public function removeImagem($id){
        $item = Produto::findOrFail($id);
        try{
            $this->util->unlinkImage($item, '/produtos');
            $item->imagem = '';
            $item->save();
            session()->flash("flash_success", "Imagem removida");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function galeria($id){
        $item = Produto::findOrFail($id);
        __validaObjetoEmpresa($item);
        return view('produtos.galeria', compact('item'));
    }

    public function storeImage(Request $request, $id){
        $item = Produto::findOrFail($id);
        __validaObjetoEmpresa($item);
        if ($request->hasFile('image')) {

            $file_name = $this->util->uploadImage($request, '/produtos');
            GaleriaProduto::create([
                'produto_id' => $id,
                'imagem' => $file_name
            ]);
            session()->flash("flash_success", "Imagem cadastrada!");
        }else{
            session()->flash("flash_error", "Selecione o arquivo!");
        }
        return redirect()->back();
    }

    public function destroyImage($id){
        $item = GaleriaProduto::findOrFail($id);
        try {
            $item->delete();

            $this->util->unlinkImage($item, '/produtos');

            session()->flash("flash_success", "Imagem removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function duplicar(Request $request, $id)
    {
        $item = Produto::findOrFail($id);
        __validaObjetoEmpresa($item);
        $empresa = Empresa::findOrFail(request()->empresa_id);

        $listaCTSCSOSN = Produto::listaCSOSN();
        if ($empresa->tributacao == 'Regime Normal') {
            $listaCTSCSOSN = Produto::listaCST();
        }
        $padroes = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->get();
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();
        $cardapio = 0;
        if (isset($request->cardapio)) {
            $cardapio = 1;
        }

        $delivery = 0;
        if (isset($request->delivery)) {
            $delivery = 1;
        }

        $ecommerce = 0;
        if (isset($request->ecommerce)) {
            $ecommerce = 1;
        }
        $marcas = Marca::where('empresa_id', request()->empresa_id)->get();
        $variacoes = VariacaoModelo::where('empresa_id', $request->empresa_id)
        ->where('status', 1)->get();

        $configMercadoLivre = MercadoLivreConfig::where('empresa_id', $request->empresa_id)
        ->first();
        return view('produtos.duplicar', 
            compact('item', 'listaCTSCSOSN', 'padroes', 'categorias', 'cardapio', 'marcas', 'delivery', 'variacoes', 
                'ecommerce', 'configMercadoLivre'));
    }

    private function criaAnuncio($request, $produto){

        $dataMercadoLivre = [
            'title' => $produto->nome,
            'category_id' => $request->mercado_livre_categoria,
            'price' => __convert_value_bd($request->mercado_livre_valor),
            'available_quantity' => __convert_value_bd($request->quantidade_mercado_livre),
            'currency_id' => 'BRL',
            'condition' => $request->condicao_mercado_livre,
            'buying_mode' => 'buy_it_now',
            'listing_type_id' => $request->mercado_livre_tipo_publicacao,
            'video_id' => $request->mercado_livre_youtube,
        ];

        if($request->marca_id){
            $marca = Marca::findOrFail($request->marca_id);
            $dataMercadoLivre['attributes'][] = [
                'id' => 'BRAND',
                'value_name' => $marca->nome
            ];
        }
        // dd($dataMercadoLivre);

        $configMercadoLivre = MercadoLivreConfig::where('empresa_id', $request->empresa_id)
        ->first();

        if($produto->img){
            $dataMercadoLivre['pictures'][0]['source'] = $configMercadoLivre->url . $produto->img;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/items");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataMercadoLivre));

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $configMercadoLivre->access_token,
            'Content-Type: application/json'
        ]);

        $res = curl_exec($curl);
        $retorno = json_decode($res);
        if($retorno->status == 400){
            $msg = $this->trataErros($retorno);
            return [
                'erro' => 1,
                'msg' => $msg
            ];
        }
        // incluir descricao

        if($request->mercado_livre_descricao){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/items/$retorno->id/description");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                ['plain_text' => $request->mercado_livre_descricao]
            ));

            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $configMercadoLivre->access_token,
                'Content-Type: application/json'
            ]);

            $res = curl_exec($curl);
        }

        return [
            'sucesso' => 1,
            'retorno' => $retorno
        ];

    }

    private function trataErros($retorno){
        $msg = "";
        foreach($retorno->cause as $c){
            $msg .= $c->message;
        }
        return $msg;
    }

    private function atualizaAnuncio($request, $produto){

        $configMercadoLivre = MercadoLivreConfig::where('empresa_id', $request->empresa_id)
        ->first();
        $dataMercadoLivre = [
            'title' => $produto->nome,
            'category_id' => $request->mercado_livre_categoria,
            'price' => __convert_value_bd($request->mercado_livre_valor),
            'available_quantity' => __convert_value_bd($request->quantidade_mercado_livre),
            'currency_id' => 'BRL',
            'condition' => $request->condicao_mercado_livre,
            'buying_mode' => 'buy_it_now',
            'video_id' => $request->mercado_livre_youtube,
        ];

        // dd($dataMercadoLivre);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/items/$produto->mercado_livre_id");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataMercadoLivre));

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $configMercadoLivre->access_token,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        $res = curl_exec($curl);
        $retorno = json_decode($res);

        if($retorno->status == 400){
            $msg = $this->trataErros($retorno);
            return [
                'erro' => 1,
                'msg' => $msg
            ];
        }

        if($request->mercado_livre_descricao){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/items/$produto->mercado_livre_id/description");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                ['plain_text' => $request->mercado_livre_descricao]
            ));

            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $configMercadoLivre->access_token,
                'Content-Type: application/json'
            ]);

            $res = curl_exec($curl);
        }

        return [
            'sucesso' => 1,
            'retorno' => $retorno
        ];
    }

}
