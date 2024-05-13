<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanoEmpresa;
use App\Models\Empresa;
use App\Models\Plano;

class GerenciarPlanoController extends Controller
{
    public function index(Request $request)
    {
        $empresa = $request->empresa;
        $empresas = Empresa::orderBy('nome', 'asc')->get();
        $planos = Plano::orderBy('nome', 'asc')->get();
        $data = PlanoEmpresa::orderBy('id', 'desc')
        ->when(!empty($empresa), function ($query) use ($empresa) {
            return $query->where('empresa_id', $empresa);
        })
        ->paginate(env("PAGINACAO"));
        return view('gerencia_planos.index', compact('data', 'empresas', 'planos'));
    }

    public function store(Request $request)
    {
        try {
            $plano = Plano::findOrfail($request->plano_id);
            $intervalo = $plano->intervalo_dias;
            $exp = date('Y-m-d', strtotime(date('Y-m-d') . "+ $intervalo days"));

            PlanoEmpresa::create([
                'empresa_id' => $request->empresa,
                'plano_id' => $request->plano_id,
                'data_expiracao' => $exp,
                'valor' => __convert_value_bd($request->valor),
                'forma_pagamento' => $request->forma_pagamento
            ]);
            session()->flash("flash_success", "Plano atribuído!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function destroy($id)
    {
        $item = PlanoEmpresa::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_success", "Apagado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->back();
    }
}
