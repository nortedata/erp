<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'usuario_id', 'valor_abertura', 'data_fechamento', 'observacao', 'status', 'valor_fechamento', 'valor_dinheiro',
        'valor_cheque', 'valor_outros'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
