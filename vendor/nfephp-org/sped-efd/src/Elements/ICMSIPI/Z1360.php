<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class Z1360 extends Element
{
    const REG = '1360';
    const LEVEL = 3;
    const PARENT = '1350';

    protected $parameters = [
        'NUM_LACRE' => [
            'type'     => 'string',
            'regex'    => '^.{1,20}$',
            'required' => true,
            'info'     => 'Número do Lacre associado na Bomba',
            'format'   => ''
        ],
        'DT_APLICACAO' => [
            'type'     => 'integer',
            'regex'    => '^(0[1-9]|[1-2][0-9]|31(?!(?:0[2469]|11))|30(?!02))(0[1-9]|1[0-2])([12]\d{3})$',
            'required' => true,
            'info'     => 'Data de aplicação do Lacre',
            'format'   => ''
        ]
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
