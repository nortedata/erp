<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cidade;

class HelperController extends Controller
{
    public function cidadePorNome($nome){
        $cidade = Cidade::
        where('nome', $nome)
        ->first();

        return response()->json($cidade, 200);
    }

    public function cidadePorCodigoIbge($codigo){
        $cidade = Cidade::
        where('codigo', $codigo)
        ->first();

        return response()->json($cidade, 200);
    }

    public function cidadePorId($id){
        $cidade = Cidade::findOrFail($id);

        return response()->json($cidade, 200);
    }

    public function buscaCidades(Request $request){
        $data = Cidade::
        where('nome', 'like', "%$request->pesquisa%")
        ->get();

        return response()->json($data, 200);
        
    }
}
