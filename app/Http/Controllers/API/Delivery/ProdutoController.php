<?php
namespace App\Http\Controllers\API\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoriaProduto;
use App\Models\Produto;
use App\Models\Adicional;
use App\Models\ProdutoAdicional;
use App\Models\DestaqueMarketPlace;

class ProdutoController extends Controller
{
    public function all(){
        $data = CategoriaProduto::
        where('empresa_id', request()->empresa_id)
        ->where('delivery', 1)
        ->with('produtosDelivery')
        ->get();

        return response()->json($data, 200);
    }

    public function adicionais(Request $request){

        $data = Adicional::
        where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->get();

        return response()->json($data, 200);
    }

    public function find($id){
        $item = Produto::
        with(['categoria', 'pizzaValores'])
        ->findOrFail($id);

        $item->adicionais_ativos = ProdutoAdicional::
        where('produto_id', $item->id)
        ->where('adicionals.status', 1)
        ->join('adicionals', 'adicionals.id', '=', 'produto_adicionals.adicional_id')
        ->with('adicional')->get();

        return response()->json($item, 200);
    }

    public function carrossel(){
        $data = DestaqueMarketPlace::
        where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->orderBy('status', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($data, 200);
    }

}
