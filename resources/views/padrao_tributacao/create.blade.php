@extends('layouts.app', ['title' => 'Novo Padrão'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo Padrão</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('produtopadrao-tributacao.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('produtopadrao-tributacao.store')
        !!}
        <div class="pl-lg-4">
            @include('padrao_tributacao._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection
