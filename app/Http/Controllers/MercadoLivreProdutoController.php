<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MercadoLivreConfig;
use App\Models\Produto;
use App\Models\CategoriaMercadoLivre;
use App\Models\PadraoTributacaoProduto;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use App\Models\CategoriaProduto;
use App\Utils\EstoqueUtil;

class MercadoLivreProdutoController extends Controller
{

    protected $util;
    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request){
        $this->validaCategorias();
        $data = Produto::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
        ->where('mercado_livre_id', '!=', null)
        ->paginate(env("PAGINACAO"));
        return view('mercado_livre_produtos.index', compact('data'));

    }

    private function validaCategorias(){
        $categorias = CategoriaMercadoLivre::count();

        if($categorias == 0){
            $config = MercadoLivreConfig::where('empresa_id', request()->empresa_id)
            ->first();

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/sites/MLB/categories/all");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $config->access_token,
                'Content-Type: application/json'
            ]);

            $res = curl_exec($curl);
            $retorno = json_decode($res);
            foreach($retorno as $r){
                $cat = [
                    '_id' => $r->id,
                    'nome' => $r->name
                ];
                CategoriaMercadoLivre::create($cat);
            }
        }
    }

    public function produtosNew(Request $request){
        $config = MercadoLivreConfig::where('empresa_id', $request->empresa_id)
        ->first();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/users/$config->user_id/items/search/?offset=0");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
            'Content-Type: application/json'
        ]);

        $res = curl_exec($curl);
        $retorno = json_decode($res);
        if(!isset($retorno->results)){
            session()->flash("flash_error", $retorno->message);
            return redirect()->route('mercado-livre-config.index');
        }
        $results = $retorno->results;
        $produtosIsert = [];
        foreach($results as $rcode){
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/items/$rcode");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $config->access_token,
                'Content-Type: application/json'
            ]);

            $res = curl_exec($curl);
            $retorno = json_decode($res);

            $res = $this->validaProdutoCadastrado($retorno, $request->empresa_id);

            if(is_array($res)){
                $produtosIsert[] = $res;
            }
        }

        if(sizeof($produtosIsert) > 0){
            $empresa = Empresa::findOrFail(request()->empresa_id);
            $listaCTSCSOSN = Produto::listaCSOSN();
            if ($empresa->tributacao == 'Regime Normal') {
                $listaCTSCSOSN = Produto::listaCST();
            }
            $padraoTributacao = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->where('padrao', 1)
            ->first();
            $padroes = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->get();
            $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();

            return view('mercado_livre_produtos.create_produtos', 
                compact('produtosIsert', 'padraoTributacao', 'listaCTSCSOSN', 'padroes', 'categorias'));
        }else{
            return redirect()->route('mercado-livre-produtos.index');
        }
    }

    private function validaProdutoCadastrado($mlProduto, $empresa_id){

        $produto = Produto::where('empresa_id', $empresa_id)
        ->where('mercado_livre_id', $mlProduto->id)
        ->first();
        if($produto != null){
            return true;
        }

        $dataProduto = [
            'empresa_id' => $empresa_id,
            'nome' => $mlProduto->title,
            'valor_venda' => $mlProduto->price,
            'mercado_livre_id' => $mlProduto->id,
            'mercado_livre_valor' => $mlProduto->price,
            'mercado_livre_link' => $mlProduto->permalink,
            'estoque' => $mlProduto->available_quantity,
            'status' => $mlProduto->status
        ];

        return $dataProduto;
    }

    public function store(Request $request){

        DB::transaction(function () use ($request) {
            $contInserts = 0;
            try{
                for($i=0; $i<sizeof($request->mercado_livre_id); $i++){

                    $data = [
                        'mercado_livre_id' => $request->mercado_livre_id[$i],
                        'nome' => $request->nome[$i],
                        'valor_unitario' => __convert_value_bd($request->valor_venda[$i]),
                        'mercado_livre_valor' => __convert_value_bd($request->mercado_livre_valor[$i]),
                        'valor_compra' => $request->valor_compra[$i] ? __convert_value_bd($request->valor_compra[$i]) : 0,
                        'codigo_barras' => $request->codigo_barras[$i],
                        'ncm' => $request->ncm[$i],
                        'unidade' => $request->unidade[$i],
                        'gerenciar_estoque' => $request->gerenciar_estoque[$i],
                        'categoria_id' => $request->categoria_id[$i],
                        'cest' => $request->cest[$i],
                        'cfop_estadual' => $request->cfop_estadual[$i],
                        'cfop_outro_estado' => $request->cfop_outro_estado[$i],
                        'perc_icms' => __convert_value_bd($request->perc_icms[$i]),
                        'perc_pis' => __convert_value_bd($request->perc_pis[$i]),
                        'perc_cofins' => __convert_value_bd($request->perc_cofins[$i]),
                        'perc_ipi' => __convert_value_bd($request->perc_ipi[$i]),
                        'perc_red_bc' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        'cEnq' => $request->cEnq[$i],
                        'empresa_id' => $request->empresa_id,
                        'mercado_livre_status' => $request->mercado_livre_status[$i]
                    ];
                    $produto = Produto::create($data);
                    if($request->estoque[$i]){
                        $this->util->incrementaEstoque($produto->id, $request->estoque[$i]);
                    }
                    $contInserts++;
                }
                session()->flash("flash_success", "Total de produtos inseridos: $contInserts");

            }catch(\Exception $e){
                session()->flash("flash_error", $e->getMessage());
            }

        });
        return redirect()->route('mercado-livre-produtos.index');
    }

    public function galery($id){
        $item = Produto::findOrFail($id);
        $curl = curl_init();
        $config = MercadoLivreConfig::where('empresa_id', $item->empresa_id)
        ->first();
        curl_setopt($curl, CURLOPT_URL, "https://api.mercadolibre.com/items/$item->mercado_livre_id");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config->access_token,
            'Content-Type: application/json'
        ]);

        $res = curl_exec($curl);
        $retorno = json_decode($res);
        return view('mercado_livre_produtos.galery', compact('item', 'retorno'));
    }

}
