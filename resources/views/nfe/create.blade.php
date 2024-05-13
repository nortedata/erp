@extends('layouts.app', ['title' => isset($isCompra) ? 'Nova Compra' : 'Nova NFe'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        @isset($isCompra)
        <h4>Nova Compra</h4>
        @else
        <h4>Nova NFe</h4>
        @endif
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ !isset($isCompra) ? route('nfe.index') : route('compras.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('nfe.store')
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('nfe._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@section('js')

<script type="text/javascript"> 
    $(".tipo_pagamento").change(() => {
        let tipo = $(".tipo_pagamento").val();
        if (tipo == "03" || tipo == "04") {
            $('#cartao_credito').modal('show')
        }
    })
</script>

<script src="/js/nfe.js"></script>
@endsection
@endsection
