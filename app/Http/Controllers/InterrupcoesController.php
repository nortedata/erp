<?php

namespace App\Http\Controllers;

use App\Models\DiaSemana;
use App\Models\Interrupcoes;
use App\Models\Funcionario;
use App\Models\MotivoInterrupcao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterrupcoesController extends Controller
{
    public function index(Request $request)
    {
        $data = Interrupcoes::where('empresa_id', $request->empresa_id)
        ->paginate(getenv("PAGINACAO"));

        return view('interrupcoes.index', compact('data'));
    }

    public function create()
    {
        // $interrupcoes = Interrupcoes::where('empresa_id', request()->empresa_id)
        // ->select('funcionarios.*')
        // ->join('funcionarios', 'funcionarios.id', '=', 'funcionamentos.funcionario_id')
        // ->pluck('funcionarios.id')
        // ->all();

        // $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        // ->whereNotIn('id', $interrupcoes)->get();

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        ->orderBy('nome', 'asc')
        ->get();

        $dias = DiaSemana::where('empresa_id', request()->empresa_id)
        ->pluck('funcionario_id')->all();

        $motivos = MotivoInterrupcao::where('empresa_id', request()->empresa_id)
        ->orderBy('motivo', 'asc')
        ->get();
        return view('interrupcoes.create', compact('funcionarios', 'dias', 'motivos'));
    }

    public function register($id)
    {
        $item = DiaSemana::findOrFail($id);
        return view('interrupcoes.register', compact('item'));
    }

    public function store(Request $request)
    {

        try {
            Interrupcoes::create([
                'funcionario_id' => $request->funcionario_id,
                'inicio' => $request->inicio,
                'fim' => $request->fim,
                'dia_id' => $request->dia,
                'motivo' => $request->motivo,
                'empresa_id' => $request->empresa_id
            ]);
            session()->flash('flash_success', 'Horário de intervalo atribuído com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_error', 'Algo deu errado:' . $e->getMessage());
        }
        return redirect()->route('interrupcoes.index');
    }

    public function destroy($id)
    {
        $item = Interrupcoes::findOrFail($id);
        try{
            $item->delete();
            session()->flash('flash_success', 'Deletado com sucesso!');
        }catch(\Exception $e){
            session()->flash('flash_error', 'Algo deu errado:' . $e->getMessage());
        }
        return redirect()->route('interrupcoes.index');
    }
}
