<?php

namespace App\Utils;

use Illuminate\Support\Str;
use App\Models\Estoque;
use App\Models\Produto;
use App\Models\MovimentacaoProduto;

class EstoqueUtil
{
    public function incrementaEstoque($produto_id, $quantidade, $produto_variacao_id = null)
    {
        $item = Estoque::where('produto_id', $produto_id)
        ->when($produto_variacao_id != null, function ($q) use ($produto_variacao_id) {
            return $q->where('produto_variacao_id', $produto_variacao_id);
        })
        ->first();
        if ($item != null) {
            $item->quantidade += (float)$quantidade;
            $item->save();
        } else {
            Estoque::create([
                'produto_id' => $produto_id,
                'quantidade' => $quantidade,
                'produto_variacao_id' => $produto_variacao_id
            ]);
        }
    }

    public function reduzEstoque($produto_id, $quantidade, $produto_variacao_id = null)
    {
        $item = Estoque::where('produto_id', $produto_id)
        ->when($produto_variacao_id != null, function ($q) use ($produto_variacao_id) {
            return $q->where('produto_variacao_id', $produto_variacao_id);
        })
        ->first();
        if ($item != null) {
            $item->quantidade -= (float)$quantidade;
            $item->save();
        }
    }

    public function reduzComposicao($produto_id, $quantidade, $produto_variacao_id = null)
    {
        $produto = Produto::findOrFail($produto_id);
        foreach ($produto->composicao as $item) {
            $this->reduzEstoque($item->ingrediente_id, ($item->quantidade * $quantidade), $produto_variacao_id);
        }
        $this->incrementaEstoque($produto_id, $quantidade, $produto_variacao_id);
    }

    public function verificaEstoqueComposicao($produto_id, $quantidade, $produto_variacao_id = null)
    {
        $produto = Produto::findOrFail($produto_id);
        $mensagem = "";
        foreach ($produto->composicao as $item) {
            $qtd = $item->quantidade * $quantidade;

            if($item->ingrediente->estoque){
                if($qtd > $item->ingrediente->estoque->quantidade){
                    $mensagem .= $item->ingrediente->nome . " com estoque insuficiente | ";
                }
            }else{
                $mensagem .= $item->ingrediente->nome . " sem nenhum estoque cadastrado | ";
            }
        }
        $mensagem = substr($mensagem, 0, strlen($mensagem)-2);
        return $mensagem;

    }

    public function movimentacaoProduto($produto_id, $quantidade, $tipo, $codigo_transacao, $tipo_transacao, 
        $produto_variacao_id = null){
        MovimentacaoProduto::create([
            'produto_id' => $produto_id,
            'quantidade' => $quantidade,
            'tipo' => $tipo,
            'codigo_transacao' => $codigo_transacao,
            'tipo_transacao' => $tipo_transacao,
            'produto_variacao_id' => $produto_variacao_id
        ]);
    }

}
