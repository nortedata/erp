<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ListaPreco;

class ListaPrecoController extends Controller
{
    public function pesquisa(Request $request)
    {
        $tipo_pagamento = $request->tipo_pagamento_lista;
        $funcionario_id = $request->funcionario_lista_id;
        $data = ListaPreco::orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('nome', 'like', "%$request->pesquisa%")
        ->when($tipo_pagamento, function ($query) use ($tipo_pagamento) {
            return $query->where('tipo_pagamento', $tipo_pagamento);
        })
        ->when($funcionario_id, function ($query) use ($funcionario_id) {
            return $query->where('funcionario_id', $funcionario_id);
        })
        ->get();
        return response()->json($data, 200);
    }

    public function find(Request $request)
    {
        $item = ListaPreco::findOrFail($request->id);
        return response()->json($item, 200);
    }
}
