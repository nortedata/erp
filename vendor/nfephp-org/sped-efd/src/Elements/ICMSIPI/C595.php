<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

/**
 * REGISTRO C595:INFORMAÇÕES DO FUNDO DE COMBATE À POBREZA – FCP NA NF3e (CÓDIGO 66)
 * @package NFePHP\EFD\Elements\ICMSIPI
 */
class C595 extends Element
{
    const REG = 'C595';
    const LEVEL = 3;
    const PARENT = 'C500';

    protected $parameters = [
        'COD_OBS' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{6}$',
            'required' => true,
            'info'     => 'Código da observação do lançamento fiscal (campo 02 do Registro 0460)',
            'format'   => ''
        ],
        'TXT_COMPL' => [
            'type'     => 'string',
            'regex' => '^(.*)$',
            'required' => true,
            'info'     => 'Descrição complementar do código de observação.',
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
