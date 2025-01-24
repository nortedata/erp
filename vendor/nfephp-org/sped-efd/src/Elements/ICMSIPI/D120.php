<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

class D120 extends Element
{
    const REG = 'D120';
    const LEVEL = 4;
    const PARENT = '';

    protected $parameters = [
        'COD_MUN_ORIG' => [
            'type'     => 'numeric',
            'regex'    => '',
            'required' => true,
            'info'     => 'Código do município de origem do serviço, conforme a tabela IBGE',
            'format'   => ''
        ],
        'COD_MUN_DEST' => [
            'type'     => 'numeric',
            'regex'    => '',
            'required' => true,
            'info'     => 'Código do município de destino, conforme a tabela IBGE',
            'format'   => ''
        ],
        'VEIC_ID' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{7}$',
            'required' => true,
            'info'     => 'Placa de identificação do veículo',
            'format'   => ''
        ],
        'UF_ID' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{2}$',
            'required' => true,
            'info'     => 'Sigla da UF da placa do veículo',
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
