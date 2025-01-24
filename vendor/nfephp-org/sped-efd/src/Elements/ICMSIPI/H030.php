<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

/**
 * REGISTRO H030: Informações complementares do inventário das mercadorias sujeitas ao
 *0 regime de substituição tributária
 *
 */
class H030 extends Element
{
    const REG = 'H030';
    const LEVEL = 4;
    const PARENT = 'H010';

    protected $parameters = [
        'VL_ICMS_OP' => [
            'type'     => 'numeric',
            'regex'     => '',
            'required' => true,
            'info'     => 'Valor médio unitário do ICMS OP',
            'format'   => '15v6'
        ],
        'VL_BC_ICMS_ST' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Valor médio unitário da base de cálculo do ICMS ST',
            'format'   => '15v6'
        ],
        'VL_ICMS_ST' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Valor médio unitário do ICMS ST',
            'format'   => '15v2'
        ],
        'VL_FCP' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Valor médio unitário do FCP',
            'format'   => '15v6'
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
    }
}
