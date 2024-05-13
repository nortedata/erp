@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
		<tr>
			<th>Cliente</th>
			<th>Valor</th>
			<th>Num. doc</th>
			<th>Chave</th>
			<th>Estado</th>
			<th>Data</th>

		</tr>
	</thead>
	<tbody>
		@foreach($data as $key => $item)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
			<td>{{ $item->cliente ? $item->cliente->info : 'consumidor final' }}</td>

			<td>{{ __moeda($item->total) }}</td>
			<td>{{ $item->numero }}</td>
			<td>{{ $item->chave }}</td>
			<td>{{ strtoupper($item->estado) }}</td>
			<td>{{ __data_pt($item->data_emissao) }}</td>

		</tr>
		@endforeach
	</tbody>
</table>
<h4>Total: R$ {{ __moeda($data->sum('total')) }}</h4>
@endsection
