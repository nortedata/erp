@extends('layouts.app', ['title' => 'Galeria do Produto'])
@section('css')
<style type="text/css">
    
</style>
@endsection
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Galeria <strong>{{ $item->nome }}</strong></h4>


        <div class="card">
            
        </div>
    </div>
</div>
@endsection