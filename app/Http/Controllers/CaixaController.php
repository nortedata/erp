<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Nfce;
use App\Models\Nfe;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = Caixa::where('usuario_id', Auth::user()->id)->where('status', 1)->first();
        if ($item == null) {
            session()->flash('flash_warning', 'Não há caixa aberto no momento!');
            return redirect()->back();
        }
        $valor_abertura = $item->valor_abertura;
        $vendas = [];
        $somaTiposPagamento = [];
        $contas = [];
        $nfce = Nfce::where('empresa_id', request()->empresa_id)->where('caixa_id', $item->id)
        ->get();
        $nfe = Nfe::where('empresa_id',  request()->empresa_id)->where('caixa_id', $item->id)->where('tpNF', 1)
        ->get();

        $pagar = ContaPagar::where('empresa_id', request()->empresa_id)->where('caixa_id', $item->id)->get();
        $receber = ContaReceber::where('empresa_id', request()->empresa_id)->where('caixa_id', $item->id)->get();

        $vendas = $this->agrupaVendas($nfce, $nfe, $pagar, $receber);
        $somaTiposPagamento = $this->somaTiposPagamento($vendas);
        $contas = $this->agrupaContas($pagar, $receber);
        $somaTiposContas = $this->somaTiposContas($contas);

        $suprimentos = [];
        $sangrias = [];
        if ($item != null) {
            $suprimentos = SuprimentoCaixa::where('caixa_id', $item->id)->get();
            $sangrias = SangriaCaixa::where('caixa_id', $item->id)->get();
        }
        if ($item != null) {
            return view('caixa.index', compact(
                'item',
                'vendas',
                'somaTiposPagamento',
                'valor_abertura',
                'suprimentos',
                'sangrias',
                'contas',
                'somaTiposContas',
                'receber',
                'pagar'
            ));
        } else {
            session()->flash('flash_warning', 'Não há caixa aberto no momento!');
            return redirect()->back();
        }
    }

    private function agrupaVendas($nfce, $nfe)
    {
        $temp = [];
        foreach ($nfe as $v) {
            $v->tipo = 'Nfe';
            array_push($temp, $v);
        }
        foreach ($nfce as $v) {
            $v->tipo = 'Nfce';
            array_push($temp, $v);
        }
        // foreach ($pagar as $v) {
        //     $v->tipo = 'Conta Paga';
        //     array_push($temp, $v);
        // }
        // foreach ($receber as $v) {
        //     $v->tipo = 'Conta Recebida';
        //     array_push($temp, $v);
        // }
        return $temp;
    }

    private function agrupaContas($pagar, $receber)
    {
        $temp = [];
        foreach ($pagar as $c) {
            $c->tipo = 'Conta Paga';
            array_push($temp, $c);
        }
        foreach ($receber as $c) {
            $c->tipo = 'Conta Recebida';
            array_push($temp, $c);
        }
        return $temp;
    }


    private function somaTiposPagamento($vendas)
    {
        $tipos = $this->preparaTipos();

        foreach ($vendas as $v) {
            if ($v->estado != 'cancelado') {
                if (sizeof($v->fatura) > 0) {
                    if ($v->fatura) {
                        foreach ($v->fatura as $f) {
                            $tipos[trim($f->tipo_pagamento)] += $f->valor;
                        }
                    }
                }
            }
        }
        return $tipos;
    }

    private function somaTiposContas($contas)
    {
        $tipos = $this->preparaTipos();

        foreach ($contas as $c) {
            if ($c->status == 1) {
                $tipos[trim($c->tipo_pagamento)] += $c->valor_integral;
            }
        }
        return $tipos;
    }


    private function preparaTipos()
    {
        $temp = [];
        foreach (Nfce::tiposPagamento() as $key => $tp) {
            $temp[$key] = 0;
        }
        return $temp;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $item = Caixa::where('usuario_id', Auth::user()->id)->where('status', 1)->first();

        return view('caixa.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->merge([
                'usuario_id' => Auth::user()->id,
                'valor_abertura' => __convert_value_bd($request->valor_abertura),
                'observacao' => $request->observacao ?? '',
                'status' => 1,
                'valor_fechamento' => 0,
            ]);
            Caixa::create($request->all());
            session()->flash('flash_success', 'Caixa aberto com sucesso!');
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash('flash_error', 'Não foi possível abrir o caixa' . $e->getMessage());
        }
        return redirect()->route('caixa.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Caixa::FindOrFail($id);
        $vendas = [];
        $somaTiposPagamento = [];

        $nfce = Nfce::where('empresa_id', request()->empresa_id)->where('caixa_id', $item->id)
        ->get();
        $nfe = Nfe::where('empresa_id',  request()->empresa_id)->where('caixa_id', $item->id)
        ->get();
        $vendas = $this->agrupaVendas($nfce, $nfe);
        $somaTiposPagamento = $this->somaTiposPagamento($vendas);
        $suprimentos = [];
        $sangrias = [];
        $contas = [];

        $pagar = ContaPagar::where('empresa_id', request()->empresa_id)->where('caixa_id', $item->id)->get();
        $receber = ContaReceber::where('empresa_id', request()->empresa_id)->where('caixa_id', $item->id)->get();

        $contas = $this->agrupaContas($pagar, $receber);
        $somaTiposContas = $this->somaTiposContas($contas);
        if ($item != null) {
            $suprimentos = SuprimentoCaixa::where('caixa_id', $item->id)->get();
            $sangrias = SangriaCaixa::where('caixa_id', $item->id)->get();
        }
        return view('caixa.show', compact(
            'item',
            'vendas',
            'somaTiposPagamento',
            'suprimentos',
            'sangrias',
            'contas',
            'receber',
            'pagar'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function list()
    {

        $data = Caixa::where('empresa_id', request()->empresa_id)
        ->orderBy('id', 'desc')->get();

        return view('caixa.list', compact('data'));
    }

    public function fechar(Request $request)
    {
        $item = Caixa::findOrFail($request->caixa_id);
        try {
            $item->status = 0;
            $item->valor_fechamento = __convert_value_bd($request->valor_fechamento);
            $item->valor_dinheiro = $request->valor_dinheiro ? __convert_value_bd($request->valor_dinheiro) : 0;
            $item->valor_cheque = $request->valor_cheque ? __convert_value_bd($request->valor_cheque) : 0;
            $item->valor_outros = $request->valor_outros ? __convert_value_bd($request->valor_outros) : 0;
            $item->data_fechamento = date('Y-m-d h:i:s');
            $item->save();

            session()->flash('flash_success', 'Caixa Fechado');
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
            session()->flash('flash_error', 'Não foi possível fechar');
        }
        return redirect()->route('caixa.list');
    }


    public function imprimir($id)
    {
        $item = Caixa::findOrFail($id);
        $config = Empresa::where('id', request()->empresa_id)->first();
        $nfce = Nfce::where('empresa_id', request()->empresa_id)->where('caixa_id', $item->id)
        ->get();
        $nfe = Nfe::where('empresa_id',  request()->empresa_id)->where('caixa_id', $item->id)
        ->get();

        $vendas = $this->agrupaVendas($nfce, $nfe);
        $somaTiposPagamento = $this->somaTiposPagamento($vendas);

        $usuario = User::findOrFail(Auth::user()->id);

        $sangrias = SangriaCaixa::where('caixa_id', $item->id)->get();

        $suprimentos = SuprimentoCaixa::where('caixa_id', $item->id)->get();

        $p = view('caixa.imprimir', compact(
            'item',
            'vendas',
            'usuario',
            'somaTiposPagamento',
            'config',
            'sangrias',
            'suprimentos'
        ));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("fechamento caixa.pdf", array("Attachment" => false));
    }
}
