<?php

namespace NFePHP\EFD\Elements\ICMSIPI;

use NFePHP\EFD\Common\Element;
use stdClass;

/**
 * REGISTRO D001: ABERTURA DO BLOCO D
 * Este registro deve ser gerado para abertura do bloco D e indica se há
 * informações sobre prestações ou contratações
 * de serviços de comunicação, transporte interestadual e intermunicipal,
 * com o devido suporte do correspondente documentofiscal.
 * Validação do Registro: registro obrigatório e único. Se o campo IND_MOV tiver valor igual a 1 (um), só devem
 * ser informados este registro de abertura e o registro D990, que é o registro de fechamento do Bloco D.
 */
class D001 extends Element
{
    const REG = 'D001';
    const LEVEL = 1;
    const PARENT = '';

    protected $parameters = [
        'IND_MOV' => [
            'type'     => 'numeric',
            'regex'    => '^[0-1]{1}$',
            'required' => true,
            'info'     => 'Indicador de movimento: '
                . '0- Bloco com dados informados; '
                . '1- Bloco sem dados informados',
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
