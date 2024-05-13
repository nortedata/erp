<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPreVenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id', 'pre_venda_id', 'quantidade', 'valor',
        'observacao', 'cfop', 'variacao_id'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
