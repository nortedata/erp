<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoProduto extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'produto_id', 'quantidade', 'tipo', 'codigo_transacao', 'tipo_transacao', 'produto_variacao_id'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function tipoTransacao(){
        if($this->tipo_transacao == 'venda_nfe'){
            return 'Venda NFe';
        }else if($this->tipo_transacao == 'venda_nfce'){
            return 'Venda NFCe';
        }else if($this->tipo_transacao == 'compra'){
            return 'Compra';
        }else{
            return 'Alteração de estoque';
        }
    }
}
