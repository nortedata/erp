<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class Z1250 extends Element
{
    const REG = '1250';
    const LEVEL = 2;
    const PARENT = '1001';

    protected $parameters = [
        'VL_CREDITO_ICMS_OP' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do ICMS operação própria que o informante tem direito ao crédito, '
            .'na forma prevista na legislação, referente às hipóteses de restituição em que há previsão deste crédito.',
            'format'   => '15v2'
        ],
        'VL_ICMS_ST_REST' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do ICMS/ST que o informante tem direito ao crédito, na forma '
            .'prevista na legislação, referente às hipóteses de restituição em que há previsão deste crédito.',
            'format'   => '15v2'
        ],
        'VL_FCP_ST_REST' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do FCP_ST agregado ao valor do ICMS/ST informado no '
            .'campo VL_ICMS_ST_REST.',
            'format'   => '15v2'
        ],
        'VL_ICMS_ST_COMPL' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do débito referente ao complemento do imposto, Nos casos '
            .'previstos na legislação.',
            'format'   => '15v2'
        ],
        'VL_FCP_ST_COMPL' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do FCP_ST agregado ao valor informado no campo VL_ICMS_ST_COMPL',
            'format'   => '15v2'
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
