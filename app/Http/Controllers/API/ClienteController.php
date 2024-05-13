<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\CashBackConfig;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function find($id)
    {
        $item = Cliente::with('cidade')->findOrFail($id);
        return response()->json($item, 200);
    }

    public function cashback($id)
    {
        $item = Cliente::with('cidade')->findOrFail($id);
        $config = CashBackConfig::where('empresa_id', $item->empresa_id)->first();
        if($config == null){
            return response()->json(null, 404);
        }
        $config->valor_cashback = $item->valor_cashback;
        return response()->json($config, 200);
    }

    public function pesquisa(Request $request)
    {
        $data = Cliente::orderBy('razao_social', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->where('razao_social', 'like', "%$request->pesquisa%")
        ->get();
        return response()->json($data, 200);
    }

    public function pesquisaDelivery(Request $request)
    {
        $data = Cliente::orderBy('razao_social', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->where('uid', '!=', '')
        ->when(!is_numeric($request->pesquisa), function ($q) use ($request) {
            return $q->where('razao_social', 'LIKE', "%$request->pesquisa%");
        })
        ->when(is_numeric($request->pesquisa), function ($q) use ($request) {
            return $q->where('telefone', 'LIKE', "%$request->pesquisa%");
        })
        ->get();
        return response()->json($data, 200);
    }
}
