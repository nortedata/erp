<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Produto;
use App\Models\Servico;
use Illuminate\Http\Request;

class OrdemServicoController extends Controller
{
    public function find($id){
        $item = Servico::with('categoria')->where('id', $id)
        ->first();
        return response()->json($item, 200);
    }

    public function findProduto($id){
        $item = Produto::where('id', $id)
        ->first();
        return response()->json($item, 200);
    }

    public function linhaServico(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $valor = __convert_value_bd($request->valor);
            $servico_id = $request->servico_id;
            $status = $request->status;
            $nome = $request->nome;

            $servico = OrdemServico::findOrFail($servico_id);
            return view('ordem_servico.partials.row_servico', compact('servico', 'qtd', 'valor', 'status', 'nome'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaProduto(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $valor = __convert_value_bd($request->valor);
            $produto_id = $request->produto_id;

            $produto = OrdemServico::findOrFail($produto_id);
            return view('ordem_servico.partials.row_produto', compact('produto', 'qtd', 'valor'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }
}
