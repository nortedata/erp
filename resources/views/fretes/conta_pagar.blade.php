@extends('layouts.app', ['title' => 'Nova Conta Pagar'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Nova Conta Pagar <strong class="text-muted">Despesa de Frete #{{ $item->frete->numero_sequencial }}</strong></h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('fretes.show', [$item->id]) }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('conta-pagar.store')
        ->multipart()
        !!}

        <input type="hidden" name="redirect" value="{{ route('fretes.show', [$item->frete->id]) }}">
        <input type="hidden" name="despesa_id" value="{{ $item->id }}">
        <div class="pl-lg-4">
            @include('conta-pagar._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>

@include('modals._novo_cliente')

@endsection
