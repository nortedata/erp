<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Utils\UploadUtil;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = User::where('usuario_empresas.empresa_id', request()->empresa_id)
            ->join('usuario_empresas', 'users.id', '=', 'usuario_empresas.usuario_id')
            ->select('users.*')
            ->when(!empty($request->name), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('name', 'LIKE', "%$request->name%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('usuarios.index', compact('data'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function edit($id)
    {
        $item = User::findOrFail($id);
        return view('usuarios.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/usuarios');
            }
            $request->merge([
                'password' => Hash::make($request['password']),
                'imagem' => $file_name
            ]);
            $usuario = User::create($request->all());

            UsuarioEmpresa::create([
                'empresa_id' => $request->empresa_id,
                'usuario_id' => $usuario->id
            ]);
            session()->flash("flash_success", "Usuário cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('usuarios.index');
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        try {
            $file_name = $usuario->imagem;

            if ($request->hasFile('image')) {
                $this->util->unlinkImage($usuario, '/usuarios');
                $file_name = $this->util->uploadImage($request, '/usuarios');
            }
            if ($request->password) {
                $request->merge([
                    'password' => Hash::make($request->password),
                    'imagem' => $file_name
                ]);
            } else {
                $request->merge([
                    'password' => $usuario->password,
                    'imagem' => $file_name
                ]);
            }
            $usuario->fill($request->all())->save();
            session()->flash("flash_success", "Usuário alterado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('usuarios.index');
    }

    public function destroy($id)
    {
        $item = User::findOrFail($id);
        try {
            $item->empresa->delete();
            $item->delete();
            session()->flash("flash_success", "Apagado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('usuarios.index');
    }

    public function profile($id)
    {
        $item = User::findOrFail($id);
        return view('usuarios.profile', compact('item'));
    }

    public function show($id){
        if(!__isAdmin()){
            session()->flash("flash_error", "Acesso permitido somente para administradores");
            return redirect()->back();
        }
        $item = User::findOrFail($id);
        return view('usuarios.show', compact('item'));
    }
}
