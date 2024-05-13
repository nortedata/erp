<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidoDelivery;
use App\Models\TamanhoPizza;
use App\Models\Empresa;
use App\Models\MarketPlaceConfig;
use App\Models\ItemAdicionalDelivery;
use App\Models\ItemPedidoDelivery;
use App\Models\ItemPizzaPedidoDelivery;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Motoboy;
use App\Models\BairroDelivery;
use App\Models\EnderecoDelivery;
use App\Models\CategoriaProduto;
use App\Models\Caixa;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use App\Utils\WhatsAppUtil;

class PedidoDeliveryController extends Controller
{
    protected $util;

    public function __construct(WhatsAppUtil $util){
        $this->util = $util;
    }

    public function index(Request $request){
        $estado = $request->estado;
        $cliente_delivery_id = $request->cliente_delivery_id;
        $cliente = null;

        $data = PedidoDelivery::
        where('empresa_id', $request->empresa_id)
        ->orderBy('created_at', 'desc')
        ->when(!empty($cliente_delivery_id), function ($query) use ($cliente_delivery_id) {
            return $query->where('cliente_id', $cliente_delivery_id);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->paginate(env("PAGINACAO"));

        if($cliente_delivery_id){
            $cliente = Cliente::findOrFail($cliente_delivery_id);
        }

        return view('pedido_delivery.index', compact('data', 'cliente'));
    }

    public function alteraStatus(Request $request){
        $item = PedidoDelivery::findOrFail($request->pedido_id);

        $item->pedido_lido = 1;
        $item->estado = $request->estado;
        $item->horario_leitura = date('H:i');
        $item->save();

        $config = MarketPlaceConfig::where('empresa_id', request()->empresa_id)
        ->first();
        if($request->estado == 'cancelado'){
            session()->flash("flash_error", "Pedido cancelado!");
            $mensagem = "Olá, aqui é da $config->nome seu pedido foi cancelado :(";
        }else{
            session()->flash("flash_success", "Pedido aprovado!");
            $mensagem = "Olá, aqui é da $config->nome seu pedido foi aprovado :)";

        }

        $this->sendMessageWhatsApp($item, $mensagem);
        return redirect()->route('pedidos-delivery.show', [$item->id]);
    }

    private function sendMessageWhatsApp($pedido, $texto){
        $telefone = "55".preg_replace('/[^0-9]/', '', $pedido->cliente->telefone);
        $retorno = $this->util->sendMessage($telefone, $texto, $pedido->empresa_id);
        // dd($retorno);
    }

    public function update(Request $request, $id){
        $item = PedidoDelivery::findOrFail($id);
        try{
            $config = MarketPlaceConfig::where('empresa_id', request()->empresa_id)
            ->first();
            $item->estado = $request->estado;
            if($item->pedido_lido == 0){
                $mensagem = "Olá, aqui é da $config->nome seu pedido foi aprovado :)";
                $this->sendMessageWhatsApp($item, $mensagem);
            }
            $item->pedido_lido = 1;
            $item->save();
            session()->flash("flash_success", "Estado alterado!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function storeItem(Request $request, $id){
        try {
            DB::transaction(function () use ($request, $id) {

                $adicionais = $request->adicionais;
                $adicionais = explode(",", $adicionais);

                $pedido = PedidoDelivery::findOrfail($id);

                $data = [
                    'pedido_id' => $id,
                    'produto_id' => $request->produto_delivery,
                    'observacao' => $request->observacao,
                    'quantidade' => __convert_value_bd($request->quantidade),
                    'valor_unitario' => __convert_value_bd($request->valor_unitario),
                    'sub_total' => __convert_value_bd($request->sub_total),
                    'estado' => $request->estado,
                    'tamanho_id' => $request->tamanho_id

                ];
                $itemPedido = ItemPedidoDelivery::create($data);

                $produto = Produto::findOrFail($request->produto_delivery);
                if($produto != null){
                    if($produto->categoria && $produto->categoria->tipo_pizza){
                        $pizzas = explode(",", $request->pizzas);
                        foreach($pizzas as $pz){
                            ItemPizzaPedidoDelivery::create([
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
                        ItemAdicionalDelivery::create($dataItemAdicional);

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

    public function show($id){
        $item = PedidoDelivery::findOrFail($id);

        $tamanhosPizza = TamanhoPizza::where('empresa_id', request()->empresa_id)
        ->get();
        $config = MarketPlaceConfig::where('empresa_id', request()->empresa_id)
        ->first();
        $cliente = $item->cliente;

        $bairros = BairroDelivery::where('empresa_id', request()->empresa_id)
        ->get();

        $motoboys = Motoboy::where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->get();

        return view('pedido_delivery.show', 
            compact('item', 'tamanhosPizza', 'config', 'cliente', 'bairros', 'motoboys'));
    }

    public function print($id){
        $item = PedidoDelivery::findOrFail($id);

        $height = 200;

        $height += $item->countItens()*25;
        $config = Empresa::where('id', $item->empresa_id)->first();

        $p = view('pedido_delivery.imprimir', compact('config', 'item'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper([0,0,204,$height]);
        $domPdf->render();

        $domPdf->stream("Pedido $id.pdf", array("Attachment" => false));
    }

    public function setEndereco(Request $request, $id){
        $item = PedidoDelivery::findOrFail($id);
        try{
            $item->endereco_id = $request->endereco_id;
            if($item->endereco){
                $item->valor_entrega = $item->endereco->bairro->valor_entrega;
            }else{
                $item->valor_entrega = 0;
            }

            $item->save();
            $item->sumTotal();

            session()->flash("flash_success", "Endereço alterado!");

        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function storeEndereco(Request $request, $id){
        $item = PedidoDelivery::findOrFail($id);
        $config = MarketPlaceConfig::where('empresa_id', request()->empresa_id)
        ->first();
        try{

            $data = [
                'rua' => $request->rua,
                'numero'=> $request->numero,
                'bairro_id'=> $request->bairro_id,
                'referencia'=> $request->referencia ?? '',
                'tipo' => $request->tipo,
                'latitude' => '',
                'longitude' => '',
                'cliente_id' => $item->cliente_id,
                'cidade_id' => $config->cidade_id,
                'padrao' => sizeof($item->cliente->enderecos) == 0 ? 1 : 0
            ];
            $endereco = EnderecoDelivery::create($data);
            $item->endereco_id = $endereco->id;

            if($endereco->bairro){
                $item->valor_entrega = $endereco->bairro->valor_entrega;
            }else{
                $item->valor_entrega = 0;
            }
            $item->save();
            session()->flash("flash_success", "Endereço cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado ' . $e->getMessage());
        }
        return redirect()->back();

    }

    public function updateItem(Request $request, $id){
        $item = ItemPedidoDelivery::findOrfail($id);
        $item->estado = $request->estado;
        if(isset($request->tempo_preparo)){
            $item->tempo_preparo = $request->tempo_preparo;
        }
        $item->save();
        session()->flash("flash_success", "Estado do item #$id - ". $item->produto->nome ." alterado para $request->estado!");
        return redirect()->back();
    }

    public function finish(Request $request, $id){
        $pedido = PedidoDelivery::findOrfail($id);
        $motoboy_id = $request->motoboy_id;
        $valor_comissao = __convert_value_bd($request->valor_comissao);

        $pedido->motoboy_id = $motoboy_id;
        $pedido->comissao_motoboy = $valor_comissao;
        $pedido->save();

        if($pedido->status == 1){
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
        $isDelivery = 1;

        return view('front_box.create', 
            compact('categorias', 'abertura', 'funcionarios', 'pedido', 'itens', 'isDelivery'));

    }

}
