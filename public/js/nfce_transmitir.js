function transmitir(id){
	console.clear()
	$.post(path_url + "api/nfce_painel/emitir", {
		id: id,
	})
	.done((success) => {
		swal("Sucesso", "NFe emitida " + success.recibo + " - chave: [" + success.chave + "]", "success")
		.then(() => {
			window.open(path_url + 'nfce/imprimir/' + id, "_blank")
			setTimeout(() => {
				location.reload()
			}, 100)
		})
	})
	.fail((err) => {
		console.log(err)
		
		swal("Algo deu errado", err.responseJSON, "error")
		
	})
}

var IDNFE = null
function cancelar(id, numero){
	IDNFE = id
	$('.ref-numero').text(numero)
	$('#modal-cancelar').modal('show')
}

function corrigir(id, numero){
	IDNFE = id
	$('.ref-numero').text(numero)
	$('#modal-corrigir').modal('show')
}

$('#btn-cancelar').click(() => {
	if(IDNFE != null){
		$.post(path_url + "api/nfce_painel/cancelar", {
			id: IDNFE,
			motivo: $('#inp-motivo-cancela').val()
		})
		.done((success) => {
			swal("Sucesso", "NFe cancelada " + success, "success")
			.then(() => {
				location.reload()
			})
		})
		.fail((err) => {
			console.log(err)

			swal("Algo deu errado", err.responseJSON, "error")

		})
	}else{
		swal("Erro", "Nota não selecionada", "error")
	}
})


function consultar(id, numero){
	$.post(path_url + "api/nfce_painel/consultar", {
		id: id,
	})
	.done((success) => {
		swal("Sucesso", success, "success")
	})
	.fail((err) => {
		console.log(err)
		swal("Algo deu errado", err.responseJSON, "error")

	})
}