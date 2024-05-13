<?php

namespace App\Http\Controllers;

use App\Models\TaxaPagamento;
use Illuminate\Http\Request;

class TaxaCartaoController extends Controller
{
    public function index()
    {
        $data = TaxaPagamento::where('empresa_id', request()->empresa_id)
            ->paginate(env("PAGINACAO"));

        return view('taxa_cartao.index', compact('data'));
    }

    public function create()
    {
        return view('taxa_cartao.create');
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'taxa' => __convert_value_bd($request->taxa),
                'bandeira_cartao' => $request->bandeira_cartao ?? null
            ]);
            TaxaPagamento::create($request->all());
            session()->flash("flash_success", "Taxa cadastrada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->route('taxa-cartao.index');
    }

    public function edit($id)
    {
        $item = TaxaPagamento::findOrFail($id);

        return view('taxa_cartao.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = TaxaPagamento::findOrFail($id);
        try {
            $request->merge([
                'taxa' => __convert_value_bd($request->taxa)
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Taxa atualizada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->route('taxa-cartao.index');
    }

    public function destroy($id)
    {
        $item = TaxaPagamento::findOrFail($id);
        try{
            $item->delete();
            session()->flash("flash_success", "Deletado com sucesso!");
        }catch(\Exception $e){
            session()->flash("flash_error", "Algo deu errado" . $e->getMessage());
        }
        return redirect()->route('taxa-cartao.index');
    }
}
