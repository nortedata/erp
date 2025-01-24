<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class Z1900 extends Element
{
    const REG = '1900';
    const LEVEL = 2;
    const PARENT = '1001';

    protected $parameters = [
        'IND_APUR_ICMS' => [
            'type'     => 'string',
            'regex'    => '^[3|4|5|6|7|8]$',
            'required' => true,
            'info'     => 'Indicador de outra apuração do ICMS: '
            .'3 – APURAÇÃO 1; '
            .'4 – APURAÇÃO 2; '
            .'5 – APURAÇÃO 3; '
            .'6 – APURAÇÃO 4; '
            .'7 – APURAÇÃO 5; '
            .'8 – APURAÇÃO 6.',
            'format'   => ''
        ],
        'DESCR_COMPL_OUT_APUR' => [
            'type'     => 'string',
            'regex'    => '^.*$',
            'required' => true,
            'info'     => 'Descrição complementar de Outra Apuração do ICMS',
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
