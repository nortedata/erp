@extends('food.default', ['title' => 'Minha conta'])
@section('content')

@section('css')
<style type="text/css">
	.btn-main{
		border: none;
	}
	.borders{
		padding: 10px;
		border: 1px solid #999;
		border-radius: 5px;
	}
	.borders:hover{
		cursor: pointer;
	}
	.b-2:hover{
		cursor: pointer;
	}
	.bg-main h5{
		color: #fff;
	}
	.b-2{
		margin-top: 5px;
		padding: 5px;
		border: 1px solid #999;
		border-radius: 4px;
	}
	.active-div{
		background: var(--color-main);
		color: #fff;
	}

	.active-btn{
		background: #27BCC2!important;
		color: #fff!important;
		border: none;
	}
	.select-card:hover{
		cursor: pointer;
	}
	img.image{
		height: 50px;
		border-radius: 10px;
	}
</style>
@endsection

<section class="shoping-cart spad">
	<div class="container">
		<div class="row mb-2">
			<div class="col-6">
				<h3>{{ $cliente->razao_social }}</h3>

			</div>
			<div class="col-6 text-right">
				<h3>{{ $cliente->telefone }}</h3>
				<a href="{{ route('food.logoff', ['link='.$config->loja_id]) }}" class="btn btn-sm btn-danger text-white">sair</a>
			</div>
		</div>

		<h4>Endereços</h4>
		@foreach($cliente->enderecos as $e)
		<div class="col-md-1"></div>
		<div class="col-12 col-md-12 borders mt-1" onclick="editEndereco('{{ json_encode($e) }}')">
			<h5>{{ $e->info }}</h5>
			@if($e->padrao)
			<p>endereço padrão</p>
			@endif
			<button class="btn btn-sm btn-warning text-white mt-1">
				<i class="fa fa-edit"></i> Editar
			</button>
		</div>
		@endforeach
		<br>
		<h4>Pedidos</h4>
		@foreach($cliente->pedidos as $p)
		<div class="card mt-2">
			<div class="card-header">
				<div class="row">
					<div class="col-md-4 col-6">
						#{{ $p->id }} - 
						@if($p->estado == 'novo')
						<span class="text-primary">Novo</span>
						@elseif($p->estado == 'aprovado')
						<span class="text-success">Aprovado</span>
						@elseif($p->estado == 'cancelado')
						<span class="text-danger">Cancelado</span>
						@else
						<span class="text-main">Finalizado</span>
						@endif
					</div>
					<div class="col-md-4 col-6 text-center">
						{{ __data_pt($p->created_at) }}
					</div>
					<div class="col-md-4 col-6 text-right">
						Total: <strong>R${{ __moeda($p->valor_total) }}</strong>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th></th>
								<th>Produto</th>
								<th>Qtd.</th>
								<th>Valor unitário</th>
								<th>Sub total</th>
							</tr>
						</thead>
						<tbody>
							@foreach($p->itens as $i)
							<tr>
								<td>
									<img class="image" src="{{ $i->produto->img }}">
								</td>
								<td>
									@if($i->tamanho)
									@foreach($i->pizzas as $pizza)
									1/{{ sizeof($i->pizzas) }} {{ $pizza->sabor->nome }} @if(!$loop->last) | @endif
									@endforeach
									- Tamanho: <strong>{{ $i->tamanho->nome }}</strong>
									@else
									{{ $i->produto->nome }}
									@endif
								</td>
								<td>{{ number_format($i->quantidade, 0) }}</td>
								<td>{{ __moeda($i->valor_unitario) }}</td>
								<td>{{ __moeda($i->sub_total) }}</td>
							</tr>
							@if(sizeof($i->adicionais) > 0)
							<tr>
								<td colspan="5">
									Adicionais: 
									@foreach($i->adicionais as $a)
									<strong>{{ $a->adicional->nome }}@if(!$loop->last), @endif</strong>
									@endforeach
								</td>
							</tr>
							@endif

							@if($i->observacao)
							<tr>
								<td colspan="5">
									Observação: 
									<strong>{{ $i->observacao }}</strong>
								</td>
							</tr>
							@endif
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="card-footer">
				<div class="row">
					<div class="col-md-4 col-6">
						Valor de entrega: <strong>R${{ __moeda($p->valor_entrega) }}</strong><br>
						{{ $p->tipo_pagamento }}
					</div>

					<div class="col-md-4 col-6 text-center">
						Desconto: <strong>R${{ __moeda($p->desconto) }}</strong>
						@if($p->cupom)#{{ $p->cupom->codigo }}@endif
					</div>

					<div class="col-md-4 col-12 text-right">
						<a href="{{ route('food.carrinho-pedir-novamente', [$p->id, 'link='.$config->loja_id]) }}" class="btn btn-success">
							Pedir novamente
						</a>
					</div>
				</div>
			</div>
		</div>
		@endforeach
	</div>
</section>

@include('food.partials.modal_edit_endereco')

@endsection

@section('js')
<script type="text/javascript" src="/delivery/js/cart.js"></script>
@endsection