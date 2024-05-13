<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionario;

class FuncionarioController extends Controller
{
    public function pesquisa(Request $request)
    {
        $data = Funcionario::orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('nome', 'like', "%$request->pesquisa%")
        ->get();
        return response()->json($data, 200);
    }

    public function find(Request $request)
    {
        $item = Funcionario::findOrFail($request->id);
        return response()->json($item, 200);
    }
}
