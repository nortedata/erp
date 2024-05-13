@extends('food.default', ['title' => 'Identificação'])
@section('content')


@section('css')
<style type="text/css">
	.btn-main{
		border: none;
	}
</style>
@endsection

<section class="featured spad" style="margin-top: -100px">
	<div class="container">
		<br>
		<input type="hidden" value="{{ $config->loja_id }}" id="inp-link">
		<input type="hidden" value="{{ $carrinho->id }}" id="inp-carrinho_id">
		<div class="row featured__filter">
			<input type="hidden" id="inp-empresa_id" value="{{ $config->empresa_id }}">
			<p class="col-12 text-main">Digite seu telefone para identificar o cadastro</p>
			<div class="col-md-3 col-12 mt-1">
				<input type="tel" id="inp-fone" name="fone" placeholder="(43) 99999-9999" class="fone form-control">
			</div>
			<div class="col-md-6 col-12 mt-1">
				<input type="text" id="inp-nome" class="form-control" placeholder="Informe seu nome completo" name="">
			</div>
			<div class="col-md-3 col-12 mt-1">
				<button type="button" class="primary-btn btn-main w-100 d-none">Continuar</button>
			</div>
		</div>
	</div>
</section>
@section('js')
<script type="text/javascript" src="/delivery/js/auth.js"></script>
@endsection
@endsection