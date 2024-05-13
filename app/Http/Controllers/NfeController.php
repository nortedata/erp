<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\FaturaNfe;
use App\Models\ItemNfe;
use App\Models\NaturezaOperacao;
use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\PedidoEcommerce;
use App\Models\Cotacao;
use App\Models\Produto;
use App\Models\Inutilizacao;
use App\Models\Transportadora;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Laravel\Ui\Presets\React;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\DanfeSimples;
use NFePHP\DA\NFe\DanfeEtiqueta;
use App\Services\NFeService;
use NFePHP\DA\NFe\Daevento;
use App\Utils\EstoqueUtil;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\OrdemServico;
use Dompdf\Dompdf;

class NfeController extends Controller
{
    protected $util;

    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;

        if (!is_dir(public_path('xml_nfe'))) {
            mkdir(public_path('xml_nfe'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfe_cancelada'))) {
            mkdir(public_path('xml_nfe_cancelada'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfe_correcao'))) {
            mkdir(public_path('xml_nfe_correcao'), 0777, true);
        }
        if (!is_dir(public_path('danfe_temp'))) {
            mkdir(public_path('danfe_temp'), 0777, true);
        }

        if (!is_dir(public_path('danfe'))) {
            mkdir(public_path('danfe'), 0777, true);
        }
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
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $estado = $request->get('estado');
        $tpNF = $request->get('tpNF');

        $this->setNumeroSequencial();
        if ($tpNF == "") {
            $tpNF = 1;
        }
        $data = Nfe::where('empresa_id', request()->empresa_id)->where('orcamento', 0)
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
        ->when($tpNF != "-", function ($query) use ($tpNF) {
            return $query->where('tpNF', $tpNF);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('nfe.index', compact('data'));
    }

    public function create()
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($clientes) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um cliente!");
            return redirect()->route('clientes.create');
        }
        $sizeProdutos = Produto::where('empresa_id', request()->empresa_id)->count();
        if ($sizeProdutos == 0) {
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

        return view(
            'nfe.create',
            compact('clientes', 'transportadoras', 'cidades', 'naturezas', 'numeroNfe', 'empresa')
        );
    }

    public function edit($id)
    {
        $item = Nfe::findOrFail($id);

        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();

        return view('nfe.edit', compact('item', 'transportadoras', 'cidades', 'naturezas'));
    }

    public function imprimir($id)
    {
        $item = Nfe::findOrFail($id);

        if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $item->chave . '.xml');

            $danfe = new Danfe($xml);
            $pdf = $danfe->render();
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    public function downloadXml($id)
    {
        $item = Nfe::findOrFail($id);
        if($item->estado == 'aprovado'){
            if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
                return response()->download(public_path('xml_nfe/') . $item->chave . '.xml');
            } else {
                session()->flash("flash_error", "Arquivo não encontrado");
                return redirect()->back();
            }
        }elseif($item->estado == 'cancelado'){
            if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
                return response()->download(public_path('xml_nfe_cancelada/') . $item->chave . '.xml');
            } else {
                session()->flash("flash_error", "Arquivo não encontrado");
                return redirect()->back();
            }
        }else{
            session()->flash("flash_error", "Nada encontrado");
            return redirect()->back();
        }
    }

    public function danfeSimples($id)
    {
        $item = Nfe::findOrFail($id);

        if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $item->chave . '.xml');
            try {
                $danfe = new DanfeSimples($xml);
                $danfe->debugMode(false);
                $pdf = $danfe->render();
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    public function danfeEtiqueta($id)
    {
        $item = Nfe::findOrFail($id);

        if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe/') . $item->chave . '.xml');
            try {
                $danfe = new DanfeEtiqueta($xml);
                $danfe->debugMode(false);
                $pdf = $danfe->render();
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    public function imprimirCancela($id)
    {
        $item = Nfe::findOrFail($id);

        $xml = file_get_contents(public_path('xml_nfe_cancelada/') . $item->chave . '.xml');
        $dadosEmitente = $this->getEmitente($item->empresa);

        $daevento = new Daevento($xml, $dadosEmitente);
        $daevento->debugMode(true);
        $pdf = $daevento->render();
        return response($pdf)
        ->header('Content-Type', 'application/pdf');
    }

    public function imprimirCorrecao($id)
    {
        $item = Nfe::findOrFail($id);
        $xml = file_get_contents(public_path('xml_nfe_correcao/') . $item->chave . '.xml');
        $dadosEmitente = $this->getEmitente($item->empresa);
        $daevento = new Daevento($xml, $dadosEmitente);
        $daevento->debugMode(true);
        $pdf = $daevento->render();
        return response($pdf)
        ->header('Content-Type', 'application/pdf');
    }

    private function getEmitente($empresa)
    {
        return [
            'razao' => $empresa->nome,
            'logradouro' => $empresa->rua,
            'numero' => $empresa->numero,
            'complemento' => '',
            'bairro' => $empresa->bairro,
            'CEP' => preg_replace('/[^0-9]/', '', $empresa->cep),
            'municipio' => $empresa->cidade->nome,
            'UF' => $empresa->cidade->uf,
            'telefone' => $empresa->telefone,
            'email' => ''
        ];
    }

    public function store(Request $request)
    {

        try {
            $nfe = DB::transaction(function () use ($request) {
                $cliente_id = isset($request->cliente_id) ? $request->cliente_id : null;
                $fornecedor_id = isset($request->fornecedor_id) ? $request->fornecedor_id : null;
                $empresa = Empresa::findOrFail($request->empresa_id);

                if (isset($request->cliente_id)) {
                    if ($request->cliente_id == null) {
                        $cliente_id = $this->cadastrarCliente($request);
                    } else {
                        $this->atualizaCliente($request);
                    }
                }
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

                $tipoPagamento = $request->tipo_pagamento;

                $caixa = __isCaixaAberto();
                $valor_produto =  number_format($request->valor_produtos, 2);
                $request->merge([
                    'emissor_nome' => $config->nome,
                    'emissor_cpf_cnpj' => $config->cpf_cnpj,
                    'ambiente' => $config->ambiente,
                    'chave' => '',
                    'cliente_id' => $cliente_id,
                    'fornecedor_id' => $fornecedor_id,
                    'transportadora_id' => $transportadora_id,
                    'numero_serie' => $empresa->numero_serie_nfe,
                    'numero' => $request->numero_nfe,
                    'estado' => 'novo',
                    'total' => __convert_value_bd($request->valor_total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_produtos' => __convert_value_bd($valor_produto),
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'caixa_id' => $caixa ? $caixa->id : null,
                    'tipo_pagamento' => $request->tipo_pagamento[0],
                    // 'bandeira_cartao' => $request->bandeira_cartao ?? null,
                    // 'cnpj_cartao' => $request->cnpj_cartao ?? null,
                    // 'cAut_cartao' => $request->cAut_cartao ?? null
                ]);

                if($request->orcamento){
                    $request->merge([
                        'gerar_conta_receber' => 0,
                    ]);
                }
                $nfe = Nfe::create($request->all());
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    $variacao_id = isset($request->variacao_id[$i]) ? $request->variacao_id[$i] : null;
                    ItemNfe::create([
                        'nfe_id' => $nfe->id,
                        'produto_id' => (int)$request->produto_id[$i],
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
                        'codigo_beneficio_fiscal' => $request->codigo_beneficio_fiscal[$i],
                        'variacao_id' => $variacao_id
                    ]);

                    if ($product->gerenciar_estoque && $request->orcamento == 0) {
                        if (isset($request->is_compra)) {
                            $this->util->incrementaEstoque($product->id, __convert_value_bd($request->quantidade[$i]), 
                                $variacao_id);
                        } else {
                            $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), 
                                $variacao_id);
                        }
                    }

                    if ($request->is_compra) {
                        $tipo = 'incremento';
                        $codigo_transacao = $nfe->id;
                        $tipo_transacao = 'compra';
                        $this->util->movimentacaoProduto($product->id, __convert_value_bd($request->quantidade[$i]), $tipo, $codigo_transacao, $tipo_transacao, $variacao_id);
                    } else {
                        $tipo = 'reducao';
                        $codigo_transacao = $nfe->id;
                        $tipo_transacao = 'venda_nfe';
                        $this->util->movimentacaoProduto($product->id, __convert_value_bd($request->quantidade[$i]), $tipo, $codigo_transacao, $tipo_transacao, $variacao_id);
                    }
                }

                if($request->tipo_pagamento){
                    if ($request->tipo_pagamento[0] != '') {
                        for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                            FaturaNfe::create([
                                'nfe_id' => $nfe->id,
                                'tipo_pagamento' => $tipoPagamento[$i],
                                'data_vencimento' => $request->data_vencimento[$i],
                                'valor' => __convert_value_bd($request->valor_fatura[$i])
                            ]);
                        }

                        if ($request->tpNF == 1) {
                            for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                                if ($request->gerar_conta_receber) {
                                    ContaReceber::create([
                                        'empresa_id' => $request->empresa_id,
                                        'nfe_id' => $nfe->id,
                                        'cliente_id' => $cliente_id,
                                        'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                                        'tipo_pagamento' => $tipoPagamento[$i],
                                        'data_vencimento' => $request->data_vencimento[$i],
                                    ]);
                                }
                            }
                        } else {
                            for ($i = 0; $i < sizeof($tipoPagamento); $i++) {
                                if ($request->gerar_conta_pagar) {
                                    ContaPagar::create([
                                        'empresa_id' => $request->empresa_id,
                                        'nfe_id' => $nfe->id,
                                        'fornecedor_id' => $fornecedor_id,
                                        'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                                        'tipo_pagamento' => $tipoPagamento[$i],
                                        'data_vencimento' => $request->data_vencimento[$i],
                                    ]);
                                }
                            }
                        }
                    }
                }
                // dd($request);
                if ($request->ordem_servico_id) {
                    $ordem = OrdemServico::findOrFail($request->ordem_servico_id);
                    $ordem->nfe_id = $nfe->id;
                    $ordem->save();
                }

                if ($request->pedido_ecommerce_id) {
                    $pedido = PedidoEcommerce::findOrFail($request->pedido_ecommerce_id);
                    $pedido->nfe_id = $nfe->id;
                    $pedido->estado = 'finalizado';
                    $pedido->save();
                }
                if ($request->cotacao_id) {
                    $cotacao = Cotacao::findOrFail($request->cotacao_id);
                    $cotacao->nfe_id = $nfe->id;
                    $cotacao->escolhida = 1;
                    $cotacao->estado = 'aprovada';
                    $cotacao->save();
                }
                return $nfe;
            });
session()->flash("flash_success", "NFe cadastrada!");
} catch (\Exception $e) {
    // echo $e->getMessage() . '<br>' . $e->getLine();
    // die;
    session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
}
if ($request->orcamento == 1) {
    session()->flash("flash_success", "Orçamento cadastrada!");
    return redirect()->route('orcamentos.index');
}
if (isset($request->is_compra)) {
    session()->flash("flash_success", "Compra cadastrada!");
    if ($nfe->isItemValidade()) {
        return redirect()->route('compras.info-validade', $nfe->id);
    }
    return redirect()->route('compras.index');
} else {
    return redirect()->route('nfe.index');
}
}

private function cadastrarCliente($request)
{
    $cliente = Cliente::create([
        'empresa_id' => $request->empresa_id,
        'razao_social' => $request->cliente_nome,
        'nome_fantasia' => $request->nome_fantasia ?? '',
        'cpf_cnpj' => $request->cliente_cpf_cnpj,
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
        'razao_social' => $request->cliente_nome,
        'nome_fantasia' => $request->nome_fantasia ?? '',
        'cpf_cnpj' => $request->cliente_cpf_cnpj,
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

private function cadastrarFornecedor($request)
{
    $fornecedor = Fornecedor::create([
        'empresa_id' => $request->empresa_id,
        'razao_social' => $request->fornecedor_nome,
        'nome_fantasia' => $request->nome_fantasia ?? '',
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

private function atualizaFornecedor($request)
{
    $fornecedor = Fornecedor::findOrFail($request->fornecedor_id);
    $fornecedor->update([
        'razao_social' => $request->fornecedor_nome,
        'nome_fantasia' => $request->nome_fantasia ?? '',
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

private function cadastrarTransportadora($request)
{
    if ($request->razao_social_transp) {
        $transportadora = Transportadora::create([
            'empresa_id' => $request->empresa_id,
            'razao_social' => $request->razao_social_transp,
            'nome_fantasia' => $request->nome_fantasia_transp ?? '',
            'cpf_cnpj' => $request->cpf_cnpj_transp,
            'ie' => $request->ie_transp,
            'antt' => $request->antt,
            'email' => $request->email_transp,
            'telefone' => $request->telefone_transp,
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

private function atualizaTransportadora($request)
{
    if ($request->razao_social_transp) {
        $transportadora = Transportadora::findOrFail($request->transportadora_id);
        $transportadora->update([
            'empresa_id' => $request->empresa_id,
            'razao_social' => $request->razao_social_transp,
            'nome_fantasia' => $request->nome_fantasia_transp ?? '',
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


public function update(Request $request, $id)
{
        // dd($request);
    try {
        DB::transaction(function () use ($request, $id) {
            $item = Nfe::findOrFail($id);
            $transportadora_id = $request->transportadora_id;
            if ($request->transportadora_id == null) {
                $transportadora_id = $this->cadastrarTransportadora($request);
            }
            $config = Empresa::find($request->empresa_id);
            $request->merge([
                'emissor_nome' => $config->nome,
                'emissor_cpf_cnpj' => $config->cpf_cnpj,
                'ambiente' => $config->ambiente,
                'chave' => '',
                'transportadora_id' => $transportadora_id,
                'numero' => $request->numero_nfe,
                'total' => __convert_value_bd($request->valor_total),
                'desconto' => __convert_value_bd($request->desconto),
                'acrescimo' => __convert_value_bd($request->acrescimo),
                'valor_produtos' => __convert_value_bd($request->valor_total) ?? 0,
                'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0
            ]);

            $item->fill($request->all())->save();

            $item->itens()->delete();
            $item->fatura()->delete();

            for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                $product = Produto::findOrFail($request->produto_id[$i]);
                $variacao_id = isset($request->variacao_id[$i]) ? $request->variacao_id[$i] : null;

                ItemNfe::create([
                    'nfe_id' => $item->id,
                    'produto_id' => (int)$request->produto_id[$i],
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
                    'codigo_beneficio_fiscal' => $request->codigo_beneficio_fiscal[$i],
                    'variacao_id' => $variacao_id
                ]);

                if ($product->gerenciar_estoque && $item->orcamento == 0) {
                    if (isset($request->is_compra)) {
                        $this->util->incrementaEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id);
                    } else {
                        $this->util->reduzEstoque($product->id, __convert_value_bd($request->quantidade[$i]), $variacao_id);
                    }
                }
            }

            ContaReceber::where('nfe_id', $item->id)->delete();
            ContaPagar::where('nfe_id', $item->id)->delete();
            FaturaNfe::where('nfe_id', $item->id)->delete();

            if ($request->tpNF == 1) {

                if ($request->gerar_conta_receber) {
                    ContaReceber::create([
                        'empresa_id' => $request->empresa_id,
                        'nfe_id' => $item->id,
                        'cliente_id' => $request->cliente_id,
                        'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                        'tipo_pagamento' => $request->tipo_pagamento[$i],
                        'data_vencimento' => $request->data_vencimento[$i],
                    ]);
                }
            } else {
                if ($request->gerar_conta_pagar) {
                    ContaPagar::create([
                        'empresa_id' => $request->empresa_id,
                        'nfe_id' => $item->id,
                        'fornecedor_id' => $request->fornecedor_id,
                        'valor_integral' => __convert_value_bd($request->valor_fatura[$i]),
                        'tipo_pagamento' => $request->tipo_pagamento[$i],
                        'data_vencimento' => $request->data_vencimento[$i],
                    ]);
                }
            }

            if ($request->tipo_pagamento[0] != '') {
                for ($i = 0; $i < sizeof($request->tipo_pagamento); $i++) {
                    FaturaNfe::create([
                        'nfe_id' => $item->id,
                        'tipo_pagamento' => $request->tipo_pagamento[$i],
                        'data_vencimento' => $request->data_vencimento[$i],
                        'valor' => __convert_value_bd($request->valor_fatura[$i])
                    ]);
                }
            }
        });
session()->flash("flash_success", "NFe alterada com sucesso!");
} catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
    session()->flash("flash_error", 'Algo deu errado: ' . $e->getMessage());
}

$item = Nfe::findOrFail($id);

if ($item->orcamento == 1) {
    session()->flash("flash_success", "Orçamento atualizado!");
    return redirect()->route('orcamentos.index');
}
if (isset($request->is_compra)) {
    session()->flash("flash_success", "Compra atualizada!");
    return redirect()->route('compras.index');
} else {
    return redirect()->route('nfe.index');
}
}


public function destroy($id)
{
    $item = Nfe::findOrFail($id);
    try {

        foreach ($item->itens as $i) {
            if ($i->produto->gerenciar_estoque) {
                $this->util->incrementaEstoque($i->produto_id, $i->quantidade, $i->variacao_id);
            }
        }
        $item->itens()->delete();
        $item->fatura()->delete();
        $item->delete();
        session()->flash("flash_success", "NFe removida!");
    } catch (\Exception $e) {
        echo $e->getLine();
        die;
        session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
    }
    return redirect()->route('nfe.index');
}

public function xmlTemp($id)
{
    $item = Nfe::findOrFail($id);

    $empresa = $item->empresa;

    if ($empresa->arquivo == null) {
        session()->flash("flash_error", "Certificado não encontrado para este emitente");
        return redirect()->route('config.index');
    }

    $nfe_service = new NFeService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$empresa->ambiente,
        "razaosocial" => $empresa->nome,
        "siglaUF" => $empresa->cidade->uf,
        "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
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

public function danfeTemp($id)
{
    $item = Nfe::findOrFail($id);

    $empresa = $item->empresa;

    if ($empresa->arquivo == null) {
        session()->flash("flash_error", "Certificado não encontrado para este emitente");
        return redirect()->route('config.index');
    }

    $nfe_service = new NFeService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$empresa->ambiente,
        "razaosocial" => $empresa->nome,
        "siglaUF" => $empresa->cidade->uf,
        "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
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

public function inutilizar(Request $request)
{
    $start_date = $request->get('start_date');
    $end_date = $request->get('end_date');
    $data = Inutilizacao::where('empresa_id', request()->empresa_id)
    ->where('modelo', '55')->orderBy('id', 'desc')
    ->when(!empty($start_date), function ($query) use ($start_date) {
        return $query->whereDate('created_at', '>=', $start_date);
    })
    ->when(!empty($end_date), function ($query) use ($end_date,) {
        return $query->whereDate('created_at', '<=', $end_date);
    })
    ->get();
    $modelo = '55';
    return view('inutilizacao.index', compact('data', 'modelo'));
}

public function inutilStore(Request $request)
{
    $request->merge([
        'estado' => 'novo',
        'modelo' => '55'
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
    $item = Nfe::findOrFail($id);
    return view('nfe.estado_fiscal', compact('item'));
}

public function storeEstado(Request $request, $id)
{
    $item = Nfe::findOrFail($id);
    try {
        $item->estado = $request->estado_emissao;
        if ($request->hasFile('file')) {
            $xml = simplexml_load_file($request->file);
            $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
            $file = $request->file;
            $file->move(public_path('xml_nfe/'), $chave . '.xml');
            $item->chave = $chave;
            $item->data_emissao = date('Y-m-d H:i:s');
            $item->numero = (int)$xml->NFe->infNFe->ide->nNF;
        }
        $item->save();
        session()->flash("flash_success", "Estado alterado");
    } catch (\Exception $e) {
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }
    return redirect()->route('nfe.index');
}

public function imprimirVenda($id)
{
    $item = Nfe::findOrFail($id);

    $config = Empresa::where('id', $item->empresa_id)->first();

    $p = view('nfe.imprimir', compact('config', 'item'));

    $domPdf = new Dompdf(["enable_remote" => true]);
    $domPdf->loadHtml($p);
    $pdf = ob_get_clean();
    $domPdf->setPaper("A4");
    $domPdf->render();
    $domPdf->stream("Venda de Produtos $id.pdf", array("Attachment" => false));
}

public function show($id)
{
    $data = Nfe::findOrFail($id);
    return view('nfe.show', compact('data'));
}
}
