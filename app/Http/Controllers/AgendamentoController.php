<?php

namespace App\Http\Controllers;

use App\Models\Funcionamento;
use App\Models\Servico;
use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Models\Funcionario;
use App\Models\Agendamento;
use App\Models\ItemAgendamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgendamentoController extends Controller
{
    public function index()
    {
        // $funcionario = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $servicos = Servico::where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->get();

        $data = Agendamento::where('empresa_id', request()->empresa_id)
        ->orderBy('data', 'desc')->get();
        $agendamentos = [];

        foreach($data as $item){
            $a = [
                'title' => $item->cliente->razao_social,
                'start' => $item->data . " " . $item->inicio,
                'end' => $item->data . " " . $item->termino,
                'className' => $item->getPrioridade(),
                'id' => $item->id
            ];
            array_push($agendamentos, $a);
        }

        return view('agendamento.index', compact('agendamentos', 'servicos'));
    }

    public function store(Request $request){
        try {

            $nfe = DB::transaction(function () use ($request) {
                $dataAgendamento = [
                    'funcionario_id' => $request->funcionario,
                    'cliente_id' => $request->cliente_id,
                    'data' => $request->data,
                    'inicio' => $request->inicio,
                    'termino' => $request->termino,
                    'prioridade' => $request->prioridade,
                    'observacao' => $request->observacao ?? "",
                    'total' => __convert_value_bd($request->total),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0, 
                    'acrescimo' => 0, 
                    'empresa_id' => $request->empresa_id
                ];
            // dd($request->servicos);
                $agendamento = Agendamento::create($dataAgendamento);
            // dd($dataAgendamento);
                for($i=0; $i<sizeof($request->servicos); $i++){
                    $servico = Servico::findOrFail($request->servicos[$i]);
                    $dataItem = [
                        'agendamento_id' => $agendamento->id,
                        'servico_id' => $request->servicos[$i],
                        'quantidade' => 1,
                        'valor' => $servico->valor
                    ];
                    ItemAgendamento::create($dataItem);
                }
            });
            session()->flash("flash_success", "Agendamento cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->back();
    }

    public function show($id){
        $item = Agendamento::findOrFail($id);

        return view('agendamento.show', compact('item'));
    }

    public function update(Request $request, $id){
        $item = Agendamento::findOrFail($id);
        $item->inicio = $request->inicio;
        $item->termino = $request->termino;
        $item->data = $request->data;
        $item->save();
        session()->flash("flash_success", "Agendamento alterado!");
        return redirect()->back();

    }

    public function updateStatus(Request $request, $id){
        $item = Agendamento::findOrFail($id);
        $item->status = 1;
        $item->save();
        session()->flash("flash_success", "Agendamento alterado!");
        return redirect()->route('agendamentos.index');

    }

    public function destroy($id)
    {
        $item = Agendamento::findOrFail($id);
        try {
            $item->itens()->delete();
            $item->delete();
            session()->flash("flash_success", "Agendamento removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->route('agendamentos.index');
    }
}
