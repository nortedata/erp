<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empresa;
use App\Models\ConfigGeral;

use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request){
        $user = User::where('email', $request->email)
        ->first();

        $validCredentials = Hash::check($request->senha, $user->getAuthPassword());
        if(!$validCredentials){
            return response()->json("Credenciais incorretas", 404);
        }

        $user->empresa_id = $user->empresa->empresa_id;
        $emp = Empresa::findOrFail($user->empresa->empresa_id);
        $user->empresa_nome = $emp->nome;
        return response()->json($user, 200);
    }

    public function dadosEmpresa(Request $request){
        $empresa = Empresa::select('nome', 'rua', 'numero', 'cpf_cnpj', 'bairro', 'celular', 'cidade_id', 'cep', 'status')
        ->with('cidade')
        ->findOrFail($request->empresa_id);

        $config = ConfigGeral::where('empresa_id', $request->empresa_id)->first();
        if($config != null){
            $empresa->definir_vendedor_pdv_off = $config->definir_vendedor_pdv_off;
            $empresa->acessos_pdv_off = json_decode($config->acessos_pdv_off);
        }else{
            $empresa->definir_vendedor_pdv_off = 0;
        }
        return response()->json($empresa, 200);
    }

    public function empresaAtiva(Request $request){
        $empresa_id = $request->empresa_id;
        $empresa = Empresa::findOrFail($empresa_id);
        return response()->json($empresa->status, 200);
    }
}
