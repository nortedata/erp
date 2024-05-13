@extends('layouts.app', ['title' => 'Lista de Vendas'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-2">
                    <a href="{{ route('frontbox.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        PDV
                    </a>
                </div>
                <hr>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-5">
                        {!! Form::text('nome', 'Pesquisar por nome') !!}
                    </div>
                    <div class="col-md-6 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('frontbox.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="col-lg-12 mt-4">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cliente</th>
                                    <th>CPF/CNPJ</th>
                                    <th>Valor</th>
                                    <th>Estado</th>
                                    <th>Ambiente</th>
                                    <th>Data</th>
                                    <th>Lista de preço</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->cliente ? $item->cliente->razao_social : ($item->cliente_nome != "" ? $item->cliente_nome : "--") }}</td>
                                    <td>{{ $item->cliente ? $item->cliente->cpf_cnpj : ($item->cliente_cpf_cnpj != "" ? $item->cliente_cpf_cnpj : "--") }}</td>
                                    <td>{{ __moeda($item->total) }}</td>
                                    <td width="150">
                                        @if($item->estado == 'aprovado')
                                        <span class="btn btn-success text-white btn-sm w-100">aprovado</span>
                                        @elseif($item->estado == 'cancelado')
                                        <span class="btn btn-danger text-white btn-sm w-100">cancelado</span>
                                        @elseif($item->estado == 'rejeitado')
                                        <span class="btn btn-warning text-white btn-sm w-100">rejeitado</span>
                                        @else
                                        <span class="btn btn-info text-white btn-sm w-100">novo</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $item->lista_id ? $item->listaPreco->nome : '--' }}</td>

                                    <td width="300">
                                        <form action="{{ route('frontbox.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf
                                            <a class="btn btn-primary btn-sm" target="_blank" href="{{ route('frontbox.imprimir-nao-fiscal', [$item->id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>

                                            <!-- <a class="btn btn-warning btn-sm" href="{{ route('frontbox.edit', $item->id) }}">
                                                <i class="ri-edit-line"></i>
                                            </a> -->

                                            <button type="button" class="btn btn-danger btn-sm btn-delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>

                                            @if($item->estado == 'novo' || $item->estado == 'rejeitado')

                                            <button title="Transmitir NFCe" type="button" class="btn btn-success btn-sm" onclick="transmitir('{{$item->id}}')">
                                                <i class="ri-send-plane-fill"></i>
                                            </button>
                                            @endif

                                            <a class="btn btn-ligth btn-sm" title="Detalhes" href="{{ route('frontbox.show', $item->id) }}"><i class="ri-eye-line"></i></a>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- {!! $data->appends(request()->all())->links() !!} --}}
                </div>
                <h5 class="mt-2">Soma: <strong class="text-success">R$ {{ __moeda($data->sum('total')) }}</strong></h5>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript" src="/js/nfce_transmitir.js"></script>

@endsection
