<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProduto;
use App\Models\Produto;
use App\Models\Fornecedor;
use App\Models\Cliente;
use App\Models\ConfiguracaoCardapio;
use App\Models\ProdutoPizzaValor;
use App\Models\Empresa;
use App\Models\ConfigGeral;
use App\Models\ItemNfe;
use App\Models\ItemListaPreco;
use App\Models\PadraoTributacaoProduto;
use App\Models\ProdutoVariacao;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function pesquisa(Request $request)
    {
        $lista_id = $request->lista_id;
        $data = Produto::orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->when(!is_numeric($request->pesquisa), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->pesquisa%");
        })
        ->when(is_numeric($request->pesquisa), function ($q) use ($request) {
            return $q->where('codigo_barras', 'LIKE', "%$request->pesquisa%");
        })
        ->get();

        if(is_numeric($request->pesquisa)){
            $dataAppend = ProdutoVariacao::where('produtos.empresa_id', $request->empresa_id)
            ->where('produto_variacaos.codigo_barras', 'LIKE', "%$request->pesquisa%")
            ->join('produtos', 'produtos.id', '=', 'produto_variacaos.produto_id')
            ->select('produto_variacaos.*')
            ->get();

            foreach($dataAppend as $v){
                $v->valor_unitario = $v->valor;
                $v->valor_compra = $v->produto->valor_compra;
                $v->nome = $v->produto->nome . " - " . $v->descricao;
                $v->codigo_variacao = $v->id;
                $v->id = $v->produto_id;
                $data->push($v);
            }

            // $data->push($dataAppend);
        }

        if($lista_id){

            foreach($data as $i){
                $itemLista = ItemListaPreco::where('lista_id', $lista_id)
                ->where('produto_id', $i->id)
                ->first();
                if($itemLista != null){
                    $i->valor_unitario = $itemLista->valor;
                }
            }
        }

        return response()->json($data, 200);
    }

    public function pesquisaCardapio(Request $request)
    {
        $data = Produto::orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('cardapio', 1)
        ->where('nome', 'like', "%$request->pesquisa%")
        ->get();
        return response()->json($data, 200);
    }

    public function pesquisaDelivery(Request $request)
    {
        $data = Produto::orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('delivery', 1)
        ->where('nome', 'like', "%$request->pesquisa%")
        ->get();
        return response()->json($data, 200);
    }

    public function find(Request $request)
    {
        $cliente = null;
        $fornecedor = null;
        $entrada = $request->entrada;
        if (isset($request->cliente_id)) {
            $cliente = Cliente::find($request->cliente_id);
        }
        if (isset($request->fornecedor_id)) {
            $fornecedor = Fornecedor::find($request->fornecedor_id);
        }
        $item = Produto::where('id', $request->produto_id)
        ->first();

        $item->cfop_atual = $item->cfop_estadual;
        if($entrada == 1){
            $item->cfop_atual = $item->cfop_entrada_estadual;
        }

        $empresa = Empresa::find($item->empresa_id);
        if ($empresa != null) {
            if ($cliente != null) {
                if ($empresa->cidade->uf != $cliente->cidade->uf) {
                    $item->cfop_atual = $item->cfop_outro_estado;
                }
            }

            if ($fornecedor != null) {
                if ($empresa->cidade->uf != $fornecedor->cidade->uf) {
                    $item->cfop_atual = $item->cfop_entrada_outro_estado;
                }
            }
        }
        return response()->json($item, 200);
    }

    public function findId($id)
    {
        $item = Produto::where('id', $id)

        ->with(['categoria', 'adicionais'])
        ->first();

        return response()->json($item, 200);
    }

    public function findWithLista(Request $request)
    {
        $lista_id = $request->lista_id;
        $item = Produto::where('id', $request->produto_id)
        ->with(['categoria', 'adicionais'])
        ->first();

        $itemLista = ItemListaPreco::where('lista_id', $lista_id)
        ->where('produto_id', $item->id)
        ->first();

        $item->valor_unitario = $itemLista->valor;

        return response()->json($item, 200);
    }

    public function padrao(Request $request)
    {
        $item = PadraoTributacaoProduto::
        with('_ncm')
        ->findOrFail($request->padrao);
        return response()->json($item, 200);
    }

    public function findByCategory(Request $request)
    {
        $id = $request->id;
        $lista_id = $request->lista_id;

        $item = CategoriaProduto::findOrFail($id);
        $produtos = $item->produtos;
        if($lista_id){
            foreach($produtos as $p){
                $itemLista = ItemListaPreco::where('lista_id', $lista_id)
                ->where('produto_id', $p->id)
                ->first();
                if($itemLista != null){
                    $p->valor_unitario = $itemLista->valor;
                }
            }
        }
        return view('produtos.cards', compact('produtos'));
    }

    public function all(Request $request)
    {
        $lista_id = $request->lista_id;
        $produtos = Produto::where('empresa_id', $request->empresa_id)
        ->limit(50)->get();

        if($lista_id){
            foreach($produtos as $p){
                $itemLista = ItemListaPreco::where('lista_id', $lista_id)
                ->where('produto_id', $p->id)
                ->first();
                if($itemLista != null){
                    $p->valor_unitario = $itemLista->valor;
                }
            }
        }
        
        return view('produtos.cards', compact('produtos'));
    }


    public function getPizzas(Request $request){
        $produto_id = $request->produto_id;
        $tamanho_id = $request->tamanho_id;

        $data = Produto::where('produtos.empresa_id', $request->empresa_id)
        ->select('produtos.*')
        ->join('categoria_produtos', 'categoria_produtos.id', '=', 'produtos.categoria_id')
        ->where('categoria_produtos.tipo_pizza', 1)
        ->get();

        return view('produtos.pizzas', compact('data', 'produto_id', 'tamanho_id'));
    }

    public function calculoPizza(Request $request){
        $sabores = $request->sabores;
        $tamanho_id = $request->tamanho_id;

        $somaValor = 0;
        $maiorValor = 0;
        if($sabores == null){
            return response()->json(0, 200);
        }
        $qtdSabores = sizeof($sabores);
        
        foreach($sabores as $s){
            $item = ProdutoPizzaValor::where('produto_id', (int)$s)
            ->where('tamanho_id', $tamanho_id)
            ->first();

            if($item->valor > $maiorValor){
                $maiorValor = $item->valor;
            }

            $somaValor += $item->valor;

        }
        $config = ConfiguracaoCardapio::where('empresa_id', $request->empresa_id)
        ->first();

        if($config->valor_pizza == 'divide'){
            return response()->json((float)number_format($somaValor/$qtdSabores, 2), 200);
        }else{
            return response()->json((float)number_format($maiorValor, 2), 200);
        }
    }

    public function findByBarcode(Request $request)
    {
        $lista_id = $request->lista_id;

        $item = Produto::with('estoque')
        ->where('codigo_barras', $request->barcode)
        ->where('empresa_id', $request->empresa_id)
        ->first();

        if($lista_id){
            $itemLista = ItemListaPreco::where('lista_id', $lista_id)
            ->where('produto_id', $item->id)
            ->first();

            if($itemLista != null){
                $item->valor_unitario = $itemLista->valor;
            }
        }

        if($item == null){
            $item = ProdutoVariacao::where('produto_variacaos.codigo_barras', $request->barcode)
            ->where('produtos.empresa_id', $request->empresa_id)
            ->join('produtos', 'produtos.id', '=', 'produto_variacaos.produto_id')
            ->select('produto_variacaos.*')
            ->first();

            $item->codigo_variacao = $item->id;
            $item->valor_unitario = $item->valor;
            $item->nome = $item->produto->nome . " - " . $item->descricao;
            $item->codigo_variacao = $item->id;
            $item->id = $item->produto_id;
        }
        return response()->json($item, 200);
    }

    public function findByBarcodeReference(Request $request)
    {
        $config = ConfigGeral::where('empresa_id', request()->empresa_id)
        ->first();
        $balanca_valor_peso = $config != null ? $config->balanca_valor_peso : 'valor';
        $balanca_digito_verificador = $config != null ? $config->balanca_digito_verificador : 6;
        $barcode = $request->barcode;
        $ref = (int)substr($barcode, 1, $balanca_digito_verificador);
        $valor = (float)substr($barcode, 7, 12);
        $valor = $valor / 1000;
        $quantidade = 1;

        $item = Produto::with('estoque')
        ->where('referencia_balanca', $ref)
        ->where('empresa_id', $request->empresa_id)
        ->first();

        if ($item->unidade == 'KG') {
            if ($balanca_valor_peso == 'valor') {
                $quantidade = $valor / $item->valor_unitario;
                $subtotal = $item->valor_unitario * number_format($quantidade, 3);
            } else {
                $quantidade = $valor / $item->valor_unitario;
                $valor = $item->valor_unitario * number_format($quantidade, 3);
                $subtotal = $valor;
            }
        }
        if ($item) {
            $item->valor = $valor;
            $item->quantidade = $quantidade;
        }

        return view('front_box.partials.row_produtos_referencia', compact('item', 'quantidade', 'valor', 'subtotal'));
    }

    public function infoVencimento($id)
    {
        $item = Produto::findOrFail($id);
        $itens = ItemNfe::where('produto_id', $item->id)->get();

        return view('produtos.partials.info_vencimento', compact('itens'));
    }

    public function validaEstoque(Request $request)
    {
        $produto = Produto::findOrFail($request->product_id);
        $qtd = $request->qtd;

        if($produto->gerenciar_estoque){
            if(!$produto->estoque){
                return response()->json("Produto sem estoque definido!", 401);
            }

            if($produto->estoque->quantidade < $qtd){
                return response()->json("Estoque insuficiente!", 401);
            }
        }
        return response()->json($produto, 200);
    }

    public function pesquisaCompostos(Request $request)
    {
        $lista_id = $request->lista_id;
        $data = Produto::orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('composto', 1)
        ->when(!is_numeric($request->pesquisa), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->pesquisa%");
        })
        ->when(is_numeric($request->pesquisa), function ($q) use ($request) {
            return $q->where('codigo_barras', 'LIKE', "%$request->pesquisa%");
        })
        ->get();

        if(is_numeric($request->pesquisa)){
            $dataAppend = ProdutoVariacao::where('produtos.empresa_id', $request->empresa_id)
            ->where('produto_variacaos.codigo_barras', 'LIKE', "%$request->pesquisa%")
            ->join('produtos', 'produtos.id', '=', 'produto_variacaos.produto_id')
            ->select('produto_variacaos.*')
            ->get();

            foreach($dataAppend as $v){
                $v->valor_unitario = $v->valor;
                $v->valor_compra = $v->produto->valor_compra;
                $v->nome = $v->produto->nome . " - " . $v->descricao;
                $v->codigo_variacao = $v->id;
                $v->id = $v->produto_id;
                $data->push($v);
            }

            // $data->push($dataAppend);
        }

        if($lista_id){

            foreach($data as $i){
                $itemLista = ItemListaPreco::where('lista_id', $lista_id)
                ->where('produto_id', $i->id)
                ->first();
                if($itemLista != null){
                    $i->valor_unitario = $itemLista->valor;
                }
            }
        }

        return response()->json($data, 200);
    }
}
