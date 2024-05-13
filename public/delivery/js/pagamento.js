function selecionaEndereco(id){
	$('.borders').removeClass('bg-main')
	$('.end-'+id).addClass('bg-main')

	$.get(path_url + 'api/delivery-link/set-endereco', { 
		endereco_id: id, 
		carrinho_id: $('#inp-carrinho_id').val(), 
	}).done((res) => {
		console.log(res)
		$('.text-entrega').text("R$ " + convertFloatToMoeda(res.valor_frete))
		$('.text-total').text("R$ " + convertFloatToMoeda(res.valor_total))
	}).fail((err) => {
		console.log(err)
	})
	validaBtnFinish()
}

$(".pay-app").on("click", function (e) {
	$(this).addClass('active-div')
	$(".pay-entrega").removeClass('active-div')
	$(".div-pay-entrega").addClass('d-none')
	$(".div-pay-app").removeClass('d-none')
})
$(".pay-entrega").on("click", function (e) {
	$(this).addClass('active-div')
	$(".pay-app").removeClass('active-div')
	$(".div-pay-entrega").removeClass('d-none')
	$(".div-pay-app").addClass('d-none')
})

var _formaPagamento = null

function setFormaPagamento(str){
	$('.cartao-escolhido').text('')
	_formaPagamento = str
	$('.btn-pay').removeClass('active-btn')
	$('.btn-'+str).addClass('active-btn')

	$('.div-troco').addClass('d-none')
	if(str == 'Dinheiro'){
		$('.div-troco').removeClass('d-none')
	}else if(str == 'cartao-entrega'){
		_formaPagamento = null
		$('#modal-escolhe-cartao').modal('show')
	}
	else if(str == 'Pix pelo App'){
		$('#modal-pix').modal('show')
	}else if(str == 'Cartão pelo App'){
		$('#modal-cartao').modal('show')
	}
	validaBtnFinish()
}

function setCartao(str){
	_formaPagamento = str
	$('#modal-escolhe-cartao').modal('hide')
	$('.cartao-escolhido').text(str)
	validaBtnFinish()
}

$("#nao_precisa_troco").on("click", function (e) {
	if($(this).is(":checked")){
		$('#inp-troco_para').val(convertFloatToMoeda($('#inp-total').val()))
		validaBtnFinish()
	}
});

$("#inp-troco_para").on("blur", function (e) {
	validaBtnFinish()
})

$("#btn-pix").on("click", function (e) {
	let cpf = $('#inp-cpf').val()
	if(cpf.length == 14){
		finalizaPix(cpf)
	}else{
		swal("Alerta", "Informe um CPF válido", "warning")
	}
})

function validaBtnFinish(){
	let troco_para = convertMoedaToFloat($('#inp-troco_para').val())
	let valida = false
	if(_formaPagamento != null){
		valida = true
	}

	if(_formaPagamento == 'Dinheiro' && troco_para <= 0){
		valida = false
	}
	$('#inp-tipo_pagamento').val(_formaPagamento)
	setTimeout(() => {
		if(valida){
			$('.btn-finish').removeAttr('disabled')
		}else{
			$('.btn-finish').attr('disabled', true)
		}
	}, 50)
}


function finalizaPix(cpf){
	console.clear()

	$('.carrinho_id').val($('#inp-carrinho_id').val())
	$('.total').val($('#inp-total').val())
	$('.tipo_pagamento').val($('#inp-tipo_pagamento').val())
	$('.observacao').val($('#inp-observacao').val())

	$body = $("body");
	$body.addClass("loading");
	$('#form-pix').submit()
}
