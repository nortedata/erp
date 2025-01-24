<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

/**
 * Elemento 0600 do Bloco 0
 * REGISTRO 0200: TABELA DE IDENTIFICAÇÃO DO ITEM (PRODUTO E SERVIÇOS)
 * Este registro tem por objetivo informar mercadorias, serviços, produtos ou
 * quaisquer outros itens concernentes às transações fiscais e aos movimentos de
 * estoques em processos produtivos, bem como os insumos. Quando ocorrer alteração
 * somente na descrição do item, sem que haja descaracterização deste, ou seja,
 * criação de um novo item, a alteração deve constar no registro 0205.
 * Somente devem ser apresentados itens referenciados nos demais blocos, exceto
 * se for apresentado o fator de conversão no registro 0220 (a partir de julho de 2012).
 * A identificação do item (produto ou serviço) deverá receber o código próprio
 * do informante do arquivo em qualquer documento, lançamento efetuado ou arquivo
 * informado (significa que o código de produto deve ser o mesmo na emissão dos
 * documentos fiscais, na entrada das mercadorias ou em qualquer outra informação
 * prestada ao fisco), observando-se ainda que:
 * a) O código utilizado não pode ser duplicado ou atribuído a itens (produto
 * ou serviço) diferentes. Os produtos e serviços que sofrerem alterações em
 * suas características básicas deverão ser identificados com códigos diferentes.
 * Em caso de alteração de codificação, deverão ser informados o código e a
 * descrição anteriores e as datas de validade inicial e final no registro 0205;
 * b) Não é permitida a reutilização de código que tenha sido atribuído para
 * qualquer produto anteriormente.
 * c) O código de item/produto a ser informado no Inventário deverá ser aquele
 * utilizado no mês inventariado.
 * d) A discriminação do item deve indicar precisamente o mesmo, sendo vedadas
 * discriminações diferentes para o mesmo item ou discriminações genéricas
 * (a exemplo de "diversas entradas", "diversas saídas", "mercadorias para revenda", etc),
 * ressalvadas as operações abaixo, desde que não destinada à posterior circulação
 * ou apropriação na produção:
 * 1- de aquisição de "materiais para uso/consumo" que não gerem direitos a créditos;
 * 2- que discriminem por gênero a aquisição de bens para o "ativo fixo" (e sua baixa);
 * 3- que contenham os registros consolidados relativos aos contribuintes com
 * atividades econômicas de fornecimento de energia elétrica, de fornecimento de
 * água canalizada, de fornecimento de gás canalizado, e de prestação de serviço de
 * comunicação e telecomunicação que poderão, a critério do Fisco,
 * utilizar registros consolidados por classe de consumo para representar suas
 * saídas ou prestações.
 *
 * NOTA: usada a letra Z no nome da Classe pois os nomes não podem ser exclusivamente
 * numeréricos e também para não confundir os com elementos do bloco B
 */
class Z0200 extends Element
{
    const REG = '0200';
    const LEVEL = 2;
    const PARENT = '';

    protected $parameters = [
        'COD_ITEM' => [
            'type'     => 'string',
            'regex'    => '^.{1,60}$',
            'required' => true,
            'info'     => 'Código do item',
            'format'   => ''
        ],
        'DESCR_ITEM' => [
            'type'     => 'string',
            'regex'    => '^.{3,255}$',
            'required' => true,
            'info'     => 'Descrição do item',
            'format'   => ''
        ],
        'COD_BARRA' => [
            'type'     => 'string',
            'regex'    => '^(SEM GTIN)|([0-9]{8,14}|\-)$',
            'required' => false,
            'info'     => 'Representação alfanumérico do código de barra do produto, se houver',
            'format'   => ''
        ],
        'COD_ANT_ITEM' => [
            'type'     => 'string',
            'regex'    => '^.{1,6}$',
            'required' => false,
            'info'     => 'Código anterior do item com relação à última informação anterior',
            'format'   => ''
        ],
        'UNID_INV' => [
            'type'     => 'string',
            'regex'    => '^.{1,6}$',
            'required' => true,
            'info'     => 'Unidade de medida utilizada na quantificação de estoques.',
            'format'   => ''
        ],
        'TIPO_ITEM' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{2}$',
            'required' => true,
            'info'     => 'Tipo do item – Atividades Industriais, Comerciais e Serviços:'
            . '00 – Mercadoria para Revenda;'
            . '01 – Matéria-prima;'
            . '02 – Embalagem;'
            . '03 – Produto em Processo;'
            . '04 – Produto Acabado;'
            . '05 – Subproduto;'
            . '06 – Produto Intermediário;'
            . '07 – Material de Uso e Consumo;'
            . '08 – Ativo Imobilizado;'
            . '09 – Serviços;'
            . '10 – Outros insumos;'
            . '99 – Outras',
            'format'   => ''
        ],
        'COD_NCM' => [
            'type'     => 'string',
            'regex'    => '^([0-9]{8})|([0-9]{2})$',
            'required' => false,
            'info'     => 'Código da Nomenclatura Comum do Mercosul',
            'format'   => ''
        ],
        'EX_IPI' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{1,3}$',
            'required' => false,
            'info'     => 'Código EX, conforme a TIPI',
            'format'   => ''
        ],
        'COD_GEN' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{2}$',
            'required' => false,
            'info'     => 'Código do gênero do item, conforme a Tabela 4.2.1',
            'format'   => ''
        ],
        'COD_LST' => [
            'type'     => 'string',
            'regex'    => '^([0-9]{2}\.[0-9]{2})$',
            'required' => false,
            'info'     => 'Código do serviço conforme lista do Anexo I da '
            . 'Lei Complementar Federal nº 116/03.',
            'format'   => ''
        ],
        'ALIQ_ICMS' => [
            'type'     => 'numeric',
            'regex'    => '^(\d*\.)?\d+$',
            'required' => false,
            'info'     => 'Alíquota de ICMS aplicável ao item nas operações internas',
            'format'   => '6v2'
        ],
        'CEST' => [
            'type'     => 'string',
            'regex'    => '^[0-9]{7}$',
            'required' => false,
            'info'     => 'Código Especificador da Substituição Tributária',
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
