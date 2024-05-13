<?php

namespace App\Http\Controllers;

use App\Models\EventoSalario;
use Illuminate\Http\Request;

class EventoFuncionarioController extends Controller
{
    public function index(Request $request)
    {
        $data = EventoSalario::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('nome', 'LIKE', "%$request->nome%");
            });
        })
        ->paginate(env("PAGINACAO"));

        return view('eventos.index', compact('data'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(Request $request)
    {
        try {
            EventoSalario::create($request->all());
            session()->flash("flash_success", "Evento cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('evento-funcionarios.index');
    }

    public function edit($id)
    {
        $item = EventoSalario::findOrFail($id);
        return view('eventos.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = EventoSalario::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash("flash_success", "Evento alterado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('evento-funcionarios.index');
    }

    public function destroy($id)
    {
        $item = EventoSalario::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Evento Deletado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('evento-funcionarios.index');
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = EventoSalario::findOrFail($request->item_delete[$i]);
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