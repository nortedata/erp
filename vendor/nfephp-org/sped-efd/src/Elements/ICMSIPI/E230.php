<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class E230 extends Element
{
    const REG = 'E230';
    const LEVEL = 5;
    const PARENT = 'E220';

    protected $parameters = [
        'NUM_DA' => [
            'type'     => 'string',
            'regex'    => '^.*$',
            'required' => false,
            'info'     => 'Número do documento de arrecadação estadual, se houver',
            'format'   => ''
        ],
        'NUM_PROC' => [
            'type'     => 'string',
            'regex'    => '^.{1,15}$',
            'required' => false,
            'info'     => 'Número do processo ao qual o ajuste está vinculado, se houver',
            'format'   => ''
        ],
        'IND_PROC' => [
            'type'     => 'integer',
            'regex'    => '^[0|1|2|9]$',
            'required' => false,
            'info'     => 'Indicador da origem do processo:'
            .'0- Sefaz;'
            .'1- Justiça Federal;'
            .'2- Justiça Estadual;'
            .'9- Outros',
            'format'   => ''
        ],
        'PROC' => [
            'type'     => 'string',
            'regex'    => '^.*$',
            'required' => false,
            'info'     => 'Descrição resumida do processo que embasou o lançamento',
            'format'   => ''
        ],
        'TXT_COMPL' => [
            'type'     => 'string',
            'regex'    => '^.*$',
            'required' => false,
            'info'     => 'Descrição complementar',
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
