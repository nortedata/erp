<?php

namespace NFePHP\EFD\Elements\Contribuicoes;

use NFePHP\EFD\Common\Element;
use stdClass;

class P200 extends Element
{
    const REG = 'P200';
    const LEVEL = 3;
    const PARENT = 'P001';

    protected $parameters = [
        'PER_REF' => [
            'type' => 'numeric',
            'regex' => '^(\d{6})$',
            'required' => false,
            'info' => 'Período de referencia da escrituração (MMAAAA) ',
            'format' => ''
        ],
        'VL_TOT_CONT_APU' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor total apurado da Contribuição Previdenciária sobre a Receita Bruta (Somatório do ' .
                'Campo 10 “VL_CONT_APU“, do(s) Registro(s) P100) ',
            'format' => '15v2'
        ],
        'VL_TOT_AJ_REDUC' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor total de “Ajustes de redução” (Registro P210, Campo 03, quando Campo 02 = ' .
                '“0”) ',
            'format' => '15v2'
        ],
        'VL_TOT_AJ_ACRES' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor total de “Ajustes de acréscimo” (Registro P210, Campo 03, quando Campo 02 = ' .
                '“1”) ',
            'format' => '15v2'
        ],
        'VL_TOT_CONT_DEV' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor total da Contribuição Previdenciária sobre a Receita Bruta a recolher (Campo 03 ' .
                '– Campo 04 + Campo 05) ',
            'format' => '15v2'
        ],
        'COD_REC' => [
            'type' => 'string',
            'regex' => '^.{6}$',
            'required' => false,
            'info' => 'Código de Receita referente à Contribuição Previdenciária, conforme informado em DCTF ',
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
