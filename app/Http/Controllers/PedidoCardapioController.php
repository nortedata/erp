<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\ItemPedido;
use App\Models\Empresa;
use App\Models\Produto;
use App\Models\ItemAdicional;
use App\Models\ItemPizzaPedido;
use App\Models\Adicional;
use App\Models\CategoriaProduto;
use App\Models\ConfiguracaoCardapio;
use App\Models\TamanhoPizza;
use App\Models\Caixa;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;

class PedidoCardapioController extends Controller
{
    public function index(Request $request){
        $data = Pedido::
        where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->orderBy('created_at', 'desc')
        ->get();

        return view('pedidos.index', compact('data'));
    }

    public function store(Request $request){
        $cliente_id = $request->cliente_id;
        $comanda = $request->comanda;
        $clienteNome = $request->cliente_nome;
        $clienteFone = $request->cliente_fone;
        $item = Pedido::where('status', 1)
        ->where('empresa_id', $request->empresa_id)
        ->where('comanda', $comanda)
        ->first();

        if($item != null){
            session()->flash("flash_error", 'Comanda já está aberta');
            return redirect()->back();
        }

        try{
            $data = [
                'cliente_id' => $cliente_id,
                'cliente_nome' => $clienteNome,
                'cliente_fone' => $clienteFone,
                'comanda' => $comanda,
                'total' => 0,
                'empresa_id' => $request->empresa_id
            ];

            Pedido::create($data);
            session()->flash("flash_success", "Comanda aberta!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function show($id){
        $item = Pedido::findOrfail($id);
        $tamanhosPizza = TamanhoPizza::where('empresa_id', request()->empresa_id)
        ->get();

        $config = ConfiguracaoCardapio::where('empresa_id', request()->empresa_id)
        ->first();
        return view('pedidos.show', compact('item', 'tamanhosPizza', 'config'));
    }

    public function storeItem(Request $request, $id){
        try {
            DB::transaction(function () use ($request, $id) {

                $adicionais = $request->adicionais;
                $adicionais = explode(",", $adicionais);

                $pedido = Pedido::findOrfail($id);

                $data = [
                    'pedido_id' => $id,
                    'produto_id' => $request->produto_cardapio,
                    'observacao' => $request->observacao,
                    'quantidade' => __convert_value_bd($request->quantidade),
                    'valor_unitario' => __convert_value_bd($request->valor_unitario),
                    'sub_total' => __convert_value_bd($request->sub_total),
                    'estado' => $request->estado,
                    'ponto_carne' => $request->ponto_carne,
                    'tamanho_id' => $request->tamanho_id

                ];
                $itemPedido = ItemPedido::create($data);

                $produto = Produto::findOrFail($request->produto_cardapio);
                if($produto != null){
                    if($produto->categoria && $produto->categoria->tipo_pizza){
                        $pizzas = explode(",", $request->pizzas);
                        foreach($pizzas as $pz){
                            ItemPizzaPedido::create([
                                'item_pedido_id' => $itemPedido->id,
                                'produto_id' => $pz
                            ]);
                        }
                    }
                }
                foreach($adicionais as $a){
                    if($a){
                        $adicional = Adicional::findOrFail($a);
                        $dataItemAdicional = [
                            'item_pedido_id' => $itemPedido->id,
                            'adicional_id' => $adicional->id,
                        ];
                        ItemAdicional::create($dataItemAdicional);

                    }
                }

                $pedido->sumTotal();

            });
            session()->flash("flash_success", "Produto adicionado!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }

        return redirect()->back();

    }

    public function destroyItem($id){
        $item = ItemPedido::findOrFail($id);
        try {
            $pedido = $item->pedido;
            $item->adicionais()->delete();
            $item->delete();
            $pedido->sumTotal();
            
            session()->flash("flash_success", "Item removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado '. $e->getMessage());
        }
        return redirect()->back();
    }

    public function print($id){
        $item = Pedido::findOrFail($id);

        $height = 180;

        $height += $item->countItens()*25;
        $config = Empresa::where('id', $item->empresa_id)->first();

        $p = view('pedidos.imprimir', compact('config', 'item'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper([0,0,204,$height]);
        $domPdf->render();
        $domPdf->stream("Pedido $id.pdf", array("Attachment" => false));
    }

    public function finish($id){
        $pedido = Pedido::findOrFail($id);

        if($pedido->status == 0){
            session()->flash("flash_warning", 'Pedido já esta finalizado');
            return redirect()->back();
        }

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

        $itens = $pedido->itens;

        // dd($itens->pluck('valor_unitario'));
        return view('front_box.create', compact('categorias', 'abertura', 'funcionarios', 'pedido', 'itens'));
    }
}
