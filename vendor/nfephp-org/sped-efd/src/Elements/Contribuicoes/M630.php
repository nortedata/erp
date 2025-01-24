<?php

namespace NFePHP\EFD\Elements\Contribuicoes;

use NFePHP\EFD\Common\Element;
use stdClass;

class M630 extends Element
{
    const REG = 'M630';
    const LEVEL = 4;
    const PARENT = 'M600';

    protected $parameters = [
        'CNPJ' => [
            'type' => 'string',
            'regex' => '^[0-9]{14}$',
            'required' => false,
            'info' => 'CNPJ da pessoa jurídica de direito público, empresa pública, sociedade de economia ' .
                'mista ou suas subsidiárias. ',
            'format' => ''
        ],
        'VL_VEND' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor Total das vendas no período ',
            'format' => '15v2'
        ],
        'VL_NAO_RECEB' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor Total não recebido no período ',
            'format' => '15v2'
        ],
        'VL_CONT_DIF' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor da Contribuição diferida no período ',
            'format' => '15v2'
        ],
        'VL_CRED_DIF' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor do Crédito diferido no período ',
            'format' => '15v2'
        ],
        'COD_CRED' => [
            'type' => 'string',
            'regex' => '^.{3}$',
            'required' => false,
            'info' => 'Código de Tipo de Crédito diferido no período, conforme a Tabela 4.3.6. ',
            'format' => ''
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
        $this->postValidation();
    }
}
