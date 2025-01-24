<?php

namespace NFePHP\EFD\Elements\Contribuicoes;

use NFePHP\EFD\Common\Element;
use stdClass;

class Z0140 extends Element
{
    const REG = '0140';
    const LEVEL = 2;
    const PARENT = '000';

    protected $parameters = [
        'COD_EST' => [
            'type' => 'string',
            'regex' => '^.{0,60}$',
            'required' => false,
            'info' => 'Código de identificação do estabelecimento',
            'format' => ''
        ],
        'NOME' => [
            'type' => 'string',
            'regex' => '^.{0,100}$',
            'required' => false,
            'info' => 'Nome empresarial do estabelecimento',
            'format' => ''
        ],
        'CNPJ' => [
            'type' => 'string',
            'regex' => '^[0-9]{14}$',
            'required' => false,
            'info' => 'Número de inscrição do estabelecimento no CNPJ.',
            'format' => ''
        ],
        'UF' => [
            'type' => 'string',
            'regex' => '^.{2}$',
            'required' => false,
            'info' => 'Sigla da unidade da federação do estabelecimento.',
            'format' => ''
        ],
        'IE' => [
            'type' => 'string',
            'regex' => '^[0-9]{2,14}$',
            'required' => false,
            'info' => 'Inscrição Estadual do estabelecimento, se contribuinte de ICMS.',
            'format' => ''
        ],
        'COD_MUN' => [
            'type' => 'numeric',
            'regex' => '^(\d{7})$',
            'required' => false,
            'info' => 'Código do município do domicílio fiscal do estabelecimento, conforme a tabela IBGE',
            'format' => ''
        ],
        'IM' => [
            'type' => 'string',
            'regex' => '^(.*)$',
            'required' => false,
            'info' => 'Inscrição Municipal do estabelecimento, se contribuinte do ISS.',
            'format' => ''
        ],
        'SUFRAMA' => [
            'type' => 'string',
            'regex' => '^.{9}$',
            'required' => false,
            'info' => 'Inscrição do estabelecimento na Suframa',
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
