<?php

namespace NFePHP\EFD\Elements\Contribuicoes;

use NFePHP\EFD\Common\Element;
use stdClass;

class M410 extends Element
{
    const REG = 'M410';
    const LEVEL = 3;
    const PARENT = 'M400';

    protected $parameters = [
        'NAT_REC' => [
            'type' => 'string',
            'regex' => '^(4|4|5)$',
            'required' => false,
            'info' => 'Natureza da Receita, conforme relação constante nas Tabelas de Detalhamento da Natureza ' .
                'da Receita por Situação Tributária abaixo ' .
                ' - Tabela 4.3.10 ' .
                ' Produtos Sujeitos à Incidência Monofásica da Contribuição Social – Alíquotas ' .
                'Diferenciadas (CST 04 - Revenda) ' .
                ' - Tabela 4.3.11 ' .
                ' Produtos Sujeitos à Incidência Monofásica da Contribuição Social – Alíquotas por ' .
                'Unidade de Medida de Produto (CST 04 - Revenda) ' .
                ' - Tabela 4.3.12 ' .
                ' Produtos Sujeitos à Substituição Tributária da Contribuição Social (CST 05 - ' .
                'Revenda) ' .
                ' - Tabela 4.3.13 ' .
                ' Produtos Sujeitos à Alíquota Zero da Contribuição Social (CST 06) ' .
                ' ',
            'format' => ''
        ],
        'VL_REC' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor da receita bruta no período, relativo a natureza da receita (NAT_REC) ',
            'format' => '15v2'
        ],
        'COD_CTA' => [
            'type' => 'string',
            'regex' => '^.{0,255}$',
            'required' => false,
            'info' => 'Código da debitada/creditada. ',
            'format' => ''
        ],
        'DESC_COMPL' => [
            'type' => 'string',
            'regex' => '^(.*)$',
            'required' => false,
            'info' => 'Descrição Complementar da Natureza da Receita. ',
            'format' => ''
        ],

    ];

    /**
     * Constructor
     * @param stdClass $std
     * @param stdClass $vigencia
     */
    public function __construct(stdClass $std, stdClass $vigencia = null)
    {
        parent::__construct(self::REG, $vigencia);
        $this->replaceParams( self::REG);
        $this->std = $this->standarize($std);
        $this->postValidation();
    }
}
