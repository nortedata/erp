@foreach($item->itens as $i)
<tr class="dynamic-form">
	<td>
		<input type="text" class="form-control" name="descricao_variacao[]" value="{{ $i->nome }}" required readonly>
	</td>
	<td>
		<input type="tel" class="form-control moeda" name="valor_venda_variacao[]" value="" required>
	</td>

	<td>
		<input type="tel" class="form-control ignore" name="codigo_barras_variacao[]" value="">
	</td>
	<td>
		<input type="text" class="form-control ignore" name="referencia_variacao[]" value="">
	</td>
	<td>
		<input class="ignore" accept="image/*" type="file" class="form-control" name="imagem_variacao[]" value="">
	</td>
	<td>
		<button type="button" class="btn btn-sm btn-danger btn-remove-tr-variacao">
			<i class="ri-subtract-line"></i>
		</button>
	</td>
</tr>
@endforeach