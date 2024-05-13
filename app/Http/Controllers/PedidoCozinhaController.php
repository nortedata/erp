<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemPedido;

class PedidoCozinhaController extends Controller
{
    public function index(){
        return view('pedido_cozinha.index');
    }

    public function updateItem(Request $request, $id){
        $item = ItemPedido::findOrfail($id);
        $item->estado = $request->estado;
        if(isset($request->tempo_preparo)){
            $item->tempo_preparo = $request->tempo_preparo;
        }
        $item->save();
        session()->flash("flash_success", "Estado do item #$id - ". $item->produto->nome ." alterado para $request->estado!");
        return redirect()->back();
    }
}
