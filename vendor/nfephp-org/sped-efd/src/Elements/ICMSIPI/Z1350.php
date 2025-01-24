<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class Z1350 extends Element
{
    const REG = '1350';
    const LEVEL = 2;
    const PARENT = '1001';

    protected $parameters = [
        'SERIE' => [
            'type'     => 'string',
            'regex'    => '^.*$',
            'required' => true,
            'info'     => 'Número de Série da Bomba',
            'format'   => ''
        ],
        'FABRICANTE' => [
            'type'     => 'string',
            'regex'    => '^.{1,60}$',
            'required' => true,
            'info'     => 'Nome do Fabricante da Bomba',
            'format'   => ''
        ],
        'MODELO' => [
            'type'     => 'string',
            'regex'    => '^.*$',
            'required' => true,
            'info'     => 'Modelo da Bomba',
            'format'   => ''
        ],
        'TIPO_MEDICAO' => [
            'type'     => 'string',
            'regex'    => '^[0|1]$',
            'required' => true,
            'info'     => 'Identificador de medição: '
            .'0 - analógico; '
            .'1 – digital',
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
