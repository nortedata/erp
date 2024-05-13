<div class="row g-2">
    <div class="col-md-3">
        {!!Form::text('nome', 'Nome')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('ajuste_sobre', 'Ajuste sobre', ['' => 'Selecione', 'valor_compra' => 'Valor de compra', 'valor_venda' => 'Valor de venda'])
        ->required()->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('tipo', 'Tipo', ['' => 'Selecione', 'incremento' => 'Incremento', 'reducao' => 'Redução'])
        ->required()->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('percentual_alteracao', '% de alteração')
        ->required()->attrs(['class' => 'percentual'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('tipo_pagamento', 'Tipo de pagamento', ['' => 'Selecione'] + App\Models\ListaPreco::tiposPagamento())->attrs(['class' => 'form-select'])
        !!}
    </div>
    
    <div class="col-md-3">
        {!!Form::select('funcionario_id', 'Funcionário')
        ->options((isset($item) && $item->funcionario) ? [$item->funcionario_id => $item->funcionario->nome] : [])
        !!}
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>