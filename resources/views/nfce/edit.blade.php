@extends('layouts.app', ['title' => 'Editar NFCe'])
@section('content')
<div style="text-align: right;" class="">
    <a href="{{ route('nfce.index') }}" class="btn btn-danger btn-sm px-3"><i class="fa fa-arrow-left"></i>Voltar</a>
</div>
<div class="card mt-1">
    <div class="card-header">
        <h4>Editar NFCe</h4>
    </div>
    <div class="card-body">
        {!!Form::open()->fill($item)
        ->put()
        ->route('nfce.update', [$item->id])
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('nfce._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@section('js')
<script src="/js/nfce.js"></script>
@endsection
@endsection
