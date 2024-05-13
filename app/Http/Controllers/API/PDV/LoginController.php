<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

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

        $user->empresa = $user->empresa;
        return response()->json($user, 200);
    }
}
