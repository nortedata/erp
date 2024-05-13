@extends('layouts.app', ['title' => 'Importação de XML'])
@section('content')

<div class="card mt-1">
    <div class="card-header">

        <h4>Importação de XML</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('compras.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('compras.finish-xml')
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('compras._forms_xml')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@section('js')
<script src="/js/nfe.js"></script>
@endsection
@endsection
