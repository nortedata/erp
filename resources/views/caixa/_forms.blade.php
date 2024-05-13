@if($item != null)
<div class="row">
    <h4>Caixa Aberto no Momento!</h4>
</div>
@else
<div class="row g-2">
    <div class="col-md-2">
        {!!Form::text('valor_abertura', 'Valor de abertura')->attrs(['class' => 'moeda'])->required()
        !!}
    </div>
    <div class="col-md-8">
        {!!Form::text('observacao', 'Observação')->attrs(['class' => ''])
        !!}
    </div>
    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>
@endif