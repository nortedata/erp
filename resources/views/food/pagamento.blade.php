@extends('food.default', ['title' => 'Pagamento'])
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
</style>
@endsection
@section('content')

<section class="featured spad" style="margin-top: -100px">
	<div class="container">
		<form id="form-pay" class="row featured__filter mt-4" method="post" action="{{ route('food.finalizar-pagamento') }}">
			@csrf
			<br>
			<input type="hidden" id="inp-carrinho_id" value="{{ $carrinho->id }}">
			<input type="hidden" id="inp-total" value="{{ $carrinho->valor_total }}">
			<input type="hidden" id="inp-empresa_id" value="{{ $carrinho->empresa_id }}">
			<input type="hidden" name="link" value="{{ $config->loja_id }}">
			<input type="hidden" name="tipo_pagamento" id="inp-tipo_pagamento">

			@if(in_array('delivery', $config->tipo_entrega))

			<div class="col-md-4"></div>
			<div class="col-12 col-md-4">
				<button type="button" class="primary-btn btn-main btn-novo-endereco w-100" data-toggle="modal" data-target="#modal-endereco">
					<i class="fa fa-plus"></i> Novo endereço
				</button>
			</div>

			<div class="col-12 mt-2">

				@foreach($cliente->enderecos as $e)

				<div class="borders @if($carrinho->endereco_id == $e->id) bg-main @endif mt-1 end-{{$e->id}}" onclick="selecionaEndereco('{{ $e->id }}')">
					<h5>{{ $e->info }}</h5>
				</div>
				@endforeach

				@if(in_array('balcao', $config->tipo_entrega))
				<div class="borders mt-1 end-balcao" onclick="selecionaEndereco('balcao')">
					<h5>Retirar no balcão</h5>
				</div>
				@endif

			</div>
			@endif

			<div class="col-12 mt-2">
				<h5>Total de produtos: <strong class="text-muted">R$ {{ __moeda($carrinho->itens->sum('sub_total')) }}</strong></h5>

				<h5>Valor de entrega: <strong class="text-danger text-entrega">R$ {{ __moeda($carrinho->valor_frete) }}</strong></h5>

				@if($carrinho->valor_desconto > 0)
				<h5>Valor de desconto: <strong class="text-primary text-entrega">R$ {{ __moeda($carrinho->valor_desconto) }}</strong></h5>
				@endif
				<h4 class="mt-1">Valor total: <strong class="text-main text-total">R$ {{ __moeda($carrinho->valor_total) }}</strong></h4>

				@if($entregaGratis)
				<h6 class="text-success">
					<i class="fa fa-check"></i>
					Entrega gratis
				</h6>
				@endif
			</div>

			<div class="col-12 mt-2">
				<h4 class="text-center">Forma de pagamento</h4>
			</div>
			<div class="col-md-3"></div>

			<div class="col-6 col-md-3 text-center pay-entrega b-2 active-div">
				Na entrega
			</div>

			<div class="col-6 col-md-3 text-center b-2 pay-app">
				Pelo App
			</div>
			
			<div class="col-12 col-md-6 div-pay-entrega mt-3 offset-md-3">
				@if(in_array('Dinheiro', $config->tipos_pagamento))
				<button type="button" class="btn btn-light btn-pay w-100 btn-Dinheiro" onclick="setFormaPagamento('Dinheiro')">Dinheiro</button>
				@endif
				
				<div class="row div-troco d-none">
					<div class="col-6">
						<input type="tel" class="form-control m-3 moeda" id="inp-troco_para" name="troco_para" placeholder="Troco para">
					</div>
					<div class="col-6">
						<br>
						<input type="checkbox" id="nao_precisa_troco" class="form-checkbox m-1"><span>Não precisa</span>
					</div>
				</div>

				@if(App\Models\MarketPlaceConfig::validaCartaoEntrega($config->tipos_pagamento))
				<button type="button" class="btn btn-light w-100 mt-1 btn-pay btn-cartao-entrega" onclick="setFormaPagamento('cartao-entrega')">Cartão na entrega <span class="text-main cartao-escolhido"></span></button>
				@endif
			</div>
			<div class="col-12 col-md-6 div-pay-app mt-3 d-none offset-md-3">
				@if(in_array('Pix pelo App', $config->tipos_pagamento))
				<button type="button" class="btn btn-light w-100" onclick="setFormaPagamento('Pix pelo App')">Pagar com PIX</button>
				@endif
				@if(in_array('Cartão pelo App', $config->tipos_pagamento))
				<button type="button" class="btn btn-light w-100 mt-1" onclick="setFormaPagamento('Cartão pelo App')">Pagar com Cartão</button>
				@endif

			</div>

			<br>
			<div class="col-12 mt-4">
				<input type="text" class="form-control" id="inp-observacao" name="observacao" placeholder="Alguma observação para o pedido?">
			</div>

			<div class="col-12 col-md-4 offset-md-8">
				<button type="submit" class="btn btn-success btn-finish w-100 mt-3" disabled>Finalizar pedido</button>
			</div>
		</form>
	</div>
</section>

@include('food.partials.modal_endereco')
@include('food.partials.modal_pix')
@include('food.partials.modal_cartao')
@include('food.partials.modal_escolhe_cartao', ['tipos_pagamento' => $config->tipos_pagamento])
@section('js')
<script type="text/javascript" src="/delivery/js/pagamento.js"></script>

<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
<script type="text/javascript">
	$(function(){
		window.Mercadopago.setPublishableKey('{{ $config->mercadopago_public_key }}');
		window.Mercadopago.getIdentificationTypes();
		
	})

	$('#cardNumber').keyup(() => {
		let cardnumber = $('#cardNumber').val().replaceAll(" ", "");
		if (cardnumber.length >= 6) {
			let bin = cardnumber.substring(0,6);

			window.Mercadopago.getPaymentMethod({
				"bin": bin
			}, setPaymentMethod);
		}
	})

	function setPaymentMethod(status, response) {
		if (status == 200) {
			let paymentMethod = response[0];
			document.getElementById('paymentMethodId').value = paymentMethod.id;

			$('#band-img').attr("src", paymentMethod.thumbnail);
			getIssuers(paymentMethod.id);
		} else {
			alert(`payment method info error: ${response}`);
		}
	}

	function getIssuers(paymentMethodId) {
		window.Mercadopago.getIssuers(
			paymentMethodId,
			setIssuers
			);
	}

	function setIssuers(status, response) {
		if (status == 200) {
			let issuerSelect = document.getElementById('issuer');
			$('#issuer').html('');
			response.forEach( issuer => {
				let opt = document.createElement('option');
				opt.text = issuer.name;
				opt.value = issuer.id;
				issuerSelect.appendChild(opt);
			});

			getInstallments(
				document.getElementById('paymentMethodId').value,
				document.getElementById('transactionAmount').value,
				issuerSelect.value
				);
		} else {
			alert(`issuers method info error: ${response}`);
		}
	}

	function getInstallments(paymentMethodId, transactionAmount, issuerId){
		window.Mercadopago.getInstallments({
			"payment_method_id": paymentMethodId,
			"amount": parseFloat(transactionAmount),
			"issuer_id": parseInt(issuerId)
		}, setInstallments);
	}

	function setInstallments(status, response){
		if (status == 200) {
			document.getElementById('installments').options.length = 0;
			response[0].payer_costs.forEach( payerCost => {
				let opt = document.createElement('option');
				opt.text = payerCost.recommended_message;
				opt.value = payerCost.installments;
				document.getElementById('installments').appendChild(opt);

				$('.installments').css('display', 'none')
				$('#installments').css('display', 'block')

			});
		} else {
			alert(`installments method info error: ${response}`);
		}
	}

	doSubmit = false;
	document.getElementById('paymentFormCartao').addEventListener('submit', getCardToken);
	function getCardToken(event){
		event.preventDefault();
		if(!doSubmit){
			$('.carrinho_id').val($('#inp-carrinho_id').val())
			$('.total').val($('#inp-total').val())
			$('.tipo_pagamento').val($('#inp-tipo_pagamento').val())
			$('.observacao').val($('#inp-observacao').val())

			let docNumber = $('.cpf_cnpj').val().replace(/[^0-9]/g,'')
			$('.cpf_cnpj').val(docNumber)

			setTimeout(() => {
				let $form = document.getElementById('paymentFormCartao');
				// $form.submit();

				window.Mercadopago.createToken($form, setCardTokenAndPay);
				return false;
			}, 50)
		}
	};

	function setCardTokenAndPay(status, response) {

		if (status == 200 || status == 201) {
			let form = document.getElementById('paymentForm');
			let card = document.createElement('input');
			card.setAttribute('name', 'token');
			card.setAttribute('type', 'hidden');
			card.setAttribute('value', response.id);
			form.appendChild(card);
			doSubmit=true;
			$('button').attr('disabled', true)
			form.submit();
		} else {
			alert("Verify filled data!\n"+JSON.stringify(response, null, 4));
		}
	};

	$('#observacao').focusout(() => {
		$('.observacao').val($('#observacao').val())
	})

</script>
@endsection
@endsection