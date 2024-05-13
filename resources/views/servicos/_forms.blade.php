<div class="row g-2">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')->attrs(['class' => ''])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])->required()
        ->value(isset($item) ? __moeda($item->valor) : '')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('tempo_servico', 'Tempo de execução (minutos)')->attrs(['data-mask' => '00'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('comissao', 'Comissão (opcional)')->attrs(['class' => 'moeda'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('unidade_cobranca', 'Unidade cobrança', ['UND' => 'UND', 'HORAS' => 'HORAS', 'MIN' => 'MIN'])->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('categoria_id', 'Categoria', ['' => 'Selecione uma categoria'] + $categorias->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('tempo_adicional', 'Tempo adicional')
        ->attrs(['data-mask' => '00'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_adicional', 'Valor adicional')->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_adicional) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('tempo_tolerancia', 'Tempo de tolerância')
        ->attrs(['data-mask' => '00'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('codigo_servico', 'Código do serviço')->attrs(['class' => ''])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::tel('codigo_tributacao_municipio', 'Código do trtibutação municipal')->attrs(['class' => ''])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('status', 'Ativo', ['1' => 'Sim', '0' => 'Não'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="card mt-4">

        <div class="card-body row">
            <h4 class="">Tributação</h4>

            <div class="col-md-2">
                {!!Form::tel('aliquota_iss', '% ISS')->attrs(['class' => 'percentual'])
                !!}
            </div>
            <div class="col-md-2">
                {!!Form::tel('aliquota_pis', '% PIS')->attrs(['class' => 'percentual'])
                !!}
            </div>
            <div class="col-md-2">
                {!!Form::tel('aliquota_cofins', '% COFINS')->attrs(['class' => 'percentual'])
                !!}
            </div>
            <div class="col-md-2">
                {!!Form::tel('aliquota_inss', '% INSS')->attrs(['class' => 'percentual'])
                !!}
            </div>
        </div>
    </div>

    <hr class="">
    {{-- Imagem --}}
    <div class="card col-md-3 form-input">
        <div class="preview">
            <button type="button" id="btn-remove-imagem" class="btn btn-link-danger btn-sm btn-danger">x</button>
            @isset($item)
            <img id="file-ip-1-preview" src="{{ $item->img }}">
            @else
            <img id="file-ip-1-preview" src="/imgs/no-image.png">
            @endif
        </div>
        <label for="file-ip-1">Imagem</label>
        <input type="file" id="file-ip-1" name="image" accept="image/*" onchange="showPreview(event);">
    </div>
    @if($errors->has('image'))
    <div class="text-danger mt-2">
        {{ $errors->first('image') }}
    </div>
    @endif
    <hr class="mt-2">
    {{-- Fim Imagem --}}
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/uploadImagem.js">

</script>
@endsection
