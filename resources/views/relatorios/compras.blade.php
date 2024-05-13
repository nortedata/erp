@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Fornecedor</th>
            <th>Data</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
        <tr class="">
            <td>
                {{ $item->id }}
            </td>
            <td>
            {{ $item->fornecedor->razao_social }}
            </td>
            <td>
                {{ __data_pt($item->created_at) }}
            </td>
            <td>
                {{ __moeda($item->total) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<h4>Total de Compras: R$ {{ __moeda($data->sum('total')) }}</h4>
@endsection
