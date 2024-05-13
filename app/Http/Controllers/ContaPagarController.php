<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\ContaPagar;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContaPagarController extends Controller
{
    public function index(Request $request)
    {
        $fornecedor_id = $request->fornecedor_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $data = ContaPagar::where('empresa_id', request()->empresa_id)
        ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
            return $query->where('fornecedor_id', $fornecedor_id);
        })
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data_vencimento', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('data_vencimento', '<=', $end_date);
        })
        ->paginate(env("PAGINACAO"));
        return view('conta-pagar.index', compact('data'));
    }

    public function create()
    {
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();
        return view('conta-pagar.create', compact('fornecedores'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'valor_pago' => $request->valor_pago ? __convert_value_bd($request->valor_pago) : 0,
            ]);
            ContaPagar::create($request->all());
            if ($request->dt_recorrencia) {
                for ($i = 0; $i < sizeof($request->dt_recorrencia); $i++) {
                    $data = $request->dt_recorrencia[$i];
                    $valor = __convert_value_bd($request->valor_recorrencia[$i]);
                    $data = [
                        'venda_id' => null,
                        'data_vencimento' => $data,
                        'data_pagamento' => $data,
                        'valor_integral' => $valor,
                        'valor_pago' => $request->status ? $valor : 0,
                        'referencia' => $request->referencia,
                        'categoria_id' => $request->categoria_id,
                        'status' => $request->status,
                        'empresa_id' => $request->empresa_id,
                        'fornecedor_id' => $request->fornecedor_id,
                        'tipo_pagamento' => $request->tipo_pagamento,
                    ];
                    ContaPagar::create($data);
                }
            }
            session()->flash("flash_success", "Conta a pagar cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }

    public function edit($id)
    {
        $item = ContaPagar::findOrFail($id);
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();

        return view('conta-pagar.edit', compact('item', 'fornecedores'));
    }

    public function update(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        try {
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'valor_pago' => __convert_value_bd($request->valor_pago) ? __convert_value_bd($request->valor_pago) : 0,
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Conta a pagar atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'fornecedor_id' => 'required',
            'valor_integral' => 'required',
            'data_vencimento' => 'required',
            'status' => 'required',
            'tipo_pagamento' => 'required'
        ];
        $messages = [
            'fornecedor_id.required' => 'Campo obrigatório',
            'valor_integral.required' => 'Campo obrigatório',
            'data_vencimento.required' => 'Campo obrigatório',
            'status.required' => 'Campo obrigatório',
            'tipo_pagamento.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ContaPagar::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Conta removida!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = ContaPagar::findOrFail($request->item_delete[$i]);
            try {
                $item->delete();
                $removidos++;
            } catch (\Exception $e) {
                session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                return redirect()->back();
            }
        }

        session()->flash("flash_success", "Total de itens removidos: $removidos!");
        return redirect()->back();
    }

    public function pay($id)
    {
        $item = ContaPagar::findOrFail($id);
        return view('conta-pagar.pay', compact('item'));
    }

    public function payPut(Request $request, $id)
    {
        $usuario = Auth::user()->id;
        $caixa = Caixa::where('usuario_id', $usuario)->where('status', 1)->first();
        $item = ContaPagar::findOrFail($id);
        try {
            $item->valor_pago = __convert_value_bd($request->valor_pago);
            $item->status = true;
            $item->data_pagamento = $request->data_pagamento;
            $item->tipo_pagamento = $request->tipo_pagamento;
            $item->caixa_id = $caixa->id;
            $item->save();
            session()->flash("flash_success", "Conta paga!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-pagar.index');
    }
}
