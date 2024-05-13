@extends('layouts.app', ['title' => 'Planos'])
@section('css')
<style type="text/css">
    .card-img-top{
        height: 240px;
    }
</style>
@endsection

@section('content')
<div class="card m-1">
    <div class="row m-3">
        @if($config != null && $config->mercadopago_public_key != "")
        @foreach($planos as $item)
        <div class="col-lg-3 col-12">
            <div class="card" style="height: 29rem;">
                <div class="m-1">
                    <img class="card-img-top" src="{{ $item->img }}" alt="Card image cap">
                </div>
                <div class="card-body">
                    <h4>{{ $item->nome }}</h4>
                    <p class="card-text">
                        {{ $item->descricao }}
                    </p>
                    <h6 class="text-danger">R$ {{ __moeda($item->valor) }}</h6>
                    <h6 class="text-primary">{{ $item->intervalo_dias }} dias</h6>
                    
                </div>
                <div class="card-footer">
                    <button onclick="selectPlano('{{$item->id}}', '{{$item->valor}}', '{{$item->nome}}')" class="btn btn-success btn-pay w-100" data-bs-toggle="modal" data-bs-target="#modal-pay">
                        <i class="fa fa-cart"></i> contratar
                    </button>
                </div>
                
            </div>
        </div>
        @endforeach
        @else
        <h5 class="text-center">Defina as configurações de pagamento!</h5>
        @endif
    </div>
</div>

<div class="modal fade" id="modal-pay" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form class="modal-content" method="post" action="{{ route('payment.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Contratar plano <strong class="plano_nome"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <p class="text-danger">* Preencha o formulário abaixo com seus dados para gerar o QrCode.</p>
                    <input type="hidden" name="plano_id" id="plano_id">

                    <div class="col-lg-3 col-6">
                        {!!Form::text('nome', 'Nome')
                        ->required()
                        !!}
                    </div>
                    <div class="col-lg-3 col-6">
                        {!!Form::text('sobre_nome', 'Sobre Nome')
                        ->required()
                        !!}
                    </div>

                    <div class="col-lg-4 col-6">
                        {!!Form::text('email', 'Email')
                        ->required()
                        !!}
                    </div>

                    <div class="col-lg-2 col-6">
                        {!!Form::select('docType', 'Tipo documento')
                        ->required()
                        ->id('docType')
                        ->attrs(['data-checkout' => 'docType', 'class' => 'form-select'])
                        !!}
                    </div>

                    <div class="col-lg-3 col-6 mt-2">
                        {!!Form::tel('docNumber', 'Número documento')
                        ->required()
                        ->attrs(['class' => 'cpf_cnpj'])
                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-success btn-gerar">Gerar QrCode</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
<script type="text/javascript">

    $(function(){
        window.Mercadopago.setPublishableKey('{{ $config->mercadopago_public_key }}');
        setTimeout(() => {
            window.Mercadopago.getIdentificationTypes();
        }, 100)
    })
    function selectPlano(id, valor, nome){

        $('.plano_nome').text(nome + " R$ " + valor.replace(".", ","))
        $('#plano_id').val(id)
    }

    $('.btn-gerar').click(() => {
        $body.addClass("loading");
    })

</script>

@endsection
