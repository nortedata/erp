<?php

namespace NFePHP\EFD\Elements\Contribuicoes;

use NFePHP\EFD\Common\Element;
use stdClass;

class D200 extends Element
{
    const REG = 'D200';
    const LEVEL = 3;
    const PARENT = 'D001';

    protected $parameters = [
        'COD_MOD' => [
            'type' => 'string',
            'regex' => '^(07|08|8B|09|10|11|26|27|57|63|67)$',
            'required' => false,
            'info' => 'Código do modelo do documento fiscal, conforme a Tabela 4.1.1 ',
            'format' => ''
        ],
        'COD_SIT' => [
            'type' => 'numeric',
            'regex' => '^(00|01|06|07|08)$',
            'required' => false,
            'info' => 'Código da situação do documento fiscal, conforme a Tabela 4.1.2 ',
            'format' => ''
        ],
        'SER' => [
            'type' => 'string',
            'regex' => '^.{0,4}$',
            'required' => false,
            'info' => 'Série do documento fiscal ',
            'format' => ''
        ],
        'SUB' => [
            'type' => 'string',
            'regex' => '^.{0,3}$',
            'required' => false,
            'info' => 'Subsérie do documento fiscal ',
            'format' => ''
        ],
        'NUM_DOC_INI' => [
            'type' => 'numeric',
            'regex' => '^(\d{0,9})$',
            'required' => false,
            'info' => 'Número do documento fiscal inicial emitido no período (mesmo modelo, série e ' .
                'subsérie). ',
            'format' => ''
        ],
        'NUM_DOC_FIN' => [
            'type' => 'numeric',
            'regex' => '^(\d{0,9})$',
            'required' => false,
            'info' => 'Número do documento fiscal final emitido no período (mesmo modelo, série e subsérie). ',
            'format' => ''
        ],
        'CFOP' => [
            'type' => 'numeric',
            'regex' => '^(\d{4})$',
            'required' => false,
            'info' => 'Código Fiscal de Operação e Prestação conforme tabela indicada no item 4.2.2 ',
            'format' => ''
        ],
        'DT_REF' => [
            'type' => 'string',
            'regex' => '^(0[1-9]|[1-2][0-9]|31(?!(?:0[2469]|11))|30(?!02))(0[1-9]|1[0-2])([12]\d{3})$',
            'required' => false,
            'info' => 'Data do dia de referência do resumo diário ',
            'format' => ''
        ],
        'VL_DOC' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor total dos documentos fiscais ',
            'format' => '15v2'
        ],
        'VL_DESC' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor total dos descontos ',
            'format' => '15v2'
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
