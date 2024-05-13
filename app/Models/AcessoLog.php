<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcessoLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id', 'ip'
    ];
}
