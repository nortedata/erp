<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\FaturaNfce;
use App\Models\ItemNfce;
use App\Models\NaturezaOperacao;
use Illuminate\Http\Request;
use NFePHP\DA\NFe\Danfce;
use App\Models\Nfce;
use App\Models\Inutilizacao;
use App\Models\Produto;
use App\Models\ContaReceber;
use Illuminate\Support\Facades\DB;
use Spatie\FlareClient\View;
use App\Services\NFCeService;
use App\Utils\EstoqueUtil;

class NfceController extends Controller
{
    protected $util;

    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
    }

    private function setNumeroSequencial(){
        $docs = Nfce::where('empresa_id', request()->empresa_id)
        ->where('numero_sequencial', null)
        ->get();

        $last = Nfce::where('empresa_id', request()->empresa_id)
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
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $estado = $request->get('estado');


        $this->setNumeroSequencial();

        $data = Nfce::where('empresa_id', request()->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
        ->when($estado != "", function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return View('nfce.index', compact('data'));
    }

    public function create()
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        $sizeProdutos = Produto::where('empresa_id', request()->empresa_id)->count();
        if ($sizeProdutos == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um produto!");
            return redirect()->route('produtos.create');
        }
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        }
        $empresa = Empresa::findOrFail(request()->empresa_id);
        $numeroNfce = Nfce::lastNumero($empresa);

        return view('nfce.create', compact('cidades', 'naturezas', 'numeroNfce'));
    }

    public function edit($id)
    {
        $item = Nfce::findOrFail($id);
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        return view('nfce.edit', compact('item', 'cidades', 'naturezas'));
    }


    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $cliente_id = $request->cliente_id;
                $empresa = Empresa::findOrFail($request->empresa_id);

                if ($request->cliente_cpf_cnpj) {
                    $cliente_id = null;
                } else {
                    if ($request->cliente_id == null) {
                        if ($request->nome != '') {
                            $cliente_id = $this->cadastrarCliente($request);
                        }
                    } else {
                        $this->atualizaCliente($request);
                    }
                }
                $caixa = __isCaixaAberto();

                $config = Empresa::find($request->empresa_id);

                $tipoPagamento = $request->tipo_pagamento;
                $request->merge([
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'chave' => '',
                    'cliente_id' => $cliente_id,
                    'numero_serie' => $empresa->numero_serie_nfce,
                    'numero' => $request->numero_nfce,
                    'cliente_nome' => $request->cliente_nome ?? '',
                    'cliente_cpf_cnpj' => $request->cliente_cpf_cnpj ?? '',
                    'estado' => 'novo',
                    'total' => __convert_value_bd($request->valor_total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_produtos' => __convert_value_bd($request->valor_total) ?? 0,
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'caixa_id' => $caixa ? $caixa->id : null,
                    'tipo_pagamento' => $request->tipo_pagamento[0],
                    'dinheiro_recebido' => 0,
                    'troco' => 0,
                ]);

                // dd($request->all());
                $nfce = Nfce::create($request->all());

                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);

                    $variacao_id = isset($request->variacao_id[$i]) ? $request->variacao_id[$i] : null;

                    ItemNfce::create([
                        'nfce_id' => $nfce->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'valor_custo' => __convert_value_bd($product->valor_compra),
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
                        'codigo_beneficio_fiscal' => $request->codigo_beneficio_fiscal[$i],
                        'variacao_id' => $variacao_id
                    ]);

                    if ($product->gerenciar_estoque) {
                        $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id);
                    }

                    $tipo = 'reducao';
                    $codigo_transacao = $nfce->id;
                    $tipo_transacao = 'venda_nfce';

                    $this->util->movimentacaoProduto($product->id, __convert_value_bd($request->quantidade[$i]), $tipo, $codigo_transacao, $tipo_transacao, $variacao_id);
                }

                for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                    if ($tipoPagamento[$i]) {
                        FaturaNfce::create([
                            'nfce_id' => $nfce->id,
                            'tipo_pagamento' => $tipoPagamento[$i],
                            'data_vencimento' => $request->data_vencimento[$i],
                            'valor' => __convert_value_bd($request->valor_fatura[$i])
                        ]);

                        if ($request->gerar_conta_receber) {
                            ContaReceber::create([
                                'empresa_id' => $request->empresa_id,
                                'nfce_id' => $nfce->id,
                                'cliente_id' => $cliente_id,
                                'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                                'tipo_pagamento' => $request->tipo_pagamento[$i],
                                'data_vencimento' => $request->data_vencimento[$i],
                            ]);
                        }
                    }
                }
            });
session()->flash("flash_success", "NFCe cadastrada!");
} catch (\Exception $e) {
    // echo $e->getMessage() . '<br>' . $e->getLine();
    // die;
    session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
}
return redirect()->route('nfce.index');
}

public function update(Request $request, $id)
{
    try {
        DB::transaction(function () use ($request, $id) {
            $item = Nfce::findOrFail($id);
            $config = Empresa::find($request->empresa_id);

            $tipoPagamento = $request->tipo_pagamento;
            $request->merge([
                'emissor_nome' => $config->nome,
                'emissor_cpf_cnpj' => $config->cpf_cnpj,
                'ambiente' => $config->ambiente,
                'numero' => $request->numero_nfce,
                'estado' => 'novo',
                'total' => __convert_value_bd($request->valor_total) - __convert_value_bd($request->desconto) + __convert_value_bd($request->acrescimo),
                'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                'valor_produtos' => __convert_value_bd($request->valor_total) ?? 0,
                'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                'tipo_pagamento' => $request->tipo_pagamento[0],
            ]);

            $item->fill($request->all())->save();

            foreach ($item->itens as $i) {
                if ($i->produto->gerenciar_estoque) {
                    $this->util->incrementaEstoque($i->produto_id, $i->quantidade, $i->variacao_id);
                }
            }

            $item->itens()->delete();
            $item->fatura()->delete();

            for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                $product = Produto::findOrFail($request->produto_id[$i]);
                $variacao_id = isset($request->variacao_id[$i]) ? $request->variacao_id[$i] : null;

                ItemNfce::create([
                    'nfce_id' => $item->id,
                    'produto_id' => (int)$request->produto_id[$i],
                    'quantidade' => __convert_value_bd($request->quantidade[$i]),
                    'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                    'valor_custo' => __convert_value_bd($product->valor_compra),
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
                    'codigo_beneficio_fiscal' => $request->codigo_beneficio_fiscal[$i],
                    'variacao_id' => $variacao_id
                ]);
                if ($product->gerenciar_estoque) {
                    $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id);
                }
            }
            for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                if ($tipoPagamento[$i]) {
                    FaturaNfce::create([
                        'nfce_id' => $item->id,
                        'tipo_pagamento' => $tipoPagamento[$i],
                        'data_vencimento' => $request->data_vencimento[$i],
                        'valor' => __convert_value_bd($request->valor_fatura[$i])
                    ]);
                }
            }
        });
        session()->flash("flash_success", "NFCe alterada com sucesso!");
    } catch (\Exception $e) {
        echo $e->getMessage() . '<br>' . $e->getLine();
        die;
        session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
    }
    return redirect()->route('nfce.index');
}

private function cadastrarCliente($request)
{
    $cliente = Cliente::create([
        'empresa_id' => $request->empresa_id,
        'razao_social' => $request->nome,
        'nome_fantasia' => $request->nome_fantasia,
        'cpf_cnpj' => $request->cpf_cnpj,
        'ie' => $request->ie,
        'contribuinte' => $request->contribuinte,
        'consumidor_final' => $request->consumidor_final,
        'email' => $request->email ?? '',
        'telefone' => $request->telefone ?? '',
        'cidade_id' => $request->cliente_cidade,
        'rua' => $request->cliente_rua,
        'cep' => $request->cep,
        'numero' => $request->cliente_numero,
        'bairro' => $request->cliente_bairro,
        'complemento' => $request->complemento
    ]);
    return $cliente->id;
}

private function atualizaCliente($request)
{
    $cliente = Cliente::findOrFail($request->cliente_id);
    $cliente->update([
        'razao_social' => $request->nome,
        'nome_fantasia' => $request->nome_fantasia,
        'cpf_cnpj' => $request->cpf_cnpj,
        'ie' => $request->ie,
        'contribuinte' => $request->contribuinte,
        'consumidor_final' => $request->consumidor_final,
        'email' => $request->email ?? '',
        'telefone' => $request->telefone ?? '',
        'cidade_id' => $request->cliente_cidade,
        'rua' => $request->cliente_rua,
        'cep' => $request->cep,
        'numero' => $request->cliente_numero,
        'bairro' => $request->cliente_bairro,
        'complemento' => $request->complemento
    ]);
    return $cliente->id;
}

public function imprimir($id)
{
    $item = Nfce::findOrFail($id);

    if (file_exists(public_path('xml_nfce/') . $item->chave . '.xml')) {
        $xml = file_get_contents(public_path('xml_nfce/') . $item->chave . '.xml');
        $danfe = new Danfce($xml, $item);
        $pdf = $danfe->render();
        return response($pdf)
        ->header('Content-Type', 'application/pdf');
    } else {
        session()->flash("flash_error", "Arquivo não encontrado");
        return redirect()->back();
    }
}

public function xmlTemp($id)
{
    $item = Nfce::findOrFail($id);

    $empresa = $item->empresa;

    if ($empresa->arquivo == null) {
        session()->flash("flash_error", "Certificado não encontrado para este emitente");
        return redirect()->route('config.index');
    }

    $nfe_service = new NFCeService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$empresa->ambiente,
        "razaosocial" => $empresa->nome,
        "siglaUF" => $empresa->cidade->uf,
        "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
        "CSC" => $empresa->csc,
        "CSCid" => $empresa->csc_id
    ], $empresa);

    $doc = $nfe_service->gerarXml($item);

    if (!isset($doc['erros_xml'])) {
        $xml = $doc['xml'];

        return response($xml)
        ->header('Content-Type', 'application/xml');
    } else {
        return response()->json($doc['erros_xml'], 401);
    }
}

public function destroy($id)
{
    $item = Nfce::findOrFail($id);
    try {
        foreach ($item->itens as $i) {
            if ($i->produto->gerenciar_estoque) {
                $this->util->incrementaEstoque($i->produto_id, $i->quantidade, $i->variacao_id);
            }
        }
        $item->itens()->delete();
        $item->fatura()->delete();
        $item->contaReceber()->delete();
        $item->delete();
        session()->flash("flash_success", "NFCe removida!");
    } catch (\Exception $e) {
        echo $e->getMessage() . '<br>' . $e->getLine();
        die;
        session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
    }
    return redirect()->route('nfce.index');
}

public function inutilizar(Request $request)
{
    $start_date = $request->get('start_date');
    $end_date = $request->get('end_date');
    $data = Inutilizacao::where('empresa_id', request()->empresa_id)
    ->where('modelo', '65')->orderBy('id', 'desc')
    ->when(!empty($start_date), function ($query) use ($start_date) {
        return $query->whereDate('created_at', '>=', $start_date);
    })
    ->when(!empty($end_date), function ($query) use ($end_date,) {
        return $query->whereDate('created_at', '<=', $end_date);
    })
    ->get();
    $modelo = '65';
    return view('inutilizacao.index', compact('data', 'modelo'));
}

public function inutilStore(Request $request)
{
    $request->merge([
        'estado' => 'novo',
        'modelo' => '65'
    ]);
    try {
        Inutilizacao::create($request->all());
        session()->flash("flash_success", "Inutilização criada!");
    } catch (\Exception $e) {
        echo $e->getMessage() . '<br>' . $e->getLine();
        die;
        session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
    }
    return redirect()->back();
}

public function inutilDestroy($id)
{
    $item = Inutilizacao::findOrFail($id);
    try {
        $item->delete();
        session()->flash("flash_success", "Inutilização removida!");
    } catch (\Exception $e) {
        session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
    }
    return redirect()->back();
}

public function alterarEstado($id)
{
    $item = Nfce::findOrFail($id);
    return view('nfce.estado_fiscal', compact('item'));
}

public function storeEstado(Request $request, $id)
{
    $item = Nfce::findOrFail($id);
    try {
        $item->estado = $request->estado_emissao;
        if ($request->hasFile('file')) {
            $xml = simplexml_load_file($request->file);
            $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
            $file = $request->file;
            $file->move(public_path('xml_nfce/'), $chave . '.xml');
            $item->chave = $chave;
            $item->data_emissao = date('Y-m-d H:i:s');
            $item->numero = (int)$xml->NFe->infNFe->ide->nNF;
        }
        $item->save();
        session()->flash("flash_success", "Estado alterado");
    } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }
    return redirect()->route('nfce.index');
}

public function show($id)
{
    $data = Nfce::findOrFail($id);

    return view('nfce.show', compact('data'));
}
}
