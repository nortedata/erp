@foreach($produtos as $p)
<div class="col-md-3 produto" ondblclick="infoProduto('{{$p->id}}')" onclick="addProduto('{{$p->id}}')">
	
	<div class="card d-block">
		<span class="stock-item"><b>{{ $p->estoqueAtual() }} {{ $p->unidade }}</b></span>
		<img class="card-img-top" src="{{ $p->img }}" alt="{{ $p->nome }}">
		<div class="card-body body-item">
			<h4 class="card-title">{{ substr($p->nome, 0, 44) }}</h4>
			@if(isset($lista_id) && $lista_id)
			
			@if($p->itemListaView($lista_id))
			<p class="card-text">R$ {{ __moeda($p->itemListaView($lista_id)->valor) }}</p>
			@endif

			@else
			<p class="card-text">R$ {{ __moeda($p->valor_unitario) }}</p>
			@endif
		</div>
	</div>
</div>
@endforeach
<div class="mt-1 d-flex justify-content-center produtos-pagination">
	{!! $produtos->links() !!}
</div>