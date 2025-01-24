<?php

namespace App\Http\Controllers\API\Comanda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\ConfiguracaoCardapio;

class ConfigController extends Controller
{
    public function setConfig(Request $request){
        $item = ConfiguracaoCardapio::where('api_token', $request->token)
        ->first();

        if($item == null){
            return response()->json("Configuração não encontrada!", 401);
        }

        $funcionario = Funcionario::where('codigo', $request->codigo_garcom)
        ->where('empresa_id', $item->empresa_id)
        ->first();

        if($funcionario == null){
            return response()->json("Funcionário não encontrado " . $request->codigo_garcom, 401);
        }

        return response()->json($item, 200);
    }
}
