@extends('layouts.app', ['title' => 'Lista de Pré vendas'])
@section('css')
<style type="text/css">
    td:hover{
        cursor: pointer;
    }
</style>
@endsection
@section('content')

<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-2">
                    <a href="{{ route('pre-venda.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Pré venda
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
                            {!!Form::text('codigo', 'Pesquisar por código')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data final')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('status', 'Estado',
                            ['1' => 'Não recebidas',
                            '-1' => 'Recebidas'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('pre-venda.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-centered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                <tr ondblclick="finalizar('{{$item->id}}')">
                                    <td width="200">{{ $item->codigo }}</td>
                                    <td width="600">{{ $item->cliente_id ? $item->cliente->razao_social : 'Consumidor Final' }}</td>
                                    <td width="200">{{ __data_pt($item->created_at) }}</td>
                                    <td width="200">{{ __moeda($item->valor_total) }}</td>
                                    <td width="150">
                                        @if($item->status == false)
                                        <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                        <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </td>
                                    <td style="width: 280px">
                                        <form action="{{ route('pre-venda.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf
                                            @if($item->status == 1)
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endif

                                            @if($item->status == 0 && $item->venda_id != null && $item->tipo_finalizado == 'nfe')

                                            <a type="button" class="btn btn-light info btn-sm" title="Ver Nfe" href="{{ route('nfe.show', $item->venda_id) }}">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a class="btn btn-primary btn-sm" title="Imprimir Nfc-e" target="_blank" href="{{ route('nfe.imprimir', [$item->venda_id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>

                                            @endif

                                            @if($item->status == 0 && $item->venda_id != null && $item->tipo_finalizado == 'nfce')

                                            <a type="button" class="btn btn-light info btn-sm" title="Ver Nfc-e" href="{{ route('nfce.show', $item->venda_id) }}">
                                                <i class="ri-eye-line"></i>
                                            </a>

                                            @if($item->tipo_finalizado == 'nfe' && $item->venda_id != null )
                                            <a class="btn btn-primary btn-sm" title="Imprimir Nfc-e" target="_blank" href="{{ route('nfce.imprimir', [$item->venda_id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>

                                            @elseif($item->tipo_finalizado == 'nfce' && $item->venda_id != null)
                                            <a class="btn btn-success btn-sm" title="Imprimir Pedido" target="_blank" href="{{ route('frontbox.imprimir-nao-fiscal', [$item->venda_id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>

                                            @if($item->nfce->estado == 'aprovado')
                                            <a class="btn btn-primary btn-sm" title="Imprimir Nfc-e" target="_blank" href="{{ route('nfce.imprimir', [$item->venda_id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>
                                            @endif

                                            @endif
                                            @endif

                                            @if($item->status == 1)
                                            <button type="button" class="btn btn-dark btn-sm" title="Finalizar" onclick="finalizar('{{$item->id}}')">
                                                <i class="ri-coins-fill"></i>
                                            </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@include('modals._finalizar_pre_venda', ['not_submit' => true])

@endsection

@section('js')
<script src="/js/pre_venda.js"></script>
@endsection


