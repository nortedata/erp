<?php

namespace App\Http\Controllers;

use App\Models\CategoriaServico;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;

class CategoriaServicoController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index()
    {
        $data = CategoriaServico::where('empresa_id', request()->empresa_id)
        ->paginate(env("PAGINACAO"));

        return view('categoria_servico.index', compact('data'));
    }

    public function create()
    {
        return view('categoria_servico.create');
    }

    public function edit($id)
    {
        $item = CategoriaServico::findOrFail($id);
        return view('categoria_servico.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/categoriaServico');
            }
            $request->merge([
                'imagem' => $file_name
            ]);
            CategoriaServico::create($request->all());
            session()->flash('flash_success', 'Cadastrado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Não foi possível concluir o cadastro' . $e->getMessage());
        }
        return redirect()->route('categoria-servico.index');
    }

    public function update(Request $request, $id)
    {
        $item = CategoriaServico::findOrFail($id);
        try {
            $file_name = $item->imagem;

            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/categoriaServico');
                $file_name = $this->util->uploadImage($request, '/categoriaServico');
            }
            $request->merge([
                'imagem' => $file_name
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_success', 'Alterado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Não foi possível alterar o cadastro' . $e->getMessage());
        }
        return redirect()->route('categoria-servico.index');
    }

    public function destroy($id)
    {
        $item = CategoriaServico::findOrFail($id);
        try {
            $item->delete();
            session()->flash('flash_success', 'Deletado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Não foi possível deletar' . $e->getMessage());
        }
        return redirect()->route('categoria-servico.index');
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = CategoriaServico::findOrFail($request->item_delete[$i]);
            try {
                $item->delete();
                $removidos++;
            } catch (\Exception $e) {
                session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                return redirect()->back();
            }
        }

        session()->flash("flash_success", "Total de itens removidos: $removidos!");
        return redirect()->back();
    }
}
