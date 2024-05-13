<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarrinhoDelivery;
use App\Models\ItemCarrinhoDelivery;
use App\Models\MarketPlaceConfig;
use App\Models\Produto;
use App\Models\ItemPizzaCarrinho;
use App\Models\Cliente;
use App\Models\ProdutoVariacao;
use App\Models\CategoriaProduto;
use App\Models\FuncionamentoDelivery;
use App\Models\ItemCarrinhoAdicionalDelivery;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CarrinhoController extends Controller
{
    public function __construct(){
        session_start();
    }

    private function _getCarrinho(){
        $data = [];
        if(isset($_SESSION["session_cart_delivery"])){
            $data = CarrinhoDelivery::where('session_cart_delivery', $_SESSION["session_cart_delivery"])
            ->first();
        }
        return $data;
    }

    public function index(Request $request){
        $carrinho = $this->_getCarrinho();
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        if(isset($_SESSION["session_cart_delivery"])){
            $item = CarrinhoDelivery::where('session_cart_delivery', $_SESSION["session_cart_delivery"])
            ->first();
        }

        $clienteLogado = $this->_getClienteLogado();

        $categorias = CategoriaProduto::where('delivery', 1)
        ->where('empresa_id', $config->empresa_id)->get();

        $notSearch = true;

        $funcionamento = $this->getFuncionamento($config);

        return view('food.carrinho', compact('config', 'categorias', 'carrinho', 'notSearch', 'clienteLogado', 
            'funcionamento'));
    }

    private function getFuncionamento($config){
        $dia = date('w');
        $hora = date('H:i');
        $dia = FuncionamentoDelivery::getDia($dia);

        $funcionamento = FuncionamentoDelivery::where('dia', $dia)
        ->where('empresa_id', $config->empresa_id)->first();

        if($funcionamento != null){

            $atual = strtotime(date('Y-m-d H:i'));
            $dataHoje = date('Y-m-d');
            $inicio = strtotime($dataHoje . " " . $funcionamento->inicio);
            $fim = strtotime($dataHoje . " " . $funcionamento->fim);
            if($atual > $inicio && $atual < $fim){
                $funcionamento->aberto = true;
            }else{
                $funcionamento->aberto = false;
            }
            return $funcionamento;
        }
        return null;
    }

    public function adicionar(Request $request){
        // dd($request->all());
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        try{
            $carrinho = DB::transaction(function () use ($request, $config) {

                if(!isset($_SESSION["session_cart_delivery"])){
                    $session_cart_delivery = Str::random(30);
                    $_SESSION['session_cart_delivery'] = $session_cart_delivery;
                }else{
                    $session_cart_delivery = $_SESSION['session_cart_delivery'];
                }

                if($request->produto_id){
                    $produto_id = $request->produto_id;
                }else{
                    $produto_id = $request->pizza_id[0];
                }

                $carrinho = CarrinhoDelivery::where('session_cart_delivery', $session_cart_delivery)
                ->first();

                $quantidade = __convert_value_bd($request->quantidade);

                $itemCarrinho = null;

                if($carrinho == null){
                    //novo carrinho
                    $clienteLogado = $this->_getClienteLogado();

                    $cli = Cliente::where('uid', $clienteLogado)->first();

                    $carrinho = CarrinhoDelivery::create([
                        'cliente_id' => $cli ? $cli->id : null,
                        'empresa_id' => $config->empresa_id,
                        'estado' => 'pendente',
                        'valor_total' => $request->valor_item,
                        'endereco_id' => null,
                        'valor_frete' => 0,
                        'session_cart_delivery' => $session_cart_delivery
                    ]);
                    $itemCarrinho = ItemCarrinhoDelivery::create([
                        'carrinho_id' => $carrinho->id,
                        'produto_id' => $produto_id,
                        'quantidade' => $quantidade,
                        'valor_unitario' => $request->valor_item/$quantidade,
                        'sub_total' => $request->valor_item,
                        'observacao' => $request->observacao ?? '',
                        'tamanho_id' => isset($request->tamanho_id) ? $request->tamanho_id : null
                    ]);
                    session()->flash("flash_success", "Produto adicionado ao carrinho!");
                }else{

                    $itemCarrinho = ItemCarrinhoDelivery::create([
                        'carrinho_id' => $carrinho->id,
                        'produto_id' => $produto_id,
                        'quantidade' => $quantidade,
                        'valor_unitario' => $request->valor_item/$quantidade,
                        'sub_total' => $request->valor_item,
                        'observacao' => $request->observacao ?? '',
                        'tamanho_id' => isset($request->tamanho_id) ? $request->tamanho_id : null
                    ]);

                    session()->flash("flash_success", "Produto adicionado ao carrinho!");
                }

                if($request->adicional){
                    for($i=0; $i<sizeof($request->adicional); $i++){
                        ItemCarrinhoAdicionalDelivery::create([
                            'item_carrinho_id' => $itemCarrinho->id, 
                            'adicional_id' => $request->adicional[$i]
                        ]);
                    }
                }

                if(isset($request->pizza_id)){

                    for($i=0; $i<sizeof($request->pizza_id); $i++){

                        ItemPizzaCarrinho::create([
                            'item_carrinho_id' => $itemCarrinho->id, 
                            'produto_id' => $request->pizza_id[$i]
                        ]);
                    }
                }
                return $carrinho;
            });
        }catch(\Exception $e){
            echo $e->getMessage();
            die;
        }
        $this->_atualizaValorCarrinho($carrinho->id);
        
        return redirect()->route('food.carrinho', 'link='.$config->loja_id);
    }

    private function _atualizaValorCarrinho($carrinho_id){
        $item = CarrinhoDelivery::findOrfail($carrinho_id);
        $item->valor_total = $item->itens->sum('sub_total') + $item->valor_frete;

        $item->save();
    }

    private function _getClienteLogado(){
        if(isset($_SESSION['cliente_delivery'])){
            return $_SESSION['cliente_delivery'];
        }
        return null;
    }

    public function updateQuantidades(Request $request){
        $session_cart_delivery = $_SESSION['session_cart_delivery'];

        $itemCarrinho = ItemCarrinhoDelivery::findOrFail($request->item_id);
        if($request->quantidade == 0){
            $itemCarrinho->adicionais()->delete();
            $itemCarrinho->delete();
        }else{
            $itemCarrinho->quantidade = $request->quantidade;
            $itemCarrinho->sub_total = $itemCarrinho->valor_unitario * $request->quantidade;
            $itemCarrinho->save();
        }

        $carrinho = CarrinhoDelivery::where('session_cart_delivery', $session_cart_delivery)
        ->first();
        $this->_atualizaValorCarrinho($carrinho->id);
        session()->flash("flash_success", "Carrinho atualizado!");
        return redirect()->back();

    }

    public function removeItem($id){
        $session_cart_delivery = $_SESSION['session_cart_delivery'];

        $itemCarrinho = ItemCarrinhoDelivery::findOrFail($id);
        $itemCarrinho->adicionais()->delete();
        $itemCarrinho->delete();

        $carrinho = CarrinhoDelivery::where('session_cart_delivery', $session_cart_delivery)
        ->first();
        $this->_atualizaValorCarrinho($carrinho->id);
        session()->flash("flash_success", "Carrinho atualizado!");
        return redirect()->back();
    }

}
