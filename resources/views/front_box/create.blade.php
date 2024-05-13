@extends('front_box.default', 
['title' => isset($pedido) ? isset($isDelivery) ? ('Finalizando Pedido Delivery ' . $pedido->id) : ('Finalizando Comanda ' . $pedido->comanda) : 'Nova Venda - PDV'])
@section('content')

{!!Form::open()
->post()
->route('frontbox.store')->id('form-pdv')
->multipart()
!!}
<div class="pl-lg-4">
    @include('front_box._forms')
</div>
{!!Form::close()!!}

@endsection

{{-- @section('js')
<script type="text/javascript" src="/js/frente_caixa.js"></script>
@endsection --}}


