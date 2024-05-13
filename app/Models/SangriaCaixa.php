<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SangriaCaixa extends Model
{
    use HasFactory;

    protected $fillable = [
        'caixa_id', 'valor', 'observacao'
    ];
}
