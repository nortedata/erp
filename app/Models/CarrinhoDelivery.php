<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrinhoDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'empresa_id', 'estado', 'valor_total', 'endereco_id', 'valor_frete', 'session_cart_delivery',
        'valor_desconto', 'cupom'
    ];

    public function itens(){
        return $this->hasMany(ItemCarrinhoDelivery::class, 'carrinho_id');
    }

    public function endereco(){
        return $this->belongsTo(EnderecoDelivery::class, 'endereco_id');
    }
}
