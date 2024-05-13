<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Nfce;
use App\Models\Nfe;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SangriaController extends Controller
{

    public function store(Request $request)
    {
        try {

            if(!$request->valor || __convert_value_bd($request->valor) == 0){
                session()->flash("flash_error", "Informe um valor maior que zero");
                return redirect()->back();
            }
            if (__convert_value_bd($request->valor) <= $this->somaTotalEmCaixa()) {
                SangriaCaixa::create([
                    'caixa_id' => $request->caixa_id,
                    'valor' => __convert_value_bd($request->valor),
                    'observacao' => $request->observacao ?? '',
                ]);
                session()->flash("flash_success", "Sangria realizada com sucesso!");
            } else {
                session()->flash("flash_warning", "Valor da Sangria ultrapassa o valor disponível!");
                redirect()->back();
            }
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    private function somaTotalEmCaixa()
    {
        $usuario_id = Auth::user()->id;
        $abertura = Caixa::where('empresa_id', request()->empresa_id)->where('status', 1)->where('usuario_id', $usuario_id)
        ->orderBy('id', 'desc')
        ->first();
        // dd($abertura->valor_abertura);
        if ($abertura == null) return 0;
        $soma = 0;
        $soma += $abertura->valor_abertura;
        $nfce = Nfce::selectRaw('sum(total) as valor')->where('empresa_id', request()->empresa_id)->where('caixa_id', $abertura->id)
        ->first();
        if ($nfce != null)
            $soma += $nfce->valor;
        $nfe = Nfe::selectRaw('sum(total) as valor')->where('empresa_id', request()->empresa_id)->where('caixa_id', $abertura->id)
        ->first();
        if ($nfe != null)
            $soma += $nfe->valor;
        $amanha = date('Y-m-d', strtotime('+1 days')) . " 00:00:00";
        $suprimentosSoma = SuprimentoCaixa::selectRaw('sum(valor) as valor')->whereBetween('created_at', [$abertura->created_at, $amanha])
        ->where('caixa_id', $abertura->id)
        ->first();
        if ($suprimentosSoma != null)
            $soma += $suprimentosSoma->valor;
        $sangriasSoma = SangriaCaixa::selectRaw('sum(valor) as valor')->whereBetween('created_at', [$abertura->created_at, $amanha])
        ->where('caixa_id', $abertura->id)
        ->first();
        if ($sangriasSoma != null)
            $soma -= $sangriasSoma->valor;
        return $soma;
    }
}
