@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
		<tr>
			<th>Cliente</th>
			<th>Valor</th>
            <th>Data Vencimento</th>
			<th>Estado</th>
		</tr>
	</thead>
	<tbody>
		@foreach($data as $key => $item)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
			<td>{{ $item->cliente->razao_social }}</td>
			<td>{{ __moeda($item->valor_integral) }}</td>
			<td>{{ __data_pt($item->data_vencimento, 0) }}</td>
            <td>
                @if($item->status == 0)
                Pendente
                @else
                Recebido
                @endif
            </td>
		</tr>
		@endforeach
	</tbody>
</table>
<h4>Total a receber: R$ {{ __moeda($data->sum('valor_integral')) }}</h4>
@endsection
