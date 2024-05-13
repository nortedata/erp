@extends('food.default', ['title' => 'Carrinho'])
@section('content')

@section('css')
<style type="text/css">
	.btn-main{
		border: none;
	}

	@media (max-width:480px)  {
		th{
			font-size: 13px !important;
		}
		td{
			font-size: 14px !important;
		}

	}
	.img-prod{
		height: 60px;
		border-radius: 5px;
	}
</style>
@endsection

<section class="shoping-cart">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="shoping__cart__table" style="margin-top: -60px">
					<table>
						<thead>
							<tr>
								<th class="shoping__product">Produto</th>
								<th>Valor unitário</th>
								<th>Quantidade</th>
								<th>Subtotal</th>
								<th></th>
							</tr>
						</thead>
						<tbody>

							@foreach($carrinho->itens as $i)
							<tr>
								<td class="shoping__cart__item">
									<img class="img-prod" src="{{ $i->produto->img }}">
									@if($i->tamanho_id == null)
									<h5>{{ $i->produto->nome }}</h5>
									@else
									@foreach($i->sabores as $s)
									<h5>1/{{ sizeof($i->sabores) }} {{ $s->sabor->nome }} @if(!$loop->last) | @endif</h5>
									@endforeach
									@endif
								</td>
								<td class="shoping__cart__price">
									R$ {{ __moeda($i->valor_unitario) }}
								</td>
								<form id="form-cart-{{$i->id}}" action="{{ route('food.carrinho-update') }}">
									<input type="hidden" value="{{ $config->loja_id }}" name="link">
									<input type="hidden" name="item_id" value="{{ $i->id }}">
									<td class="shoping__cart__quantity">
										<div class="quantity" data-item="{{$i->id}}">
											<div class="pro-qty">
												<input name="quantidade" type="text" value="{{ number_format($i->quantidade, 0) }}">
											</div>
										</div>
									</td>
								</form>

								<td class="shoping__cart__total">
									R$ {{ __moeda($i->sub_total) }}
								</td>
								<td class="shoping__cart__item__close">
									<form action="{{ route('food.remove-item', [$i->id, 'link='.$config->loja_id]) }}" method="post" id="form-{{$i->id}}">
										@csrf
										@method('delete')
										<button type="button" class="btn btn-danger btn-sm btn-delete" title="Remover Item">
											<span class="icon_close text-white"></span>
										</button>
									</form>
								</td>

							</tr>
							@if(sizeof($i->adicionais) > 0 || $i->observacao != null || $i->tamanho_id != null)

							<tr style="background: #EFEFEF;">
								<td colspan="5">
									@if(sizeof($i->adicionais) > 0)
									Adicionais: <b>@foreach($i->adicionais as $a) {{ $a->adicional->nome }}@if(!$loop->last), @endif @endforeach</b>
									@endif

									@if($i->observacao != null)
									<span class="ml-2">Observação: <strong>{{ $i->observacao }}</strong></span>
									@endif

									@if($i->tamanho_id != null)
									<span class="ml-2">Tamanho: <strong>{{ $i->tamanho->nome }}</strong></span>
									@endif

								</td>
							</tr>
							@endif
							@endforeach

						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="shoping__cart__btns">
					<a href="{{ route('food.index', ['link='.$config->loja_id]) }}" class="primary-btn cart-btn">CONTINUAR COMPRANDO</a>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="shoping__continue">
					<div class="shoping__discount">
						<h5>Cupom de desconto</h5>
						<input type="hidden" id="inp-empresa_id" value="{{ $config->empresa_id }}">
						<input type="hidden" id="inp-total" value="{{ $carrinho->valor_total }}">
						<input type="hidden" id="inp-carrinho_id" value="{{ $carrinho->id }}">
						<input type="hidden" id="inp-cliente_uid" value="{{ $clienteLogado }}">
						<form action="#">
							<input type="text" id="inp-cupom" value="{{ $carrinho->cupom }}" placeholder="Informe seu cupom" data-mask="AAAAAA">
							<button type="button" class="site-btn btn-cupom">APLICAR DESCONTO</button>
						</form>
					</div>
				</div>
			</div>
			<form class="col-lg-6" method="get" action="{{ route('food.pagamento') }}">
				<input type="hidden" name="link" value="{{ $config->loja_id }}">
				@if($funcionamento && $funcionamento->aberto)

				<div class="shoping__checkout">
					<h5>Total do carrinho</h5>
					<ul>
						<li>Subtotal <span class="text-main">R$ {{ __moeda($carrinho->itens->sum('sub_total')) }}</span></li>
						<li>Desconto <span class="vl-desconto">R$ {{ __moeda($carrinho->valor_desconto) }}</span></li>
						<li>Total <span class="text-main total-cart">R$ {{ __moeda($carrinho->valor_total - $carrinho->valor_frete) }}</span></li>
					</ul>

					<button type="submit" class="primary-btn btn-main w-100">IR PAGA PAGAMENTO</button>
				</div>
				@else
				<div class="card bg-danger">
					<div class="card-header" style="height: 50px;">
						<p class="text-white">Restaurante fechado</p>
					</div>
				</div>
				@endif
			</form>
			
		</div>
	</div>
</section>

@endsection

@section('js')
<script type="text/javascript" src="/delivery/js/cart.js"></script>
@endsection