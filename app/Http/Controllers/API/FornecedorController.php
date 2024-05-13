<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    public function find($id)
    {
        $item = Fornecedor::with('cidade')->findOrFail($id);
        return response()->json($item, 200);
    }

    public function pesquisa(Request $request)
    {
        $data = Fornecedor::orderBy('razao_social', 'desc')
            ->where('empresa_id', $request->empresa_id)
            ->where('razao_social', 'like', "%$request->pesquisa%")
            ->get();
        return response()->json($data, 200);
    }
}
