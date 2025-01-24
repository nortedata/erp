<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class Z1255 extends Element
{
    const REG = '1255';
    const LEVEL = 3;
    const PARENT = '1250';

    protected $parameters = [
        'COD_MOT_REST_COMPL' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{5}$',
            'required' => true,
            'info'     => 'Código do motivo da restituição ou complementação conforme Tabela 5.7',
            'format'   => ''
        ],
        'VL_CREDITO_ICMS_OP_MOT' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do ICMS operação própria que o informante tem direito ao crédito, '
            .'na forma prevista na legislação, referente às hipóteses de restituição em que há previsão deste crédito,'
            .' para o mesmo COD_MOT_REST_COMPL',
            'format'   => '15v2'
        ],
        'VL_ICMS_ST_REST_MOT' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do ICMS/ST que o informante tem direito ao crédito, na forma '
            .'prevista na legislação, referente às hipóteses de restituição em que há previsão deste crédito, para '
            .'o mesmo COD_MOT_REST_COMPL',
            'format'   => '15v2'
        ],
        'VL_FCP_ST_REST_MOT' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do FCP_ST agregado ao valor do ICMS/ST informado no campo '
            .'VL_ICMS_ST_REST_MOT',
            'format'   => '15v2'
        ],
        'VL_ICMS_ST_COMPL_MOT' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do débito referente ao complemento do imposto, nos casos previstos '
            .'na legislação, para o mesmo COD_MOT_REST_COMPL',
            'format'   => '15v2'
        ],
        'VL_FCP_ST_COMPL_MOT' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Informar o valor total do FCP_ST agregado ao valor informado no campo VL_ICMS_ST_COMPL_MOT',
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
