<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\UsuarioEmpresa;
use App\Models\ContadorEmpresa;

class EmpresaController extends Controller
{
    public function pesquisa(Request $request){

        $empresasComContador = ContadorEmpresa::pluck('empresa_id')->all();
        $data = Empresa::orderBy('nome', 'desc')
        ->select('nome', 'id', 'cpf_cnpj')
        ->when($request->pesquisa, function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->pesquisa%");
        })
        ->where('tipo_contador', 0)
        ->whereNotIn('id', $empresasComContador)
        ->get();

        return response()->json($data, 200);
    }

    public function findAll(Request $request){


        $data = Empresa::orderBy('nome', 'desc')
        ->select('nome', 'id', 'cpf_cnpj')
        ->when($request->pesquisa, function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->pesquisa%");
        })
        ->get();

        return response()->json($data, 200);
    }

    public function findUser(Request $request){
        $usuarioEmpresa = UsuarioEmpresa::where('empresa_id', $request->empresa_id)
        ->first();
        return response()->json($usuarioEmpresa->usuario, 200); 
    }
}
