<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketPlaceConfig;
use App\Models\CarrinhoDelivery;
use App\Models\ItemCarrinhoDelivery;
use App\Models\CategoriaProduto;
use App\Models\ItemCarrinhoAdicionalDelivery;
use App\Models\ItemPizzaCarrinho;
use App\Models\Cliente;
use App\Models\BairroDelivery;
use App\Models\EnderecoDelivery;
use App\Models\PedidoDelivery;
use App\Models\CupomDescontoCliente;
use App\Models\ItemPedidoDelivery;
use App\Models\ItemPizzaPedido;
use App\Models\CupomDesconto;
use App\Models\ItemPizzaPedidoDelivery;
use App\Models\ItemAdicionalDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PagamentoController extends Controller
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

    private function _getClienteLogado(){
        if(isset($_SESSION['cliente_delivery'])){
            return $_SESSION['cliente_delivery'];
        }
        return null;
    }

    public function index(Request $request){
        $carrinho = $this->_getCarrinho();

        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        if($carrinho == []){
            return redirect()->route('food.index', 'link='.$config->loja_id);
        }

        if($config->pedido_minimo > $carrinho->valor_total){
            session()->flash('flash_error', 'Valor mínimo para o pedido R$' . __moeda($config->pedido_minimo));
            return redirect()->back();
        }
        $clienteLogado = $this->_getClienteLogado();
        if($clienteLogado == null){
            if($carrinho->fone != null){
                $cliente = Cliente::where('empresa_id', $carrinho->empresa_id)
                ->where('telefone', $carrinho->fone)
                ->first();

                if($cliente != null){
                    $_SESSION['cliente_delivery'] = $cliente->uid;
                }else{
                    return redirect()->route('food.auth', 'link='.$config->loja_id);
                }
            }else{
                return redirect()->route('food.auth', 'link='.$config->loja_id);
            }
        }

        $cliente = Cliente::where('uid', $this->_getClienteLogado())
        ->first();
        if($cliente == null){
            return redirect()->route('food.index', 'link='.$config->loja_id);
        }

        $categorias = CategoriaProduto::where('delivery', 1)
        ->where('empresa_id', $config->empresa_id)->get();
        $notSearch = true;

        $config->tipo_entrega = json_decode($config->tipo_entrega);
        if(sizeof($config->tipo_entrega) == 0){
            session()->flash('flash_error', 'Nenhum tipo de entrega definido!');
            return redirect()->back();
        }
        $tiposPagamento = [];
        $tipos_pagamento = $config->tipos_pagamento ? json_decode($config->tipos_pagamento) : [];

        foreach($tipos_pagamento as $tp){
            array_push($tiposPagamento, $tp);
        }
        $config->tipos_pagamento = $tiposPagamento;

        if(sizeof($config->tipos_pagamento) == 0){
            session()->flash('flash_error', 'Nenhum tipo de pagamento definido!');
            return redirect()->back();
        }

        $bairros = BairroDelivery::where('empresa_id', $config->empresa_id)
        ->where('status', 1)->get();

        if($carrinho->endereco_id == null){
            $carrinho->endereco_id = $cliente->enderecoPrincipal ? $cliente->enderecoPrincipal->id : null;
            if($cliente->enderecoPrincipal){
                $carrinho->valor_frete = $cliente->enderecoPrincipal->bairro->valor_entrega;
            }

            $carrinho->valor_total = $carrinho->itens->sum('sub_total') + $carrinho->valor_frete - $carrinho->valor_desconto;
            $carrinho->save();
        }

        $entregaGratis = false;
        if($carrinho->valor_total >= $config->valor_entrega_gratis){
            $carrinho->valor_frete = 0;
            $carrinho->valor_total = $carrinho->itens->sum('sub_total') + $carrinho->valor_frete - $carrinho->valor_desconto;
            $carrinho->save();
            $entregaGratis = true;

        }

        return view('food.pagamento', compact('carrinho', 'config', 'categorias', 'notSearch', 'bairros', 
            'cliente', 'entregaGratis'));
    }

    public function finalizar(Request $request){
        // dd($request->all());
        $carrinho = $this->_getCarrinho();
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        echo $config->confirmacao_pedido_cliente;

        try{
            $pedido = DB::transaction(function () use ($request, $config, $carrinho) {
                $cliente = Cliente::where('uid', $this->_getClienteLogado())
                ->first();

                $cupom = CupomDesconto::where('codigo', $carrinho->cupom)->first();
                $troco = __convert_value_bd($request->troco_para);
                $pedido = PedidoDelivery::create([
                    'empresa_id' => $config->empresa_id,
                    'cliente_id' => $cliente->id,
                    'valor_total' => $carrinho->valor_total,
                    'troco_para' => $troco != $carrinho->valor_total ? $troco : null,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'observacao' => $request->observacao ?? '',
                    'telefone' => $cliente->telefone,
                    'estado' => 'novo',
                    'endereco_id' => $carrinho->endereco_id,
                    'cupom_id' => $cupom ? $cupom->id : null,
                    'desconto' => $carrinho->valor_desconto,
                    'valor_entrega' => $carrinho->valor_frete,
                    'horario_cricao' => date('H:i')
                ]);
                if($cupom){
                    CupomDescontoCliente::create([
                        'cliente_id' => $cliente->id,
                        'empresa_id' => $pedido->empresa_id,
                        'cupom_id' => $cupom->id,
                        'pedido_id' => $pedido->id
                    ]);
                }
                foreach($carrinho->itens as $i){
                    $itemPedido = ItemPedidoDelivery::create([
                        'pedido_id' => $pedido->id,
                        'produto_id' => $i->produto_id,
                        'quantidade' => $i->quantidade,
                        'observacao' => $i->observacao,
                        'tamanho_id' => $i->tamanho_id,
                        'valor_unitario' => $i->valor_unitario, 
                        'sub_total' => $i->sub_total
                    ]);
                    foreach($i->adicionais as $a){
                        ItemAdicionalDelivery::create([
                            'item_pedido_id' => $itemPedido->id,
                            'adicional_id' => $a->adicional_id
                        ]);
                    }

                    foreach($i->pizzas as $pizza){
                        ItemPizzaPedidoDelivery::create([
                            'item_pedido_id' => $itemPedido->id,
                            'produto_id' => $pizza->produto_id
                        ]);
                    }

                }
            });
            $_SESSION["session_cart_delivery"] = null;
            if($config->confirmacao_pedido_cliente == 1){
                session()->flash("flash_warning", "Pedido realizado aguardando confirmação");
                return redirect()->route('food.aguardando-confirmar', 'link='.$config->loja_id);
            }else{
                session()->flash("flash_success", "Pedido realizado com sucesso");
                return redirect()->route('food.index', 'link='.$config->loja_id);
            }

        }catch(\Exception $e){
            // echo $e->getMessage();
            // die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }

    public function pedirNovamente($id){
        $pedido = PedidoDelivery::findOrfail($id);
        $config = MarketPlaceConfig::findOrfail(request()->loja_id);

        try{
            $pedido = DB::transaction(function () use ($pedido, $config) {
                $session_cart_delivery = Str::random(30);
                $_SESSION['session_cart_delivery'] = $session_cart_delivery;
                $carrinho = CarrinhoDelivery::create([
                    'cliente_id' => $pedido->cliente_id,
                    'empresa_id' => $pedido->empresa_id,
                    'estado' => 'pendente',
                    'valor_total' => $pedido->valor_total,
                    'endereco_id' => null,
                    'valor_frete' => 0,
                    'session_cart_delivery' => $session_cart_delivery
                ]);

                foreach($pedido->itens as $i){
                    $itemCarrinho = ItemCarrinhoDelivery::create([
                        'carrinho_id' => $carrinho->id,
                        'produto_id' => $i->produto_id,
                        'quantidade' => $i->quantidade,
                        'valor_unitario' => $i->valor_unitario,
                        'sub_total' => $i->sub_total,
                        'observacao' => $i->observacao,
                        'tamanho_id' => $i->tamanho_id
                    ]);

                    foreach($i->adicionais as $a){
                        ItemCarrinhoAdicionalDelivery::create([
                            'item_carrinho_id' => $itemCarrinho->id, 
                            'adicional_id' => $a->adicional_id
                        ]);
                    }

                    foreach($i->pizzas as $a){
                        ItemPizzaCarrinho::create([
                            'item_carrinho_id' => $itemCarrinho->id, 
                            'produto_id' => $a->produto_id
                        ]);
                    }
                }
            });
            session()->flash("flash_success", "Itens adicionados as carrinho!");
            return redirect()->route('food.carrinho', 'link='.$config->loja_id);
        }catch(\Exception $e){
            echo $e->getMessage();
            die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }

    public function pagamentoPix(Request $request){
        $carrinho = $this->_getCarrinho();
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        try{
            $pedido = DB::transaction(function () use ($request, $config, $carrinho) {
                $cliente = Cliente::where('uid', $this->_getClienteLogado())
                ->first();

                $cupom = CupomDesconto::where('codigo', $carrinho->cupom)->first();
                $troco = __convert_value_bd($request->troco_para);
                $pedido = PedidoDelivery::create([
                    'empresa_id' => $config->empresa_id,
                    'cliente_id' => $cliente->id,
                    'valor_total' => $carrinho->valor_total,
                    'troco_para' => $troco != $carrinho->valor_total ? $troco : null,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'observacao' => $request->observacao ?? '',
                    'telefone' => $cliente->telefone,
                    'estado' => 'novo',
                    'endereco_id' => $carrinho->endereco_id,
                    'cupom_id' => $cupom ? $cupom->id : null,
                    'desconto' => $carrinho->valor_desconto,
                    'valor_entrega' => $carrinho->valor_frete,
                    'horario_cricao' => date('H:i')
                ]);
                if($cupom){
                    CupomDescontoCliente::create([
                        'cliente_id' => $cliente->id,
                        'empresa_id' => $pedido->empresa_id,
                        'cupom_id' => $cupom->id,
                        'pedido_id' => $pedido->id
                    ]);
                }
                foreach($carrinho->itens as $i){
                    $itemPedido = ItemPedidoDelivery::create([
                        'pedido_id' => $pedido->id,
                        'produto_id' => $i->produto_id,
                        'quantidade' => $i->quantidade,
                        'observacao' => $i->observacao,
                        'tamanho_id' => $i->tamanho_id,
                        'valor_unitario' => $i->valor_unitario, 
                        'sub_total' => $i->sub_total
                    ]);
                    foreach($i->adicionais as $a){
                        ItemAdicionalDelivery::create([
                            'item_pedido_id' => $itemPedido->id,
                            'adicional_id' => $a->adicional_id
                        ]);
                    }

                    foreach($i->pizzas as $pizza){
                        ItemPizzaPedidoDelivery::create([
                            'item_pedido_id' => $itemPedido->id,
                            'produto_id' => $pizza->produto_id
                        ]);
                    }

                }
                return $pedido;
            });
            $pedido = $this->createQrCode($pedido, $request->cpf);
            $_SESSION["session_cart_delivery"] = null;
            if(isset($pedido['erro'])){
                session()->flash("flash_error", $pedido['erro']);
                return redirect()->back();
            }
            session()->flash("flash_success", "Pedido realizado com sucesso");
            return redirect()->route('food.qr_code', [$pedido->transacao_id, 'link='.$config->loja_id]);

        }catch(\Exception $e){
            echo $e->getMessage();
            die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }

    private function createQrCode($pedido, $cpf){
        $config = MarketPlaceConfig::where('empresa_id', $pedido->empresa_id)->first();
        $cliente = $pedido->cliente;

        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        \MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

        $payment = new \MercadoPago\Payment();

        $payment->transaction_amount = (float) number_format($pedido->valor_total,2);
        $payment->description = "pagamento do pedido #" . $pedido->id;
        $payment->payment_method_id = "pix";

        $cep = str_replace("-", "", $config->cep);
        $payment->payer = array(
            "email" => $config->email,
            // "first_name" => $cliente->nome,
            // "last_name" => $cliente->sobre_nome,
            "identification" => array(
                "type" => 'CPF',
                "number" => $cpf
            ),
            "address"=>  array(
                "zip_code" => $cep,
                "street_name" => $config->rua,
                "street_number" => $config->numero,
                "neighborhood" => $config->bairro,
                "city" => $config->cidade->nome,
                "federal_unit" => $config->cidade->uf
            )
        );

        $payment->save();
        if($payment->transaction_details){

            $pedido->transacao_id = $payment->id;
            $pedido->status_pagamento = $payment->status;
            $pedido->qr_code_base64 = $payment->point_of_interaction->transaction_data->qr_code_base64;
            $pedido->qr_code = $payment->point_of_interaction->transaction_data->qr_code;

            $pedido->save();
            return $pedido;

        }else{
            return [
                'erro' => $payment->error
            ];
        }
    }

    public function qrCode($transacao_id){
        $item = PedidoDelivery::where('transacao_id', $transacao_id)->first();
        $config = MarketPlaceConfig::where('empresa_id', $item->empresa_id)->first();
        $carrinho = $this->_getCarrinho();
        $categorias = CategoriaProduto::where('delivery', 1)
        ->where('empresa_id', $config->empresa_id)->get();
        return view('food.qr_code', compact('item', 'config', 'carrinho', 'categorias'));
    }

    public function pagamentoCartao(Request $request){
        $carrinho = $this->_getCarrinho();
        $config = MarketPlaceConfig::findOrfail($request->loja_id);
        try{
            $pedido = DB::transaction(function () use ($request, $config, $carrinho) {
                $cliente = Cliente::where('uid', $this->_getClienteLogado())
                ->first();

                $cupom = CupomDesconto::where('codigo', $carrinho->cupom)->first();
                $troco = __convert_value_bd($request->troco_para);
                $pedido = PedidoDelivery::create([
                    'empresa_id' => $config->empresa_id,
                    'cliente_id' => $cliente->id,
                    'valor_total' => $carrinho->valor_total,
                    'troco_para' => $troco != $carrinho->valor_total ? $troco : null,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'observacao' => $request->observacao ?? '',
                    'telefone' => $cliente->telefone,
                    'estado' => 'novo',
                    'endereco_id' => $carrinho->endereco_id,
                    'cupom_id' => $cupom ? $cupom->id : null,
                    'desconto' => $carrinho->valor_desconto,
                    'valor_entrega' => $carrinho->valor_frete,
                    'horario_cricao' => date('H:i')
                ]);
                if($cupom){
                    CupomDescontoCliente::create([
                        'cliente_id' => $cliente->id,
                        'empresa_id' => $pedido->empresa_id,
                        'cupom_id' => $cupom->id,
                        'pedido_id' => $pedido->id
                    ]);
                }
                foreach($carrinho->itens as $i){
                    $itemPedido = ItemPedidoDelivery::create([
                        'pedido_id' => $pedido->id,
                        'produto_id' => $i->produto_id,
                        'quantidade' => $i->quantidade,
                        'observacao' => $i->observacao,
                        'tamanho_id' => $i->tamanho_id,
                        'valor_unitario' => $i->valor_unitario, 
                        'sub_total' => $i->sub_total
                    ]);
                    foreach($i->adicionais as $a){
                        ItemAdicionalDelivery::create([
                            'item_pedido_id' => $itemPedido->id,
                            'adicional_id' => $a->adicional_id
                        ]);
                    }

                    foreach($i->pizzas as $pizza){
                        ItemPizzaPedidoDelivery::create([
                            'item_pedido_id' => $itemPedido->id,
                            'produto_id' => $pizza->produto_id
                        ]);
                    }

                }

                return $pedido;
            });
            $pagamento = $this->pagarComCartao($pedido, $request);

            if(isset($pagamento['erro'])){
                session()->flash("flash_error", $pedido['erro']);
                return redirect()->back();
            }

            $pedido->estado = 'aprovado';
            $pedido->save();

            $_SESSION["session_cart_delivery"] = null;
            
            session()->flash("flash_success", "Pagamento realizado com sucesso");
            return redirect()->route('food.index', 'link='.$config->loja_id);

        }catch(\Exception $e){
            // echo $e->getMessage();
            // die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }

    private function pagarComCartao($pedido, $request){
        $config = MarketPlaceConfig::where('empresa_id', $pedido->empresa_id)->first();
        $cliente = $pedido->cliente;

        \MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

        $payment = new \MercadoPago\Payment();
        $payment->transaction_amount = (float)$request->transactionAmount;
        $payment->token = $request->token;
        $payment->description = $request->description;
        $payment->installments = (int)$request->installments;
        $payment->payment_method_id = $request->paymentMethodId;

        $payer = new \MercadoPago\Payer();
        $payer->email = $request->email;
        $payer->identification = array(
            "type" => $request->docType,
            "number" => $request->docNumber
        );
        $payment->payer = $payer;

        if($payment->error){
            session()->flash("flash_error", $payment->error);
            return [
                'erro' => $payment->error
            ];
        }else{
            $pedido->status_pagamento = $payment->status;
            $pedido->transacao_id = (string)$payment->id;
            $pedido->save();

            return [
                'sucesso' => 1,
                'transacao_id' => $pedido->transacao_id
            ];
        }

        $payment->save();
    }

    public function aguardandoConfirmar(){
        $cliente = Cliente::where('uid', $this->_getClienteLogado())
        ->first();

        $config = MarketPlaceConfig::where('empresa_id', $cliente->empresa_id)->first();

        if($cliente == null){
            return redirect()->route('food.index', 'link='.$config->loja_id);
        }

        $pedido = PedidoDelivery::where('cliente_id', $cliente->id)
        ->where('estado', 'novo')
        ->orderBy('id', 'desc')->first();

        if($pedido == null){
            return redirect()->route('food.index', 'link='.$config->loja_id);
        }

        if($pedido->estado != 'novo'){
            return redirect()->route('food.index', 'link='.$config->loja_id);
        }

        $carrinho = $this->_getCarrinho();
        $categorias = CategoriaProduto::where('delivery', 1)
        ->where('empresa_id', $config->empresa_id)->get();
        $notSearch = true;
        return view('food.aguardando_confirmar', compact('pedido', 'config', 'carrinho', 'categorias', 'notSearch'));
    }

}
