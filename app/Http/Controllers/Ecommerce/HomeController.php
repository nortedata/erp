<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoriaProduto;
use App\Models\EcommerceConfig;
use App\Models\Produto;
use App\Models\Carrinho;

class HomeController extends Controller
{

    public function __construct(){
        session_start();
    }
    
    public function index(Request $request){
        $config = EcommerceConfig::findOrfail($request->loja_id);
        $categorias = CategoriaProduto::where('ecommerce', 1)
        ->where('empresa_id', $config->empresa_id)->get();

        $produtosEmDestaque = $this->produtosEmDestaque($config->empresa_id);

        $carrinho = $this->_getCarrinho();

        return view('loja.index', compact('config', 'categorias', 'produtosEmDestaque', 'carrinho'));
    }

    public function politicaPrivacidade(Request $request){
        $config = EcommerceConfig::findOrfail($request->loja_id);
        $categorias = CategoriaProduto::where('ecommerce', 1)
        ->where('empresa_id', $config->empresa_id)->get();

        $produtosEmDestaque = $this->produtosEmDestaque($config->empresa_id);

        $carrinho = $this->_getCarrinho();

        return view('loja.politica_privacidade', compact('config', 'categorias', 'produtosEmDestaque', 'carrinho'));
    }

    public function pesquisa(Request $request){

        $categoria_pesquisa = $request->categoria;
        $pesquisa = $request->pesquisa;
        $config = EcommerceConfig::findOrfail($request->loja_id);
        $categorias = CategoriaProduto::where('ecommerce', 1)
        ->where('empresa_id', $config->empresa_id)->get();

        $carrinho = $this->_getCarrinho();

        $produtos = Produto::where('produtos.empresa_id', $config->empresa_id)
        ->select('produtos.*')
        ->where('produtos.status', 1)
        ->where('produtos.ecommerce', 1)
        ->when(!empty($categoria_pesquisa), function ($query) use ($categoria_pesquisa) {
            return $query->join('categoria_produtos', 'categoria_produtos.id', '=', 'produtos.categoria_id')
            ->where('categoria_produtos.hash_ecommerce', $categoria_pesquisa);
        })
        ->when(!empty($pesquisa), function ($query) use ($pesquisa) {
            return $query->where('produtos.nome', 'like', "%$pesquisa%");
        })
        ->get();

        return view('loja.pesquisa', compact('config', 'categorias', 'produtos', 'carrinho', 'categoria_pesquisa', 'pesquisa'));
    }

    private function _getCarrinho(){
        $data = [];
        if(isset($_SESSION["session_cart"])){
            $data = Carrinho::where('session_cart', $_SESSION["session_cart"])
            ->first();
        }
        return $data;
    }

    public function produtosDaCategoria(Request $request, $hash){

        $config = EcommerceConfig::findOrfail($request->loja_id);
        $categorias = CategoriaProduto::where('ecommerce', 1)
        ->where('empresa_id', $config->empresa_id)->get();
        $categoria = CategoriaProduto::where('hash_ecommerce' ,$hash)->first();

        if($categoria == null){
            abort(404);
        }
        $produtos = Produto::where('empresa_id', $config->empresa_id)
        ->where('categoria_id', $categoria->id)
        ->where('status', 1)
        ->where('ecommerce', 1)->get();

        $carrinho = $this->_getCarrinho();

        return view('loja.produtos_categoria', compact('config', 'categorias', 'categoria', 'produtos', 'carrinho'));
    }

    private function produtosEmDestaque($empresa_id){
        return Produto::where('empresa_id', $empresa_id)
        ->where('destaque_ecommerce', 1)
        ->where('status', 1)
        ->where('ecommerce', 1)->get();
    }

    public function produtoDetalhe(Request $request, $hash){
        $config = EcommerceConfig::findOrfail($request->loja_id);
        $produto = Produto::where('empresa_id', $config->empresa_id)
        ->where('hash_ecommerce', $hash)
        ->where('status', 1)->first();

        if($produto == null){
            session()->flash("flash_error", "Produto não encontrado!");
            return redirect()->back();
        }

        $categorias = CategoriaProduto::where('ecommerce', 1)
        ->where('empresa_id', $config->empresa_id)->get();
        $carrinho = $this->_getCarrinho();

        return view('loja.produtos_detalhe', compact('config', 'categorias', 'produto', 'carrinho'));

    }
}
