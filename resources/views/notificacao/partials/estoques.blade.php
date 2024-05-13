<div class="row">
	<div class="col-md-6 col-12">
		<h5>Produto: <strong>{{ $item->produto->nome }}</strong></h5>
		<h5>Quantidade: <strong>{{ $item->quantidade }}</strong></h5>
	</div>
	<div class="col-md-6 col-12">
		<h5>Valor de compra: <strong>R$ {{ __moeda($item->produto->valor_compra) }}</strong></h5>
		<h5>Valor de venda: <strong>R$ {{ __moeda($item->produto->valor_unitario) }}</strong></h5>
	</div>

</div>