<div class="row g-2">
    <div class="col-md-3">
        {!!Form::text('nome', 'Nome')->required()
        !!}
    </div>
    @if(__isInternacionalizar(Auth::user()->empresa))
    <div class="col-md-3">
        {!!Form::text('nome_en', 'Nome (em inglês)')
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('nome_es', 'Nome (em espanhol)')
        !!}
    </div>
    @endif

    <div class="col-md-2">
        {!!Form::select('cardapio', 'Cardápio', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select tooltipp'])
        !!}
        <div class="text-tooltip d-none">
            Marcar como sim se for usar esta categoria no cardápio
        </div>
    </div>

    <div class="col-md-2">
        {!!Form::select('delivery', 'Delivery', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select tooltipp2'])
        !!}
        <div class="text-tooltip2 d-none">
            Marcar como sim se for usar esta categoria no Delivery/Marketplace
        </div>
    </div>

    <div class="col-md-2">
        {!!Form::select('tipo_pizza', 'Tipo pizza', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('ecommerce', 'Ecommerce', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select tooltipp3'])
        !!}
        <div class="text-tooltip3 d-none">
            Marcar como sim se for usar esta categoria no Ecommerce
        </div>
    </div>
    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>