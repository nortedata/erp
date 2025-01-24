<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

/**
 * REGISTRO H010: INVENTÁRIO.
 * Este registro deve ser informado para discriminar os itens existentes no estoque.
 * Este registro não pode ser fornecido se o campo 03 (VL_INV) do registro H005
 * for igual a “0” (zero).
 * A partir de janeiro de 2015, caso o contribuinte utilize o bloco H para
 * atender à legislação do Imposto de Renda, especificamente o artigo 261 do
 * Regulamento do Imposto de Renda – RIR/99 – Decreto nº 3.000/1999, deverá
 * informar neste registro, além dos itens exigidos pelas legislações do ICMS e
 * do IPI, aqueles bens exigidos pela legislação do Imposto de Renda.
 */
class H010 extends Element
{
    const REG = 'H010';
    const LEVEL = 3;
    const PARENT = 'H005';

    protected $parameters = [
        'COD_ITEM' => [
            'type'     => 'string',
            'regex'    => '^.{1,60}',
            'required' => true,
            'info'     => 'Código do item (campo 02 do Registro 0200)',
            'format'   => ''
        ],
        'UNID' => [
            'type'     => 'string',
            'regex'    => '^.{1,6}',
            'required' => true,
            'info'     => 'Unidade do item',
            'format'   => ''
        ],
        'QTD' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Quantidade do item',
            'format'   => '15v3'
        ],
        'VL_UNIT' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Valor unitário do item',
            'format'   => '15v6'
        ],
        'VL_ITEM' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => true,
            'info'     => 'Valor do item',
            'format'   => '15v2'
        ],
        'IND_PROP' => [
            'type'     => 'integer',
            'regex'    => '^[0-2]{1}$',
            'required' => true,
            'info'     => 'Indicador de propriedade/posse do item: '
            . '0-Item de propriedade do informante e em seu poder; '
            . '1-Item de propriedade do informante em posse de terceiros; '
            . '2- Item de propriedade de terceiros em posse do informante',
            'format'   => ''
        ],
        'COD_PART' => [
            'type'     => 'string',
            'regex'    => '^.{1,60}$',
            'required' => false,
            'info'     => 'Código do participante (campo 02 do Registro 0150): '
            . '- proprietário/possuidor que não seja o informante do arquivo',
            'format'   => ''
        ],
        'TXT_COMPL' => [
            'type'     => 'string',
            'regex'    => '^.{3,255}',
            'required' => false,
            'info'     => 'Descrição complementar.',
            'format'   => ''
        ],
        'COD_CTA' => [
            'type'     => 'string',
            'regex'    => '^.{1,255}',
            'required' => false,
            'info'     => 'Código da conta analítica contábil debitada/creditada',
            'format'   => ''
        ],
        'VL_ITEM_IR' => [
            'type'     => 'numeric',
            'regex'    => '^\d+(\.\d*)?|\.\d+$',
            'required' => false,
            'info'     => 'Valor do item para efeitos do Imposto de Renda.',
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
    }
}
