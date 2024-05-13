@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Data</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $item)
        <tr class="@if($key%2 == 0) pure-table-odd @endif">
            <td>
                {{ $item['id'] }}
            </td>
            <td>
                {{ $item['cliente'] }}
            </td>
            <td>
                {{ __data_pt($item['data']) }}
            </td>
            <td>
                {{ __moeda($item['total']) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
{{-- <h4>Total de Vendas: R$ {{ $item->sum(['total']) }}</h4> --}}
@endsection
