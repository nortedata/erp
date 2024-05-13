<?php

namespace App\Http\Controllers;

use App\Models\NaturezaOperacao;
use Illuminate\Http\Request;

class NaturezaOperacaoController extends Controller
{
    public function index(Request $request)
    {
        $data = NaturezaOperacao::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->descricao), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('descricao', 'LIKE', "%$request->descricao%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('natureza_operacao.index', compact('data'));
    }

    public function create()
    {
        return view('natureza_operacao.create');
    }

    public function edit($id)
    {
        $item = NaturezaOperacao::findOrFail($id);
        return view('natureza_operacao.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            NaturezaOperacao::create($request->all());
            session()->flash("flash_success", "Natureza criada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->route('natureza-operacao.index');
    }

    public function update(Request $request, $id)
    {
        $item = NaturezaOperacao::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Natureza alterada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->route('natureza-operacao.index');
    }

    public function destroy($id)
    {
        $item = NaturezaOperacao::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Apagado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->route('natureza-operacao.index');
    }
}
