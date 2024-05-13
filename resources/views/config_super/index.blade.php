@extends('layouts.app', ['title' => 'Configurações'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <h4>Configuração Super</h4>
                <hr>
                <div class="row mt-3">
                    <h5>Responsável Técnico</h5>
                    <div class="col-lg-12">
                        {!!Form::open()->fill($item)
                        ->post()
                        ->route('configuracao-super.store')
                        ->multipart()
                        !!}
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                {!!Form::text('name', 'Nome')
                                ->required()
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('cpf_cnpj', 'CNPJ')
                                ->required()
                                ->attrs(['class' => 'cpf_cnpj'])
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('telefone', 'Telefone')
                                ->attrs(['class' => 'fone'])
                                ->required()
                                !!}
                            </div>
                            <div class="col-md-4">
                                {!!Form::text('email', 'E-mail')
                                ->required()
                                !!}
                            </div>
                            <hr class="mt-4">
                            <h5 class="text-success">Para recebimento dos planos</h5>
                            <div class="col-md-6">
                                {!!Form::text('mercadopago_public_key', 'Mercado Pago Public Key')
                                !!}
                            </div>
                            <div class="col-md-6">
                                {!!Form::text('mercadopago_access_token', 'Mercado Pago Access Token')
                                !!}
                            </div>

                            <hr class="mt-4">
                            <div class="col-md-6">
                                <h5 class="text-success">Para ativar conta do cliente MarketPlace</h5>

                                {!!Form::text('sms_key', 'SMS key comtele')
                                !!}
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-success">Para enviar mensagens de alteração do pedido delivery https://criarwhats.com</h5>

                                {!!Form::text('token_whatsapp', 'Token WhatsApp')
                                !!}
                            </div>

                            <hr class="mt-4">

                            <h5 class="text-success">Para cálculo dos correios</h5>
                            <div class="col-md-3">
                                {!!Form::text('usuario_correios', 'Usuário')
                                !!}
                            </div>
                            <div class="col-md-5">
                                {!!Form::text('codigo_acesso_correios', 'Código de accesso')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::text('cartao_postagem_correios', 'Cartão postagem')
                                !!}
                            </div>

                            <hr class="mt-4">

                            <h5 class="text-success">NFSe</h5>
                            <div class="col-md-10">
                                {!!Form::text('token_auth_nfse', 'Token integra notas')
                                !!}
                            </div>

                            <hr class="mt-4">

                            <h5 class="text-success">Emissão de documentos</h5>
                            <div class="col-md-2">
                                {!!Form::tel('timeout_nfe', 'Tempo de espera NFe')
                                ->attrs(['data-mask' => '00'])
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('timeout_nfce', 'Tempo de espera NFCe')
                                ->attrs(['data-mask' => '00'])
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('timeout_cte', 'Tempo de espera CTe')
                                ->attrs(['data-mask' => '00'])
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('timeout_mdfe', 'Tempo de espera MDFe')
                                ->attrs(['data-mask' => '00'])
                                !!}
                            </div>
                            <hr class="mt-4">
                            <div class="col-12" style="text-align: right;">
                                <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
                            </div>
                        </div>
                        {!!Form::close()!!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
