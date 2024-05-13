@section('css')
<style>
    .active {
        background: rgb(85, 114, 245) !important;
        color: #fff !important;
    }

    #salvar_venda:hover {
        cursor: pointer;
    }

</style>
@endsection

<input type="hidden" id="abertura" value="{{ $abertura }}" name="">
<input type="hidden" id="lista_id" value="" name="lista_id">
@isset($pedido)
@isset($isDelivery)
<input name="pedido_delivery_id" id="pedido_delivery_id" value="{{ $pedido->id }}" class="d-none">
<input id="pedido_desconto" value="{{ $pedido->desconto }}" class="d-none">
<input id="pedido_valor_entrega" value="{{ $pedido->valor_entrega }}" class="d-none">
@else
<input name="pedido_id" id="pedido_id" value="{{ $pedido->id }}" class="d-none">
@endif
@endif

<div class="row">
    <div class="col-lg-4">
        <div class="row">
            <div class="col-lg-6">
                <div class="card widget-icon-box">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="flex-grow-1 overflow-hidden">
                                <h5 class="text-muted text-uppercase fs-13 mt-0" title="Number of Customers">Cliente</h5>
                                <h6 class="cliente_selecionado">--</h6>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <button type="button" class="avatar-title text-bg-success rounded rounded-3 fs-3 widget-icon-box-avatar shadow btn-selecionar_cliente" data-bs-toggle="modal" data-bs-target="#cliente">
                                    <i class="ri-group-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card widget-icon-box">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="flex-grow-1 overflow-hidden">
                                <h5 class="text-muted text-uppercase fs-13 mt-0" title="Conversation Ration">Funcionário</h5>
                                <h6 class="funcionario_selecionado">--</h6>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <button type="button" class="avatar-title text-bg-warning rounded rounded-3 fs-3 widget-icon-box-avatar" data-bs-toggle="modal" data-bs-target="#funcionario">
                                    <i class=" ri-user-2-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card" style="height: 750px">
            <div class="col-11" style="margin-left: 24px">
                {{-- {!!Form::select('produto_id', '')->attrs(['class' => 'select2'])
                !!} --}}
            </div>
            <hr>
            <h5 class="text-center">Categorias</h5>
            <div class="card categorias m-1" data-simplebar data-simplebar-lg style="max-height: 110px;">
                <div class="d-flex gap-1 m-2">
                    <button type="button" id="cat_todos" onclick="todos()" class="btn btn-light rounded-pill m-1">Todos</button>
                    @foreach ($categorias as $cat)
                    <button type="button" class="btn btn-light rounded-pill m-1 btn_cat btn_cat_{{ $cat->id }}" onclick="selectCat('{{ $cat->id }}')">{{$cat->nome}}</button>
                    @endforeach
                </div>
            </div>
            <h4 class="text-center mt-3">Produtos</h4>
            <div class="card-body lista_produtos m-1" data-simplebar data-simplebar-lg style="max-height: 522px;">
                <div class="row cards-categorias">

                </div>
            </div>
            <div class="row">
                <div class="col-1 text-center">
                    <input class="mousetrap" type="" autofocus style="border: none; width: 10px; height: 10px; background-color:black" id="codBarras" name="">
                </div>
                <div class="col-10 leitor_ativado text-info">
                    Leitor Ativado
                </div>
                <div class="col-10 leitor_desativado d-none">
                    Leitor Desativado
                </div>
            </div>

        </div>
    </div>
    <div class="col-lg-8 produtos">
        <div class="card" style="height: 850px">
            <div class="row m-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="inp-produto_id" class="">Produto</label>
                        <div class="input-group">
                            <select class="form-control produto_id" name="produto_id" id="inp-produto_id"></select>
                        </div>
                        <input name="variacao_id" id="inp-variacao_id" type="hidden" value="">

                    </div>
                </div>
                <div class="col-md-2">
                    {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::tel('valor_unitario', 'Valor Unitário')->attrs(['class' => 'moeda value_unit']) !!}
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <div class="col-12">
                            <br>
                            <button class="btn btn-primary btn-add-item w-100" type="button" style="margin-left: 0px">Adicionar</button>
                        </div>

                    </div>
                </div>
                <div class="col-md-1">
                    {!! Form::hidden('subtotal', 'SubTotal')->attrs(['class' => 'moeda']) !!}
                    {!! Form::hidden('valor_total', 'valor Total')->attrs(['class' => 'moeda']) !!}
                </div>
            </div>
            <div class="card m-1">
                <div data-bs-target="#navbar-example2" class="scrollspy-example" style="height: 440px">
                    <table class="table table-striped dt-responsive nowrap table-itens">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor</th>
                                <th>Subtotal</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($itens))
                            @foreach ($itens as $key => $product)
                            <tr>
                                <input readonly type="hidden" name="key" class="form-control" value="{{ $product->key }}">
                                <input readonly type="hidden" name="produto_id[]" class="form-control" value="{{ $product->produto->id }}">
                                <input name="variacao_id[]" type="hidden" value="{{ $product->variacao_id }}">

                                <td>
                                    {{ $product->img }}
                                    <img src="{{ $product->produto->img }}" style="width: 30px; height: 40px; border-radius: 10px;">
                                </td>
                                <td>
                                    <input readonly type="text" name="produto_nome[]" class="form-control" value="{{ $product->produto->nome }}">
                                </td>
                                <td>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <button id="btn-subtrai" class="btn btn-danger" type="button">-</button>
                                        </div>
                                        <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ number_format($product->quantidade,0) }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-success" id="btn-incrementa" type="button">+</button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input readonly type="tel" name="valor_unitario[]" class="form-control" value="{{ __moeda($product->valor_unitario) }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($product->valor_unitario * $product->quantidade) }}">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-row"><i class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="">
            <h4 class="text-center">Finalização da Venda</h4>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="card widget-icon-box div-pagamento">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="text-muted text-uppercase fs-13 mt-0" title="Number of Customers">Desconto</h5>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <button type="button" onclick="setaDesconto()" class="avatar-title text-bg-primary rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                        <i class="ri-checkbox-indeterminate-line"></i>
                                    </button>
                                </div>
                            </div>
                            <h3 id="valor_desconto">R$ 0,00</h3>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col-lg-3 col-6">
                    <div class="card widget-icon-box div-pagamento">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="text-muted text-uppercase fs-13 mt-0" title="Number of Customers">Acréscimo</h5>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <button type="button" onclick="setaAcrescimo()" class="avatar-title text-bg-danger rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                        <i class="ri-add-box-line"></i>
                                    </button>
                                </div>
                            </div>
                            <h3 id="valor_acrescimo">R$ 0,00</h3>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->

                <div class="col-lg-3 col-6">
                    <div class="card widget-icon-box div-pagamento">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="row">
                                        <h5 class="text-center">Suprimento</h5>
                                    </div>
                                    <div class="avatar-sm m-1">
                                        <button type="button" style="margin-left: 35px" data-bs-toggle="modal" data-bs-target="#suprimento_caixa" class="avatar-title text-bg-info rounded rounded-3 fs-3 widget-icon-box-avatar">
                                            <i class="ri-add-box-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="row">
                                        <h5 class="text-center">Sangria</h5>
                                    </div>
                                    <div class="avatar-sm m-1">
                                        <button type="button" style="margin-left: 35px" data-bs-toggle="modal" data-bs-target="#sangria_caixa" class="avatar-title text-bg-warning rounded rounded-3 fs-3 widget-icon-box-avatar">
                                            <i class="ri-checkbox-indeterminate-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->

                <div class="col-lg-3 col-6">
                    <div class="card widget-icon-box div-pagamento">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="text-muted text-uppercase fs-13 mt-0" title="Number of Customers">TOTAL</h5>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title text-bg-dark rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                        <i class="ri-shopping-cart-fill"></i>
                                    </span>
                                </div>
                            </div>
                            <h3 class="">
                                @isset($item)
                                <strong class="total-venda">{{ __moeda($item->valor_total) }}</strong>
                                @else
                                <strong class="total-venda">0,00</strong>
                                @endif
                            </h3>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="card widget-icon-box div-pagamento" style="height: 93%">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="text-muted text-uppercase fs-13 mt-0" title="Number of Orders">Tipo de Pagamento</h5>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title text-bg-success rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                        <i class=" ri-money-dollar-circle-line"></i>
                                    </span>
                                </div>
                            </div>
                            {{-- @isset($item) --}}
                            {{-- {!! Form::select('tipo_pagamento', '', ['' => 'Selecione'] + App\Models\Nfce::tiposPagamento())->attrs(['class' => 'form-select'])->options(isset($item) ? $item->getTipoPagamento($item->tipo_pagamento) : '') !!} --}}
                            {{-- @else --}}
                            {{-- {{$item}} --}}
                            {!! Form::select('tipo_pagamento', '', ['' => 'Selecione'] + App\Models\Nfce::tiposPagamento())->attrs(['class' => 'form-select'])->value(isset($item) ? $item->tipo_pagamento : '') !!}
                            {{-- @endisset --}}
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col-lg-3 col-6 div-troco d-none">
                    <div class="card div-pagamento" style="height: 93%">
                        <div class="row m-2">
                            <div class="col-lg-5 mt-4">
                                <h5>Valor Recebido</h5>
                            </div>
                            <div class="col-lg-7">
                                {!! Form::tel('valor_recebido', '')->attrs(['class' => 'moeda']) !!}
                            </div>
                        </div>
                        <div class="row m-1">
                            <div class="card text-bg-danger">
                                <h3 class="m-1">TROCO = <strong class="" id="valor-troco"></strong></h3>
                                <input type="hidden" name="troco" id="inp-troco">
                            </div>
                        </div>
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col-lg-2 col-6 div-vencimento d-none">
                    <div class="card div-pagamento" style="height: 93%">
                        <div class="row m-2">
                            <div class="text-center">
                                <h5>Data dde vencimento</h5>
                            </div>
                            <div>
                                {!! Form::date('data_vencimento', '')->attrs(['class' => 'data_atual']) !!}
                            </div>
                        </div>
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col">
                    <div class="card widget-icon-box div-pagamento" style="height: 93%">
                        <div class="card-body">
                            <div class="d-f mt-3">
                                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#pagamento_multiplo"><i class="ri-list-check-3"></i> Pagamento múltiplo</button><br>
                                <button type="button" class="btn btn-dark w-100 mt-1" data-bs-toggle="modal" data-bs-target="#lista_precos"><i class="ri-cash-line"></i> Lista de preços</button>
                                <!-- <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="text-muted text-uppercase fs-13 mt-0" title="Average Revenue">Pagamento Multiplo</h5>

                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <button type="button" class="avatar-title text-bg-warning rounded rounded-3 fs-3 widget-icon-box-avatar shadow pagamento_multiplo" data-bs-toggle="modal" data-bs-target="#pagamento_multiplo">
                                        <i class=" ri-list-check-3"></i>
                                    </button>
                                </div> -->
                            </div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col">
                    <div class="card widget-icon-box div-pagamento" style="height: 93%">
                        <div class="card-body">
                            <div class="mt-2">

                                <a class="btn btn-danger bbtn-sm" href="{{ route('frontbox.index')}}" style="margin-top: -20px">
                                    <i class="ri-arrow-left-s-line"></i>
                                    sair do PDV
                                </a><br><br>
                                <button type="button" class="btn btn-success w-100" disabled id="salvar_venda" data-bs-toggle="modal" data-bs-target="#finalizar_venda">
                                    <i class="ri-checkbox-line"></i>
                                    Finalizar Venda
                                </button>
                            </div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div>
            {{-- <div class="row">
                <div class="col-sm-6 col-lg-3">
                    {!! Form::select('forma_pagamento', 'Forma de Pagamento')->attrs(['class' => 'form-select']) !!}
                </div>
                <div class="col-sm-6 col-lg-3">
                    {!! Form::select('tipo_pagamento', 'Tipo de Pagamento')->attrs(['class' => 'form-select']) !!}
                </div>
            </div> --}}

        </div>
    </div>
</div>
</div>

@include('modals._pagamento_multiplo', ['not_submit' => true])
@include('modals._finalizar_venda', ['not_submit' => true])
@include('modals._funcionario', ['not_submit' => true])
@include('modals._cartao_credito', ['not_submit' => true])
@include('modals._variacao', ['not_submit' => true])
@include('modals._lista_precos')
@include('modals._cliente', ['cashback' => 1])

@section('js')
<script src="/js/frente_caixa.js" type=""></script>
<script type="text/javascript" src="/js/mousetrap.js"></script>

@endsection
