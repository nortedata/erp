<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empresa;
use App\Models\UsuarioEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioSuperController extends Controller
{
    public function index(Request $request)
    {
        $data = User::when(!empty($request->name), function ($q) use ($request) {
            return $q->where('name', 'LIKE', "%$request->name%");
        })
        ->paginate(env("PAGINACAO"));
        return view('usuarios_super.index', compact('data'));
    }

    public function edit($id)
    {
        $item = User::findOrFail($id);

        $empresas = Empresa::all();
        return view('usuarios_super.edit', compact('item', 'empresas'));
    }

    public function destroy($id){
        $item = User::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('usuario-super.index');
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        try {
            
            if ($request->password) {
                $request->merge([
                    'password' => Hash::make($request->password),
                ]);
            }

            if($request->empresa){
                UsuarioEmpresa::create([
                    'empresa_id' => $request->empresa,
                    'usuario_id' => $id
                ]);
            }
            $usuario->fill($request->all())->save();
            session()->flash("flash_success", "Usuário alterado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('usuario-super.index');
    }
}
