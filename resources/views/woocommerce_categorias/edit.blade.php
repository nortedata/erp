@extends('layouts.app', ['title' => 'Editar Categoria Woocommerce'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Editar Categoria Woocommerce</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('woocommerce-categorias.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->put()
        ->route('woocommerce-categorias.update', [$item->id])
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('woocommerce_categorias._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection
