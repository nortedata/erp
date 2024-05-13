<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DiaSemana;
use App\Models\Funcionamento;
use App\Models\Funcionario;
use App\Models\Interrupcoes;
use App\Models\Agendamento;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Http\Request;

class AgendamentoController extends Controller
{
    public function buscarHorarios(Request $request)
    {
        $servicos = json_decode($request->servicos);
        $data = $request->data;
        $empresa_id = $request->empresa_id;
        $funcionario_id = $request->funcionario_id;

        $diaSemanaNumerico = date('w', strtotime($data));

        $diaStr = DiaSemana::getDia($diaSemanaNumerico);
        $totalServico = 0;
        $tempoServico = 0;

        foreach($servicos as $s){
            $item = Servico::findOrFail($s);
            $tempoServico += (float)$item->tempo_servico;
            $totalServico += (float)$item->valor;
        }

        $funcionarios = Funcionario::where('funcionarios.empresa_id', $empresa_id)
        ->select('funcionarios.*')
        ->join('dia_semanas', 'dia_semanas.funcionario_id', '=', 'funcionarios.id')
        ->where(function($q) use ($diaStr) {
            return $q->orWhere('dia', 'LIKE',  '%'.$diaStr.'%');
        })
        ->when(!empty($funcionario_id), function ($query) use ($funcionario_id) {
            return $query->where('funcionarios.id', $funcionario_id);
        })
        ->get();

        $horarios = [];

        foreach($funcionarios as $f){
            $funcionamento = Funcionamento
            ::where('funcionario_id', $f->id)
            ->where('dia_id', $diaStr)
            ->first(); // funcionamento do dia

            $inicio = $funcionamento->inicio;
            $fim = $funcionamento->fim;

            $dif = strtotime("$data $fim") - strtotime("$data $inicio");

            $minutosDif = $dif/(60); //converte milesegundos em minutos
            $contador = $minutosDif/$tempoServico;

            $interrupcoes = Interrupcoes::where('funcionario_id', $f->id)
            ->where('dia_id', $diaStr)->get();

            $inicio = strtotime("$data $inicio");
            for($i=0; $i<$contador; $i++){
                $fim = strtotime("+".$tempoServico." minutes", $inicio);

                $temp = [
                    'funcionario_id' => $f->id,
                    'funcionario_nome' => $f->nome,
                    'inicio' => date('H:i', $inicio),
                    'fim' => date('H:i', $fim),
                    'data' => $data,
                    'total' => $totalServico,
                    'tempoServico' => $tempoServico
                ];

                $add = true;

                $interrupcao = Interrupcoes::where('funcionario_id', $f->id)
                ->where('dia_id', $diaStr)
                ->whereTime('inicio', '<=', date('H:i', $inicio))
                ->whereTime('fim', '>=', date('H:i', $inicio))
                ->first();

                if($interrupcao != null){

                    $add = false;
                }else{

                    $agendamento = Agendamento::where('funcionario_id', $f->id)
                    ->whereDate('data', $data)
                    ->whereTime('inicio', '<=', date('H:i', $inicio))
                    ->whereTime('termino', '>=', date('H:i', $inicio))
                    ->first();

                    if($agendamento != null){
                        $add = false;
                    }
                }

                if($add == true){
                    array_push($horarios, $temp);
                }

                $inicio = $fim;
            }
        }

        return view('agendamento.partials.agenda_row', compact('horarios'));

    }

}
