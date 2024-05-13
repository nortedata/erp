<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CategoriaProduto;
use App\Models\Empresa;
use App\Models\Nfce;
use App\Models\Produto;
use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use NFePHP\DA\NFe\CupomNaoFiscal;
use App\Utils\EstoqueUtil;


class FrontBoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
    }

    public function index()
    {
        $data = Nfce::where('empresa_id', request()->empresa_id)
        ->orderBy('id', 'desc')->get();        
        return view('front_box.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();

        $abertura = Caixa::where('usuario_id', get_id_user())
        ->where('status', 1)
        ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if($config == null){
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if($config->natureza_id_pdv == null){
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        return view('front_box.create', compact('categorias', 'abertura', 'funcionarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Nfce::findOrFail($id);

        return view('front_box.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Nfce::findOrFail($id);
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();

        $abertura = Caixa::where('empresa_id', request()->empresa_id)->where('usuario_id', get_id_user())
        ->where('status', 1)
        ->first();

        $config = Empresa::findOrFail(request()->empresa_id);
        if($config == null){
            session()->flash("flash_warning", "Configure antes de continuar!");
            return redirect()->route('config.index');
        }

        if($config->natureza_id_pdv == null){
            session()->flash("flash_warning", "Configure a natureza de operação padrão para continuar!");
            return redirect()->route('config.index');
        }

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();

        return view('front_box.edit', compact('categorias', 'abertura', 'funcionarios', 'item'));
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
        $item = Nfce::findOrFail($id);
        try {
            foreach ($item->itens as $i) {
                if ($i->produto->gerenciar_estoque) {
                    $this->util->incrementaEstoque($i->produto_id, $i->quantidade);
                }
            }
            $item->itens()->delete();
            $item->fatura()->delete();
            $item->contaReceber()->delete();
            $item->delete();
            session()->flash("flash_success", "Venda removida!");
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->route('frontbox.index');
    }

    public function imprimirNaoFiscal($id)
    {
        $item = Nfce::findOrFail($id);
        $config = Empresa::where('id', $item->empresa_id)
        ->first();
        // if ($config->logo) {
        //     $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
        // } else {
        //     $logo = null;
        // }
        $usuario = UsuarioEmpresa::find(get_id_user());
        $cupom = new CupomNaoFiscal($item, $config);

        // if ($usuario->config) {
        //     $cupom->setPaperWidth($usuario->config->impressora_modelo);
        // }
        $pdf = $cupom->render();
        return response($pdf)
        ->header('Content-Type', 'application/pdf');
    }
}
