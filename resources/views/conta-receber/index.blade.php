@extends('layouts.app', ['title' => 'Conta a receber'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-2">
                    <a href="{{ route('conta-receber.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Conta Receber
                    </a>
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            {!!Form::select('cliente_id', 'Pesquisar por nome')->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data Final')
                            !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('conta-receber.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                        </div>
                                    </th>
                                    <th>Razão Social</th>
                                    <th>Valor Integral</th>
                                    <th>Valor Recebido</th>
                                    <th>Data Cadastro</th>
                                    <th>Data Vencimento</th>
                                    <th>Data Recebimento</th>
                                    <th>Estado</th>
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input check-delete" type="checkbox" name="item_delete[]" value="{{ $item->id }}">
                                        </div>
                                    </td>
                                    <td>{{ ($item->cliente) ? $item->cliente->razao_social : '--' }}</td>
                                    <td>{{ __moeda($item->valor_integral) }}</td>
                                    <td>{{ __moeda($item->valor_recebido) }}</td>
                                    <td>{{ __data_pt($item->created_at, 0) }}</td>
                                    <td>
                                        {{ __data_pt($item->data_vencimento, 0) }}
                                        @if(!$item->status)
                                        <br>
                                        <span class="text-danger" style="font-size: 10px">{{ $item->diasAtraso() }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->status ? __data_pt($item->data_recebimento, false) : '--' }}</td>
                                    <td>
                                        @if($item->status)
                                        <span class="btn btn-success position-relative me-lg-5 btn-sm">
                                            <i class="ri-checkbox-line"></i> Recebido
                                        </span>
                                        @else
                                        <span class="btn btn-warning position-relative me-lg-5 btn-sm">
                                            <i class="ri-alert-line"></i> Pendente
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('conta-receber.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 150px;">
                                            @if(!$item->status)
                                            @method('delete')
                                            <a class="btn btn-warning btn-sm" href="{{ route('conta-receber.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @csrf
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            <a href="{{ route('conta-receber.pay', $item) }}" class="btn btn-success btn-sm text-white">
                                                <i class="ri-money-dollar-box-line"></i>
                                            </a>
                                            @endif
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
                        <br>
                        <form action="{{ route('conta-receber.destroy-select') }}" method="post" id="form-delete-select">
                            @method('delete')
                            @csrf
                            <div></div>
                            <button type="button" class="btn btn-danger btn-sm btn-delete-all" disabled>
                                <i class="ri-close-circle-line"></i> Remover selecionados
                            </button>
                        </form>
                    </div>
                </div>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection
