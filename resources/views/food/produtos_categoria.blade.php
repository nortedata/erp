@extends('food.default', ['title' => 'Produtos'])
@section('css')
<style type="text/css">
	.img-size{
		height: 80px;
	}
	.border-tamanho img{
		border: 1px solid #EFEFEF;
		border-radius: 5px;
		padding: 3px;
	}
	.border-tamanho:hover{
		cursor: pointer;
	}
	.active-border{
		background: var(--color-main);
	}

	.bg-active{
		background: #34A853;
	}
	.fresh-meat{
		border-radius: 10px;
	}
	.fixedbutton {
		position: fixed;
		bottom: 10px;
		z-index: 999;
		width: 84%;
	}
	@media (max-width:480px)  {
		.fixedbutton {
			width: 100%;
		}
	}
</style>
@endsection
@section('content')

<section class="featured spad" style="margin-top: -100px">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="section-title">
					<h4>Produtos da categoria <strong>{{ $categoria->nome }}</strong></h4>
				</div>
				
			</div>
		</div>

		<div class="col-12 mb-3">
			@if($categoria->tipo_pizza)
			<h5 class="text-center">Selecione o tamanho</h5>

			<div class="row mt-2">
				@foreach($tamanhosPizza as $key => $t)
				<div class="col border-tamanho text-center" onclick="escolherTamanho('{{ $t }}', '{{ $key+1 }}')">
					<img class="img-size active-{{ $key+1 }}" src="/delivery/tamanhos/{{ $key+1 }}.png">
					<br>
					<b style="font-size: 12px">{{ $t->nome }}</b><br>
					<b style="font-size: 11px;">{{ $t->quantidade_pedacos }} pedaço(s)</b>
				</div>
				@endforeach
			</div>
			@endif
		</div>

		<div class="row featured__filter">

			@foreach($produtos as $p)
			<div class="col-lg-3 col-md-4 col-sm-6 mix fresh-meat produto-{{ $p->id }} m-1">
				<div class="featured__item">
					<br>
					<div class="featured__item__pic set-bg" data-setbg="{{ $p->img }}">
						<ul class="featured__item__pic__hover">
							@if($p->categoria && $p->categoria->tipo_pizza)

							@else
							<li><a href="{{ route('food.produto-detalhe', [$p->hash_delivery, 'link='.$config->loja_id])}}"><i class="fa fa-shopping-cart"></i></a></li>
							@endif
						</ul>
					</div>
					<div class="featured__item__text" style="height: 120px">
						<h6>
							<a href="{{ route('food.produto-detalhe', [$p->hash_delivery, 'link='.$config->loja_id])}}">{{ $p->nome }}</a>
						</h6>
						@if($p->categoria && $p->categoria->tipo_pizza)
						<h5 class="text-main valor-pizza-{{ $p->id }}">
							{!! $p->valoresPizza() !!}
						</h5>
						@else
						<h5 class="text-main">R$ {{ __moeda($p->valor_delivery) }}</h5>
						@endif
					</div>
					<button type="button" onclick="selecionaPizza('{{ $p->id }}')" class="btn btn-main btn-seleciona d-none text-white w-100 mt-2">selecionar sabor</button>
				</div>
			</div>
			@endforeach
			
		</div>
		<div class="row">
			<button class="btn btn-main text-white fixedbutton d-none" type="button">
				<i class="fa fa-shopping-cart"></i>
				Adicionar ao carrinho <strong class="valor-pizza">0,00</strong>
			</button>
		</div>

		<form method="get" action="{{ route('food.pizza-detalhe') }}" id="form-pizza">
			<input type="hidden" value="{{ $config->loja_id }}" name="link">
			<input type="hidden" value="" name="tamanho_id" id="tamanho_id">
			<input type="hidden" value="" name="valor_pizza" id="valor_pizza">
			<div class="appends">
				
			</div>
		</form>
	</div>
</section>

@if($categoria->tipo_pizza)
@section('js')
<script type="text/javascript" src="/delivery/js/pizza.js"></script>
@endsection
@endif
@endsection