<?php

namespace App\Utils;

use Illuminate\Support\Str;

class ModuloUtil
{
	public function getModulos(){
		return [
			'Produtos', 'Pessoas', 'Compras', 'PDV', 'NFe', 'NFCe', 'CTe', 'MDFe', 'Financeiro', 'Veiculos', 'Serviços',
			'Atendimento', 'Cardapio', 'Agendamentos', 'Delivery', 'Ecommerce', 'NFSe'
		];
	}

}