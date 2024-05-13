@extends('layouts.app', ['title' => 'Lista de Caixa'])
@section('content')

<div class="card mt-1">
    <div class="card-body">
        <div class="table-response">
            <table class="table table-striped table-centered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Caixa</th>
                        <th>Data Abertura</th>
                        <th>Data Fechamento</th>
                        <th>Valor Abertura</th>
                        <th>Valor Fechamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                    <tr>
                        <td>{{ $item->usuario->name }}</td>
                        <td>{{ __data_pt($item->created_at) }}</td>
                        <td>{{ $item->data_fechamento ? __data_pt($item->data_fechamento) : '--' }}</td>
                        <td>{{ __moeda($item->valor_abertura) }}</td>
                        <td>{{ __moeda($item->valor_fechamento) }}</td>
                        <td>
                            @if($item->status == 0)
                            <a target="_blank" class="btn btn-dark btn-sm" href="{{ route('caixa.imprimir' , $item) }}"><i class="ri-printer-fill"></i></a>
                            @endif

                            <a class="btn btn-primary btn-sm" href="{{ route('caixa.show' , $item) }}"><i class=" ri-list-indefinite"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Nada encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')

@endsection
