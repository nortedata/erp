<?php

namespace NFePHP\EFD\Elements\Contribuicoes;

use NFePHP\EFD\Common\Element;
use stdClass;

class Z1900 extends Element
{
    const REG = '1900';
    const LEVEL = 2;
    const PARENT = '1001';

    protected $parameters = [
        'CNPJ' => [
            'type' => 'string',
            'regex' => '^[0-9]{14}$',
            'required' => false,
            'info' => 'CNPJ do estabelecimento da pessoa jurídica, emitente dos documentos geradores de receita ',
            'format' => ''
        ],
        'COD_MOD' => [
            'type' => 'string',
            'regex' => '^(98|99)$',
            'required' => false,
            'info' => 'Código do modelo do documento fiscal conforme a Tabela 4.1.1, ou ' .
                ' 98 – Nota Fiscal de Prestação de Serviços (ISSQN) 99 – Outros Documentos ',
            'format' => ''
        ],
        'SER' => [
            'type' => 'string',
            'regex' => '^.{0,4}$',
            'required' => false,
            'info' => 'Série do documento fiscal ',
            'format' => ''
        ],
        'SUB_SER' => [
            'type' => 'numeric',
            'regex' => '^(\d{0,20})$',
            'required' => false,
            'info' => 'Subserie do documento fiscal ',
            'format' => ''
        ],
        'COD_SIT' => [
            'type' => 'numeric',
            'regex' => '^(00|02|09)$',
            'required' => false,
            'info' => 'Código da situação do documento fiscal ' .
                ' 00 – Documento regular 02 – Documento cancelado 99 – Outros ',
            'format' => ''
        ],
        'VL_TOT_REC' => [
            'type' => 'numeric',
            'regex' => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info' => 'Valor total da receita, conforme os documentos emitidos no período, representativos da ' .
                'venda de bens e serviços ',
            'format' => '15v2'
        ],
        'QUANT_DOC' => [
            'type' => 'numeric',
            'regex' => '^([0-9]+)$',
            'required' => false,
            'info' => 'Quantidade total de documentos emitidos no período ',
            'format' => ''
        ],
        'CST_PIS' => [
            'type' => 'string',
            'regex' => '^((0[1-9])|49|99)$',
            'required' => false,
            'info' => 'Código da Situação Tributária do PIS/Pasep ',
            'format' => ''
        ],
        'CST_COFINS' => [
            'type' => 'string',
            'regex' => '^((0[1-9])|49|99)$',
            'required' => false,
            'info' => 'Código da Situação Tributária da Cofins ',
            'format' => ''
        ],
        'CFOP' => [
            'type' => 'numeric',
            'regex' => '^(\d{4})$',
            'required' => false,
            'info' => 'Código fiscal de operação e prestação ',
            'format' => ''
        ],
        'INF_COMPL' => [
            'type' => 'string',
            'regex' => '^(.*)$',
            'required' => false,
            'info' => 'Informações complementares ',
            'format' => ''
        ],
        'COD_CTA' => [
            'type' => 'string',
            'regex' => '^.{0,255}$',
            'required' => false,
            'info' => 'Código da conta analítica contábil representativa da receita ',
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
