<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Nfce;
use App\Models\Nfe;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;
use Svg\Tag\Rect;

class SuprimentoController extends Controller
{
    public function store(Request $request)
    {
        try {
            if(!$request->valor || __convert_value_bd($request->valor) == 0){
                session()->flash("flash_error", "Informe um valor maior que zero");
                return redirect()->back();
            }
            SuprimentoCaixa::create([
                'caixa_id' => $request->caixa_id,
                'valor' => __convert_value_bd($request->valor),
                'observacao' => $request->observacao ?? '',
            ]);
            session()->flash("flash_success", "Suprimento realizado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }
}
