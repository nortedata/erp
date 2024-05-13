<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CategoriaProduto;
use App\Models\Empresa;
use App\Models\FaturaPreVenda;
use App\Models\Funcionario;
use App\Models\ItemPreVenda;
use App\Models\NaturezaOperacao;
use App\Models\PreVenda;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use StringBackedEnum;
use Svg\Tag\Rect;
use Illuminate\Support\Str;


class PreVendaController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $status = $request->get('status');

        $item = PreVenda::first();

        $data = PreVenda::where('empresa_id', request()->empresa_id)
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
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
        ->orderBy('id', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('pre_venda.index', compact('data'));
    }


    public function create()
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        $abertura = Caixa::where('empresa_id', request()->empresa_id)->where('usuario_id', get_id_user())
        ->where('status', 1)
        ->first();

        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        }
        return view('pre_venda.create', compact('abertura', 'categorias', 'funcionarios', 'naturezas'));
    }

    public function store(Request $request)
    {
        // dd($request);
        try {
            // $valor_total = $this->somaItens($request);

            $natureza = NaturezaOperacao::where('empresa_id', request()->empresa_id)->first();
            $request->merge([
                'cliente_id' => $request->cliente_id,
                'bandeira_cartao' => $request->bandeira_cartao ?? '',
                'cnpj_cartao' => $request->cnpj_cartao ?? '',
                'cAut_cartao' => $request->cAut_cartao ?? '',
                'descricao_pag_outros' => $request->descricao_pag_outros ?? '',
                'rascunho' => $request->rascunho ?? 0,
                'usuario_id' => get_id_user(),
                'observacao' => $request->observacao ?? '',
                'qtd_volumes' => $request->qtd_volumes ?? 0,
                'peso_liquido' => $request->peso_liquido ?? 0,
                'peso_bruto' => $request->peso_bruto ?? 0,
                'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                'valor_total' => __convert_value_bd($request->valor_total),
                'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                'natureza_id' => $natureza->id,
                'forma_pagamento' => '',
                'tipo_pagamento' => $request->tipo_pagamento_row ? '99' : $request->tipo_pagamento,
                'nome' => $request->nome,
                'cpf' => $request->cpf ?? '',
                'codigo' => Str::random(8)
            ]);

            $preVenda = PreVenda::create($request->all());

            for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                $product = Produto::findOrFail($request->produto_id[$i]);
                $cfop = 0;
                ItemPreVenda::create([
                    'pre_venda_id' => $preVenda->id,
                    'produto_id' => (int)$request->produto_id[$i],
                    'quantidade' => __convert_value_bd($request->quantidade[$i]),
                    'valor' => __convert_value_bd($request->valor_unitario[$i]),
                    'cfop' => $cfop,
                    'observacao' => $request->observacao ?? '',
                ]);
            }

            if ($request->tipo_pagamento_row) {
                for ($i = 0; $i < sizeof($request->tipo_pagamento_row); $i++) {
                    FaturaPreVenda::create([
                        'valor_parcela' => __convert_value_bd($request->valor_integral_row[$i]),
                        'tipo_pagamento' => $request->tipo_pagamento_row[$i],
                        'pre_venda_id' => $preVenda->id,
                        'vencimento' => $request->data_vencimento_row[$i]
                    ]);
                }
            } else {
                FaturaPreVenda::create([
                    'valor_parcela' => __convert_value_bd($request->valor_total),
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'pre_venda_id' => $preVenda->id,
                    'vencimento' => $request->data_vencimento
                ]);
            }
            session()->flash("flash_success", "Pré-venda realizada com sucesso!");
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash("flash_error", "Algo deu errado por aqui: " . $e->getMessage());
        }
        return redirect()->back()->with(['codigo' => $preVenda->codigo]);
    }

    private function somaItens($request)
    {
        $valor_total = 0;
        for ($i = 0; $i < sizeof($request->produto_id); $i++) {
            $valor_total += __convert_value_bd($request->subtotal_item[$i]);
        }
        return $valor_total;
    }

    public function destroy($id)
    {
        $item = PreVenda::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Deletado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('pre-venda.index');
    }
}
