<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MetaResultado;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\OrdemServico;

class MetaController extends Controller
{

    public function vendasFuncionario(Request $request){
        $item = MetaResultado::findOrFail($request->meta_id);

        $totalMeta = $item->valor;
        $somaVendasMes = $this->somaVendasMes($item);
        return view('metas_resultado.vendas_funcionario', compact('item', 'totalMeta', 'somaVendasMes'));
    }

    private function somaVendasMes($item){
        $soma = Nfe::where('empresa_id', $item->empresa_id)
        ->where('estado', '!=', 'cancelado')
        ->where('funcionario_id', $item->funcionario_id)
        ->whereMonth('created_at', date('m'))
        ->where('orcamento', 0)
        ->sum('total');

        $soma += Nfce::where('empresa_id', $item->empresa_id)
        ->where('estado', '!=', 'cancelado')
        ->where('funcionario_id', $item->funcionario_id)
        ->whereMonth('created_at', date('m'))
        ->sum('total');

        return $soma;
    }

    public function vendasFuncionarioGrafico(Request $request){
        $item = MetaResultado::findOrFail($request->meta_id);

        $values = [];
        $labels = [];

        $meses = [
            'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
            'Out', 'Nov', 'Dez'
        ];

        for($i= 1; $i<=(int)date('d'); $i++){

            $dia = $i < 10 ? "0$i" : $i;
            $soma = Nfe::where('empresa_id', $item->empresa_id)
            ->where('estado', '!=', 'cancelado')
            ->where('funcionario_id', $item->funcionario_id)
            ->whereDate('created_at', date('Y-m')."/$dia")
            ->sum('total');

            $soma += Nfce::where('empresa_id', $item->empresa_id)
            ->where('estado', '!=', 'cancelado')
            ->where('funcionario_id', $item->funcionario_id)
            ->whereDate('created_at', date('Y-m')."/$dia")
            ->sum('total');

            $labels[] = $dia."/".$meses[(int)date('m')-1];
            $values[] = $soma;
        }

        $data = [
            'labels' => $labels,
            'values' => $values,
        ];

        return response()->json($data, 200);
    }

    public function ordemServicoFuncionario(Request $request){
        $item = MetaResultado::findOrFail($request->meta_id);

        $totalMeta = $item->valor;
        $somaVendasMes = $this->somaOsMes($item);
        return view('metas_resultado.vendas_funcionario', compact('item', 'totalMeta', 'somaVendasMes'));
    }

    private function somaOsMes($item){
        $soma = OrdemServico::where('empresa_id', $item->empresa_id)
        ->where('funcionario_id', $item->funcionario_id)
        ->whereMonth('created_at', date('m'))
        ->sum('valor');

        return $soma;
    }

    public function ordemServicoFuncionarioGrafico(Request $request){
        $item = MetaResultado::findOrFail($request->meta_id);

        $values = [];
        $labels = [];

        $meses = [
            'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
            'Out', 'Nov', 'Dez'
        ];

        for($i= 1; $i<=(int)date('d'); $i++){

            $dia = $i < 10 ? "0$i" : $i;
            

            $soma = OrdemServico::where('empresa_id', $item->empresa_id)
            ->where('funcionario_id', $item->funcionario_id)
            ->whereDate('created_at', date('Y-m')."/$dia")
            ->sum('valor');

            $labels[] = $dia."/".$meses[(int)date('m')-1];
            $values[] = $soma;
        }

        $data = [
            'labels' => $labels,
            'values' => $values,
        ];

        return response()->json($data, 200);
    }
}
