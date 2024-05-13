<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fornecedor;
use App\Models\Nfe;
use App\Models\ItemNfe;
use App\Models\Produto;
use App\Models\Cidade;
use App\Models\ContaPagar;
use App\Models\NaturezaOperacao;
use App\Models\Transportadora;
use App\Models\Empresa;
use App\Models\FaturaNfe;
use App\Models\Cotacao;
use App\Utils\EstoqueUtil;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $util;

    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
    }

    private function setNumeroSequencial(){
        $docs = Nfe::where('empresa_id', request()->empresa_id)
        ->where('numero_sequencial', null)
        ->get();

        $last = Nfe::where('empresa_id', request()->empresa_id)
        ->orderBy('numero_sequencial', 'desc')
        ->where('numero_sequencial', '>', 0)->first();
        $numero = $last != null ? $last->numero_sequencial : 0;
        $numero++;

        foreach($docs as $d){
            $d->numero_sequencial = $numero;
            $d->save();
            $numero++;
        }
    }

    public function index(Request $request)
    {
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $fornecedor_id = $request->get('fornecedor_id');
        $estado = $request->get('estado');
        $this->setNumeroSequencial();

        $data = Nfe::where('empresa_id', request()->empresa_id)
        ->where('tpNF', 0)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
            return $query->where('fornecedor_id', $fornecedor_id);
        })
        ->when($estado != "", function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('compras.index', compact('data', 'fornecedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $sizeFornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->count();
        if ($sizeFornecedores == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um fornecedor!");
            return redirect()->route('fornecedores.create');
        }
        $produtos = Produto::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($produtos) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um produto!");
            return redirect()->route('produtos.create');
        }
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        }
        $empresa = Empresa::findOrFail(request()->empresa_id);
        $numeroNfe = Nfe::lastNumero($empresa);

        $cotacao = null;
        if(isset($request->cotacao_id)){
            $cotacao = Cotacao::findOrfail($request->cotacao_id);
        }

        $isCompra = 1;
        return view(
            'nfe.create',
            compact('produtos', 'transportadoras', 'cidades', 'naturezas', 'numeroNfe', 'isCompra', 'cotacao', 'empresa')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function xml()
    {
        return view('compras.xml');
    }

    public function storeXml(Request $request)
    {
        if ($request->hasFile('file')) {

            $arquivo = $request->hasFile('file');
            $file = $request->file;

            $xml = simplexml_load_file($request->file);

            if ($xml->NFe->infNFe == null) {
                session()->flash('flash_error', 'Este XML parece inválido!');
                return redirect()->back();
            }

            $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
            $file->move(public_path('xml_entrada'), $chave . ".xml");

            $cidade = Cidade::where('codigo', $xml->NFe->infNFe->emit->enderEmit->cMun)
            ->first();

            $doc = $xml->NFe->infNFe->emit->CNPJ ? $xml->NFe->infNFe->emit->CNPJ : $xml->NFe->infNFe->emit->CPF;
            $doc = trim($doc);
            $mask = '##.###.###/####-##';
            if (strlen($doc) == 11) {
                $mask = '###.###.###-##';
            }
            $doc = __mask($doc, $mask);

            $dataFornecedor = [

                'empresa_id' => $request->empresa_id,
                'razao_social' => $xml->NFe->infNFe->emit->xNome,
                'nome_fantasia' => $xml->NFe->infNFe->emit->xFant,
                'cpf_cnpj' => $doc,
                'ie' => $xml->NFe->infNFe->emit->IE,
                'contribuinte' => $xml->NFe->infNFe->emit->IE != '' ? 1 : 0,
                'consumidor_final' => 0,
                'email' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
                'telefone' => $xml->NFe->infNFe->emit->enderEmit->fone,
                'cidade_id' => $cidade->id,
                'rua' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
                'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
                'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
                'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
                'complemento' => $xml->NFe->infNFe->emit->enderEmit->xBairro
            ];

            $fornecedor = $this->cadastraFornecedor($dataFornecedor);
            $vFrete = (float)$xml->NFe->infNFe->total->ICMSTot->vFrete;
            $vDesc = (float)$xml->NFe->infNFe->total->ICMSTot->vDesc;

            $itens = [];
            $contSemRegistro = 0;
            foreach ($xml->NFe->infNFe->det as $item) {

                $produto = Produto::verificaCadastrado(
                    $item->prod->cEAN,
                    $item->prod->xProd,
                    $item->prod->cProd,
                    $request->empresa_id
                );

                $vIpi = 0;
                $vICMSST = 0;
                if (isset($item->imposto->IPI)) {
                    $valor = (float)$item->imposto->IPI->IPITrib->vIPI;
                    if ($valor > 0)
                        $vIpi = $valor / (float)$item->prod->qCom;
                }

                if (isset($item->imposto->ICMS)) {
                    $arr = (array_values((array)$item->imposto->ICMS));
                    $cst = $arr[0]->CST ? $arr[0]->CST : $arr[0]->CSOSN;
                    $valor = (float)$arr[0]->vICMSST ?? 0;
                    if ($valor > 0)
                        $vICMSST = $valor / $item->prod->qCom;
                }

                $nomeProduto = $item->prod->xProd;
                $nomeProduto = str_replace("'", "", $nomeProduto);
                $codigo = preg_replace('/[^0-9]/', '', $item->prod->cProd);

                if ($produto == null) {
                    $contSemRegistro++;
                }

                $prod = new \stdClass();

                $prod->id = $produto != null ? $produto->id : 0;
                $prod->codigo = $codigo;
                $prod->xProd = $produto == null ? $nomeProduto : $produto->nome;
                $prod->ncm = (string)$item->prod->NCM;
                $prod->cest = (string)$item->prod->CEST;
                $prod->cfop = (string)$item->prod->CFOP;
                $prod->unidade = (string)$item->prod->uCom;
                $prod->valor_unitario = number_format((float)$item->prod->vUnCom + $vIpi + $vICMSST, 2, '.', '');
                $prod->quantidade = (float)$item->prod->qCom;
                $prod->sub_total = (float)$item->prod->vProd;
                $prod->codigo_barras = (string)$item->prod->cEAN;
                $prod->valor_venda = $produto == null ? 0 : $produto->valor_venda;
                $prod->valor_compra = $produto == null ? 0 : $produto->valor_compra;


                $arr = (array_values((array)$item->imposto->ICMS));
                $cst = (string)($arr[0]->CST ? $arr[0]->CST : $arr[0]->CSOSN);
                $pICMS = (float)$arr[0]->pICMS ?? 0;

                $prod->perc_red_bc = 0;
                $prod->perc_icms = $pICMS;
                $prod->cst_csosn = $cst;

                $arr = (array_values((array)$item->imposto->PIS));

                $prod->cst_pis = (string)$arr[0]->CST;
                $prod->perc_pis = (float)$arr[0]->pPIS ?? 0;

                $arr = (array_values((array)$item->imposto->COFINS));
                $prod->cst_cofins = $arr[0]->CST;
                $pCOFINS = $arr[0]->COFINS ?? 0;
                if ($pCOFINS == 0) {
                    $pCOFINS = $arr[0]->pCOFINS ?? 0;
                }
                $prod->perc_cofins = $arr[0]->pPIS ?? 0;

                $arr = (array_values((array)$item->imposto->IPI));
                if (isset($arr[1])) {

                    $cst_ipi = $arr[1]->CST ?? '99';
                    $pIPI = $arr[0]->IPI ?? 0;
                    if ($pIPI == 0) {
                        $pIPI = $arr[0]->pIPI ?? 0;
                    }

                    if (isset($arr[1]->pIPI)) {
                        $pIPI = $arr[1]->pIPI ?? 0;
                    } else {
                        if (isset($arr[4]->pIPI)) {
                            $ipi = $arr[4]->CST;
                            $pIPI = $arr[4]->pIPI;
                        } else {
                            $pIPI = 0;
                        }
                    }
                } else {
                    $cst_ipi = '99';
                    $pIPI = 0;
                }

                $prod->perc_ipi = $pIPI;
                $prod->cst_ipi = $cst_ipi;

                $prod->codigo_beneficio_fiscal = '';

                array_push($itens, $prod);
            }

            $dadosXml = [
                'chave' => $chave,
                'vProd' => (float)$xml->NFe->infNFe->total->ICMSTot->vNF,
                'indPag' => (int)$xml->NFe->infNFe->ide->indPag,
                'nNf' => (int)$xml->NFe->infNFe->ide->nNF,
                'vFrete' => $vFrete,
                'vDesc' => $vDesc,
                'contSemRegistro' => $contSemRegistro,
                'data_emissao' => substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 16),
                'itens' => $itens
            ];

            if (!is_dir(public_path('xml_entrada'))) {
                mkdir(public_path('xml_entrada'), 0777, true);
            }

            $fatura = [];
            if (!empty($xml->NFe->infNFe->cobr->dup)) {
                foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
                    $titulo = $dup->nDup;
                    $vencimento = $dup->dVenc;
                    $vencimento = explode('-', $vencimento);
                    $vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
                    $valor_parcela = number_format((float) $dup->vDup, 2, ".", "");
                    $parcela = [
                        'numero' => (int)$titulo,
                        'vencimento' => $vencimento,
                        'valor_parcela' => $valor_parcela,
                        'rand' => rand(0, 10000)
                    ];
                    array_push($fatura, $parcela);
                }
            } else {
                $vencimento = explode('-', substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 10));
                $vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
                $parcela = [
                    'numero' => 1,
                    'vencimento' => $vencimento,
                    'valor_parcela' => (float)$xml->NFe->infNFe->total->ICMSTot->vProd,
                    'rand' => rand(0, 10000)
                ];
                array_push($fatura, $parcela);
            }

            $dadosXml['fatura'] = $fatura;

            $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
            $cidades = Cidade::all();
            $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
            if (sizeof($naturezas) == 0) {
                session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
                return redirect()->route('natureza-operacao.create');
            }

            return view('compras.import_xml', compact('dadosXml', 'transportadoras', 'cidades', 'naturezas', 'fornecedor'));
        } else {
            session()->flash('flash_error', 'XML inválido!');
            return redirect()->back();
        }
    }

    private function getCfopEntrada($cfop)
    {

        $digito = substr($cfop, 0, 1);
        if ($digito == '5') {
            return '1' . substr($cfop, 1, 4);
        } else {
            return '2' . substr($cfop, 1, 4);
        }
    }

    private function cadastraFornecedor($dataFornecedor)
    {
        $fornecedor = Fornecedor::where('cpf_cnpj', $dataFornecedor['cpf_cnpj'])
        ->where('empresa_id', request()->empresa_id)->first();

        if ($fornecedor == null) {
            $fornecedor = Fornecedor::create($dataFornecedor);
        }

        return $fornecedor;
    }

    public function finishXml(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {

                $fornecedor_id = isset($request->fornecedor_id) ? $request->fornecedor_id : null;
                $empresa = Empresa::findOrFail($request->empresa_id);

                if (isset($request->fornecedor_id)) {
                    if ($request->fornecedor_id == null) {
                        $fornecedor_id = $this->cadastrarFornecedor($request);
                    } else {
                        $this->atualizaFornecedor($request);
                    }
                }
                $transportadora_id = $request->transportadora_id;
                if ($request->transportadora_id == null) {
                    $transportadora_id = $this->cadastrarTransportadora($request);
                } else {
                    $this->atualizaTransportadora($request);
                }
                $config = Empresa::find($request->empresa_id);

                $caixa = __isCaixaAberto();

                $request->merge([
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'chave' => '',
                    'fornecedor_id' => $fornecedor_id,
                    'transportadora_id' => $transportadora_id,
                    'numero_serie' => $empresa->numero_serie_nfe,
                    'numero' => $request->numero_nfe,
                    'chave_importada' => $request->chave_importada,
                    'estado' => 'novo',
                    'total' => __convert_value_bd($request->valor_total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_produtos' => __convert_value_bd($request->valor_produtos),
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'caixa_id' => $caixa ? $caixa->id : null,
                    'tipo_pagamento' => isset($request->tipo_pagamento[0]) ? $request->tipo_pagamento[0] : null,
                ]);
                // dd($request->tipo_pagamento[]);
                $nfe = Nfe::create($request->all());
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    if ($request->produto_id[$i] == 0) {
                        //cadastrar produto
                        $product = $this->cadastrarProduto($request, $i);
                    } else {
                        $product = Produto::findOrFail($request->produto_id[$i]);
                    }

                    ItemNfe::create([
                        'nfe_id' => $nfe->id,
                        'produto_id' => $product->id,
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'sub_total' => __convert_value_bd($request->sub_total[$i]),
                        'perc_icms' => __convert_value_bd($request->perc_icms[$i]),
                        'perc_pis' => __convert_value_bd($request->perc_pis[$i]),
                        'perc_cofins' => __convert_value_bd($request->perc_cofins[$i]),
                        'perc_ipi' => __convert_value_bd($request->perc_ipi[$i]),
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        'perc_red_bc' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                        'cfop' => $request->cfop[$i],
                        'ncm' => $request->ncm[$i],
                        'codigo_beneficio_fiscal' => $request->codigo_beneficio_fiscal[$i]
                    ]);

                    if ($product->gerenciar_estoque) {
                        $this->util->incrementaEstoque($product->id, __convert_value_bd($request->quantidade[$i]));
                    }
                }
                if($request->tipo_pagamento){
                    for ($i = 0; $i < sizeof($request->tipo_pagamento); $i++) {
                        if ($request->tipo_pagamento[$i]) {
                            FaturaNfe::create([
                                'nfe_id' => $nfe->id,
                                'tipo_pagamento' => $request->tipo_pagamento[$i],
                                'data_vencimento' => $request->data_vencimento[$i],
                                'valor' => __convert_value_bd($request->valor_fatura[$i])
                            ]);
                        }


                        if ($request->gerar_conta_pagar) {
                            ContaPagar::create([
                                'empresa_id' => $request->empresa_id,
                                'nfe_id' => $nfe->id,
                                'fornecedor_id' => $fornecedor_id,
                                'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                                'tipo_pagamento' => $request->tipo_pagamento[$i],
                                'data_vencimento' => $request->data_vencimento[$i],
                            ]);
                        }
                    }
                }
            });
session()->flash("flash_success", "Importação cadastrada!");
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>' . $e->getLine();
    die;
    session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
}

return redirect()->route('compras.index');
}

private function cadastrarProduto($request, $i)
{
        // dd($request);
    $cfop = $request->cfop[$i];
    $cfopOutroEstado = '';
    $cfopEstado = '';
    $digito = substr($cfop, 0, 1);

    $cfopEstado = '5' . substr($cfop, 1, 4);
    $cfopOutroEstado = '6' . substr($cfop, 1, 4);

    $p = Produto::create([
        'empresa_id' => $request->empresa_id,
        'nome' => $request->nome_produto[$i],
        'ncm' => $request->ncm[$i],
        'gerenciar_estoque' => $request->gerenciar_estoque,
        'unidade' => $request->unidade[$i],
        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
        'perc_red_bc' => __convert_value_bd($request->perc_red_bc[$i]),
        'cfop_estadual' => $cfopEstado,
        'cfop_outro_estado' => $cfopOutroEstado,
        'valor_compra' => $request->valor_compra ? __convert_value_bd($request->valor_compra) : 0,

        'perc_red_bc' => __convert_value_bd($request->perc_red_bc[$i]),
        'cst_csosn' => $request->cst_csosn[$i],
        'cst_pis' => $request->cst_pis[$i],
        'cst_cofins' => $request->cst_cofins[$i],
        'cst_ipi' => $request->cst_ipi[$i],

        'perc_icms' => __convert_value_bd($request->perc_icms[$i]),
        'perc_pis' => __convert_value_bd($request->perc_pis[$i]),
        'perc_cofins' => __convert_value_bd($request->perc_cofins[$i]),
        'perc_ipi' => __convert_value_bd($request->perc_ipi[$i]),

    ]);
    return $p;
}

private function cadastrarTransportadora($request)
{
    if ($request->razao_social_transp) {
        $transportadora = Transportadora::create([
            'empresa_id' => $request->empresa_id,
            'razao_social' => $request->razao_social_transp,
            'nome_fantasia' => $request->nome_fantasia_transp,
            'cpf_cnpj' => $request->cpf_cnpj_transp,
            'ie' => $request->ie_transp,
            'antt' => $request->antt,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'cidade_id' => $request->cidade_id,
            'rua' => $request->rua_transp,
            'cep' => $request->cep_transp,
            'numero' => $request->numero_transp,
            'bairro' => $request->bairro_transp,
            'complemento' => $request->complemento_transp
        ]);
        return $transportadora->id;
    }
    return null;
}

private function atualizaFornecedor($request)
{
    $fornecedor = Fornecedor::findOrFail($request->fornecedor_id);
    $fornecedor->update([
        'razao_social' => $request->fornecedor_nome,
        'nome_fantasia' => $request->nome_fantasia,
        'cpf_cnpj' => $request->fornecedor_cpf_cnpj,
        'ie' => $request->ie,
        'contribuinte' => $request->contribuinte,
        'consumidor_final' => $request->consumidor_final,
        'email' => $request->email ?? '',
        'telefone' => $request->telefone ?? '',
        'cidade_id' => $request->fornecedor_cidade,
        'rua' => $request->fornecedor_rua,
        'cep' => $request->cep,
        'numero' => $request->fornecedor_numero,
        'bairro' => $request->fornecedor_bairro,
        'complemento' => $request->complemento
    ]);
    return $fornecedor->id;
}

public function infoValidade($id)
{
    $compra = Nfe::findOrFail($id);
    $produtos = [];
    foreach ($compra->itens as $i) {
        if ($i->produto->alerta_validade) {
            array_push($produtos, $i);
        }
    }
    return view('compras.info_validade', compact('produtos', 'compra'));
}

public function setarInfoValidade(Request $request)
{
    for ($i = 0; $i < sizeof($request->produto_id); $i++) {

        $item = ItemNfe::findOrFail($request->produto_id[$i]);
        $item->lote = $request->lote[$i];
        $item->data_vencimento = $request->data_vencimento[$i];
        $item->save();
    }
    session()->flash('flash_success', 'Validade definada com sucesso!');
    return redirect()->route('compras.index');
}

public function show($id){
    $data = Nfe::findOrFail($id);
    return view('nfe.show', compact('data'));
}
}
