@extends('food.default', ['title' => 'Home'])
@section('content')

<section class="featured spad" style="margin-top: -100px">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="section-title">
					<h2>Produtos</h2>
				</div>
			</div>
		</div>
		<div class="row featured__filter">

			@foreach($produtos as $p)
			<div class="col-lg-3 col-md-4 col-sm-6 mix {{$p->categoria->nome}} fresh-meat">
				<div class="featured__item">
					<div class="featured__item__pic set-bg" data-setbg="{{ $p->img }}">
						<ul class="featured__item__pic__hover">
							<li><a href="{{ route('food.produto-detalhe', [$p->hash_delivery, 'link='.$config->loja_id])}}"><i class="fa fa-shopping-cart"></i></a></li>
						</ul>
					</div>
					<div class="featured__item__text">
						<h6><a href="{{ route('food.produto-detalhe', [$p->hash_delivery, 'link='.$config->loja_id])}}">{{ $p->nome }}</a></h6>
						<h5 class="text-main">R$ {{ __moeda($p->valor_delivery) }}</h5>
					</div>
				</div>
			</div>
			@endforeach

		</div>
	</div>
</section>

@endsection