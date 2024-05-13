<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigGeral extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'empresa_id', 'balanca_digito_verificador', 'balanca_valor_peso', 'confirmar_itens_prevenda', 'notificacoes'
    ];

    public static function getNotificacoes(){
        return [
            'Contas a pagar', 'Contas a receber', 'Alerta de estoque', 'Alerta de validade', 'Ticket'
        ];
    }
}
