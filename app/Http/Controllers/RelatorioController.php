<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProduto;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\ComissaoVenda;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Fornecedor;
use App\Models\ItemNfe;
use App\Models\ItemNfce;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\Cte;
use App\Models\Mdfe;
use App\Models\Funcionario;
use App\Models\Marca;
use App\Models\TaxaPagamento;
use Dompdf\Dompdf;

class RelatorioController extends Controller
{
    public function index()
    {
        $marcas = Marca::where('empresa_id', request()->empresa_id)->get();
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        return view('relatorios.index', compact('funcionarios', 'marcas', 'categorias'));
    }

    public function produtos(Request $request)
    {
        // dd($request);
        $estoque = $request->estoque;
        $tipo = $request->tipo;
        $marca_id = $request->marca_id;
        $categoria_id = $request->categoria_id;
        $data = Produto::select('produtos.*')
        ->where('empresa_id', $request->empresa_id)
        ->when($estoque != '', function ($query) use ($estoque) {
            if ($estoque == 1) {
                return $query->join('estoques', 'estoques.produto_id', '=', 'produtos.id')
                ->where('estoques.quantidade', '>', 0);
            } else {
                return $query->leftJoin('estoques', 'estoques.produto_id', '=', 'produtos.id')
                ->whereNull('estoques.produto_id')
                ->orWhere(function ($q) use ($query) {
                    return $q->join('estoques', 'estoques.produto_id', '=', 'produtos.id')
                    ->where('estoques.quantidade', '=', 0);
                });
            }
        })
        ->when(!empty($categoria_id), function ($query) use ($categoria_id) {
            return $query->where('categoria_id', $categoria_id);
        })
        ->when(!empty($marca_id), function ($query) use ($marca_id) {
            return $query->where('marca_id', $marca_id);
            
        })
        ->get();

        if ($tipo != '') {
            foreach ($data as $item) {
                $sumNfe = ItemNfe::where('produto_id', $item->id)
                ->sum('quantidade');

                $sumNfce = ItemNfce::where('produto_id', $item->id)
                ->sum('quantidade');

                $item->quantidade_vendida = $sumNfe + $sumNfce;
            }

            if ($tipo == 1) {
                $data = $data->sortByDesc('quantidade_vendida');
            } else {
                $data = $data->sortBy('quantidade_vendida');
            }
        }

        $marca = null;
        if($marca_id != null){
            $marca = Marca::findOrFail($marca_id);
        }

        $categoria = null;
        if($categoria_id != null){
            $categoria = CategoriaProduto::findOrFail($categoria_id);
        }


        $p = view('relatorios/produtos', compact('data', 'tipo', 'marca', 'categoria'))
        ->with('title', 'Relatório de Produtos');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Relatório de Produtos.pdf", array("Attachment" => false));
    }

    public function clientes(Request $request)
    {
        $tipo = $request->tipo;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = Cliente::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })->get();

        if ($tipo != '') {
            foreach ($data as $item) {
                $sumNfe = Nfe::where('cliente_id', $item->id)
                ->sum('total');

                $sumNfce = Nfce::where('cliente_id', $item->id)
                ->sum('total');

                $item->total = $sumNfe + $sumNfce;
            }

            if ($tipo == 1) {
                $data = $data->sortByDesc('total');
            } else {
                $data = $data->sortBy('total');
            }
        }

        $p = view('relatorios/clientes', compact('data', 'tipo'))
        ->with('title', 'Relatório de Clientes');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Clientes.pdf", array("Attachment" => false));
    }

    public function fornecedores(Request $request)
    {
        $tipo = $request->tipo;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = Fornecedor::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })->get();

        if ($tipo != '') {
            foreach ($data as $item) {
                $sumNfe = Nfe::where('fornecedor_id', $item->id)
                ->where('tpNF', 0)
                ->sum('total');

                $item->total = $sumNfe;
            }

            if ($tipo == 1) {
                $data = $data->sortByDesc('total');
            } else {
                $data = $data->sortBy('total');
            }
        }

        $p = view('relatorios/fornecedores', compact('data', 'tipo'))
        ->with('title', 'Relatório de Fornecedores');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Fornecedores.pdf", array("Attachment" => false));
    }

    public function nfe(Request $request)
    {
        $tipo = $request->tipo;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $finNFe = $request->finNFe;
        $cliente = $request->cliente;
        $estado = $request->estado;

        $data = Nfe::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data_emissao', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('data_emissao', '<=', $end_date);
        })
        ->when(!empty($cliente), function ($query) use ($cliente) {
            return $query->where('cliente_id', $cliente);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->when(!empty($tipo), function ($query) use ($tipo) {
            return $query->where('tpNF', $tipo);
        })
        ->when(!empty($finNFe), function ($query) use ($finNFe) {
            return $query->where('finNFe', $finNFe);
        })->get();


        $p = view('relatorios/nfe', compact('data'))
        ->with('title', 'Relatório de NFe');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de NFe.pdf", array("Attachment" => false));
    }

    public function nfce(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $cliente_id = $request->cliente_id;
        $estado = $request->estado;

        $data = Nfce::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data_emissao', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('data_emissao', '<=', $end_date);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })->get();

        $p = view('relatorios/nfce', compact('data'))
        ->with('title', 'Relatório de NFCe');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de NFCe.pdf", array("Attachment" => false));
    }

    public function cte(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $estado = $request->estado;

        $data = Cte::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->get();


        $p = view('relatorios/cte', compact('data'))
        ->with('title', 'Relatório de CTe');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de CTe.pdf", array("Attachment" => false));
    }

    public function mdfe(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $estado = $request->estado;

        $data = Mdfe::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado_emissao', $estado);
        })
        ->get();


        $p = view('relatorios/mdfe', compact('data'))
        ->with('title', 'Relatório de MDFe');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de MDFe.pdf", array("Attachment" => false));
    }

    public function conta_pagar(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $status = $request->status;

        $data = ContaPagar::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($status), function ($query) use ($status) {
            if ($status == -1) {
                return $query->where('status', '!=', 1);
            } else {
                return $query->where('status', $status);
            }
        })
        ->get();

        $p = view('relatorios/conta_pagar', compact('data'))
        ->with('title', 'Relatório de Contas a Pagar');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Contas a Pagar.pdf", array("Attachment" => false));
    }

    public function conta_receber(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $status = $request->status;

        $data = ContaReceber::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($status), function ($query) use ($status) {
            if ($status == -1) {
                return $query->where('status', '!=', 1);
            } else {
                return $query->where('status', $status);
            }
        })
        ->get();

        $p = view('relatorios/conta_receber', compact('data'))
        ->with('title', 'Relatório de Contas a Receber');

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Contas a Receber.pdf", array("Attachment" => false));
    }

    public function comissao(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $funcionario_id = $request->funcionario_id;

        $data = ComissaoVenda::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($funcionario_id), function ($query) use ($funcionario_id) {
            return $query->where('funcionario_id', $funcionario_id);
        })
        ->get();

        $p = view('relatorios/comissao', compact('data'))
        ->with('title', 'Relatório de Comissao');

        // if ($funcionario_id == null) {
        //     session()->flash('flash_error', 'Selecione um funcionário para continuar');
        //     return redirect()->back();
        // }

        $p = view('relatorios/comissao', compact('data'))
        ->with('funcionário', $funcionario_id)
        ->with('title', 'Relatório de Comissão');

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Comissão.pdf", array("Attachment" => false));
    }

    public function vendas(Request $request)
    {
        $tipo = $request->tipo;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $vendas = Nfe::where('empresa_id', $request->empresa_id)->where('tpNF', 1)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->where('nves.empresa_id', $request->empresa_id)
        ->where('nves.estado', '!=', 'cancelado')
        ->limit($total_resultados ?? 1000000)
        ->get();

        $vendasCaixa = Nfce::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->where('nfces.empresa_id', $request->empresa_id)
        ->where('nfces.estado', '!=', 'cancelado')
        ->limit($total_resultados ?? 1000000)
        ->get();

        $data = $this->uneArrayVendas($vendas, $vendasCaixa);
        // dd($data);
        $p = view('relatorios/vendas', compact('data', 'tipo'))
        ->with('title', 'Relatório de Vendas');
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Vendas.pdf", array("Attachment" => false));
    }

    private function uneArrayVendas($vendas, $vendasCaixa)
    {
        $adicionados = [];
        $arr = [];
        foreach ($vendas as $v) {
            $temp = [
                'id' => $v->id,
                'data' => $v->created_at,
                'total' => $v->total,
                'cliente' => $v->cliente->info
                // 'itens' => $v->itens
            ];
            array_push($adicionados, $v->id);
            array_push($arr, $temp);
        }
        foreach ($vendasCaixa as $v) {
            $temp = [
                'id' => $v->id,
                'data' => $v->created_at,
                'total' => $v->total,
                'cliente' => $v->cliente ? $v->cliente->info : '--'
                // 'itens' => $v->itens
            ];
            array_push($adicionados, $v->id);
            array_push($arr, $temp);
        }
        return $arr;
    }

    public function compras(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = Nfe::where('empresa_id', request()->empresa_id)->where('tpNF', 0)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->where('nves.empresa_id', $request->empresa_id)
        ->limit($total_resultados ?? 1000000)
        ->get();

        $p = view('relatorios/compras', compact('data'))
        ->with('title', 'Relatório de Compras');
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de Compras.pdf", array("Attachment" => false));
    }

    public function taxas(Request $request)
    {
        $data_inicial = $request->data_inicial;
        $data_final = $request->data_final;

        if ($data_final && $data_final) {
            $data_inicial = $this->parseDate($data_inicial);
            $data_final = $this->parseDate($data_final);
        }
        $taxas = TaxaPagamento::where('empresa_id', request()->empresa_id)->get();
        $tipos = $taxas->pluck('tipo_pagamento')->toArray();
        $vendas = Nfe::where('empresa_id', request()->empresa_id)
        ->when($data_inicial != '', function ($q) use ($data_inicial) {
            return $q->whereDate('created_at', '>=', $data_inicial);
        })
        ->when($data_final != '', function ($q) use ($data_final) {
            return $q->whereDate('created_at', '<=', $data_final);
        })
        ->get();

        $data = [];
        foreach ($vendas as $v) {
            $bandeira_cartao = $v->bandeira_cartao;
            if (sizeof($v->fatura) > 1) {
                foreach ($v->fatura as $ft) {
                    $fp = $ft->tipo_pagamento;
                    if (in_array($fp, $tipos)) {
                        $taxa = TaxaPagamento::where('empresa_id', request()->empresa_id)
                        ->where('tipo_pagamento', $fp)
                        ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                            return $q->where('bandeira_cartao', $bandeira_cartao);
                        })
                        ->first();
                        if ($taxa != null) {
                            $item = [
                                'cliente' => $v->cliente ? ($v->cliente->razao_social . " " . $v->cliente->cpf_cnpj) :
                                'Consumidor final',
                                'total' => $ft->valor,
                                'taxa_perc' => $taxa ? $taxa->taxa : 0,
                                'taxa' => $taxa ? ($ft->valor * ($taxa->taxa / 100)) : 0,
                                'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                                'tipo_pagamento' => Nfe::getTipo($fp),
                                'venda_id' => $v->id,
                                'tipo' => 'PEDIDO'
                            ];
                            array_push($data, $item);
                        }
                    }
                }
            } else {
                if (in_array($v->tipo_pagamento, $tipos)) {
                    $total = $v->valor_total - $v->desconto + $v->acrescimo;
                    $taxa = TaxaPagamento::where('empresa_id', request()->empresa_id)
                    ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                        return $q->where('bandeira_cartao', $bandeira_cartao);
                    })
                    ->where('tipo_pagamento', $v->tipo_pagamento)->first();
                    if ($taxa != null) {
                        $item = [
                            'cliente' => $v->cliente->razao_social,
                            'total' => $v->total,
                            'taxa_perc' => $taxa->taxa,
                            'taxa' => $taxa ? ($total * ($taxa->taxa / 100)) : 0,
                            'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                            'tipo_pagamento' => Nfe::getTipo($v->tipo_pagamento),
                            'venda_id' => $v->id,
                            'tipo' => 'PEDIDO'
                        ];
                        array_push($data, $item);
                    } else {
                        echo $bandeira_cartao;
                        die;
                    }
                }
            }
        }

        $vendasCaixa = Nfce::where('empresa_id', request()->empresa_id)
        ->when($data_inicial != '', function ($q) use ($data_inicial) {
            return $q->whereDate('created_at', '>=', $data_inicial);
        })
        ->when($data_final != '', function ($q) use ($data_final) {
            return $q->whereDate('created_at', '<=', $data_final);
        })
        ->get();

        foreach ($vendasCaixa as $v) {
            $bandeira_cartao = $v->bandeira_cartao;
            if (sizeof($v->fatura) > 1) {
                foreach ($v->fatura as $ft) {
                    if (in_array($ft->tipo_pagamento, $tipos)) {
                        $taxa = TaxaPagamento::where('empresa_id', request()->empresa_id)
                        ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                            return $q->where('bandeira_cartao', $bandeira_cartao);
                        })
                        ->where('tipo_pagamento', $ft->tipo_pagamento)->first();

                        if ($taxa != null) {
                            $item = [
                                'cliente' => $v->cliente ? ($v->cliente->razao_social . " " . $v->cliente->cpf_cnpj) :
                                'Consumidor final',
                                'total' => $ft->valor,
                                'taxa_perc' => $taxa->taxa,
                                'taxa' => $taxa ? ($ft->valor * ($taxa->taxa / 100)) : 0,
                                'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                                'tipo_pagamento' => Nfe::getTipo($ft->tipo_pagamento),
                                'venda_id' => $v->id,
                                'tipo' => 'PDV'
                            ];
                            array_push($data, $item);
                        }
                    }
                }
            } else {
                if (in_array($v->tipo_pagamento, $tipos)) {
                    $taxa = TaxaPagamento::where('empresa_id', request()->empresa_id)
                    ->when($bandeira_cartao != '' && $bandeira_cartao != '99', function ($q) use ($bandeira_cartao) {
                        return $q->where('bandeira_cartao', $bandeira_cartao);
                    })
                    ->where('tipo_pagamento', $v->tipo_pagamento)->first();

                    if ($taxa != null) {
                        $item = [
                            'cliente' => $v->cliente ? ($v->cliente->razao_social . " " . $v->cliente->cpf_cnpj) :
                            'Consumidor final',
                            'total' => $v->total,
                            'taxa_perc' => $taxa->taxa,
                            'taxa' => $taxa ? ($v->total * ($taxa->taxa / 100)) : 0,
                            'data' => \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i'),
                            'tipo_pagamento' => Nfe::getTipo($v->tipo_pagamento),
                            'venda_id' => $v->id,
                            'tipo' => 'PDV'
                        ];
                        array_push($data, $item);
                    }
                }
            }
        }

        $p = view('relatorios/taxas')
        ->with('data', $data)
        ->with('title', 'Taxas de Pagamento');

        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Taxas de pagamento.pdf", array("Attachment" => false));
    }
}
