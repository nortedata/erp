<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estoque;
use App\Utils\EstoqueUtil;

class EstoqueController extends Controller
{

    protected $util;

    public function __construct(EstoqueUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request){
        $data = Estoque::select('estoques.*')
        ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
        ->where('produtos.empresa_id', request()->empresa_id)
        ->when(!empty($request->produto), function ($q) use ($request) {
            return $q->where('produtos.nome', 'LIKE', "%$request->produto%");
        })
        ->orderBy('id', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('estoque.index', compact('data'));
    }

    public function create()
    {
        return view('estoque.create');
    }

    public function store(Request $request)
    {
        // dd($request);
        try {
            $file_name = $this->util->incrementaEstoque($request->produto_id, $request->quantidade, $request->produto_variacao_id);

            $transacao = Estoque::where('produto_id', $request->produto_id)->first();
            $tipo = 'incremento';
            $codigo_transacao = $transacao->id;
            $tipo_transacao = 'alteracao_estoque';

            $this->util->movimentacaoProduto($request->produto_id, $request->quantidade, $tipo, $codigo_transacao, $tipo_transacao, $request->produto_variacao_id);

            session()->flash("flash_success", "Estoque adicionado com sucesso!");
        } catch (\Exception $e) {
            // echo $e->getLine();
            // die;
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('estoque.index');
    }
}
