<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class E001 extends Element
{
    const REG = 'E001';
    const LEVEL = 1;
    const PARENT = '';

    protected $parameters = [
        'IND_MOV' => [
            'type'     => 'numeric',
            'regex'    => '^[0|1]{1}$',
            'required' => true,
            'info'     => 'Indicador de movimento:'
            . '0- Bloco com dados informados;'
            . '1- Bloco sem dados informados',
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
