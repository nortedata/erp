@extends('layouts.app', ['title' => 'Detalhes da NFCe'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <h4>Detalhes da NFCe</h4>
                <div style="text-align: right; margin-top: -35px;">
                    <a href="{{ route('nfce.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                <hr class="mt-3">
                <div class="">
                    <h4>Cliente: <strong style="color: steelblue">{{ $data->cliente_id ? $data->cliente->razao_social : 'Consumidor Final'}}</strong></h4>
                    <h4>Total: <strong class="text-success">R$ {{ __moeda($data->total) }}</strong></h4>

                </div>
                <hr>
                <div class="col-lg-12 mt-4">
                    <h5>Itens da NFCe</h5>
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Valor</th>
                                    <th>Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data->itens as $item)
                                <tr>
                                    <td>{{ $item->produto->nome }}</td>
                                    <td>{{ $item->quantidade }}</td>
                                    <td>{{ __moeda($item->valor_unitario) }}</td>
                                    <td>{{ __moeda($item->sub_total) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                    <div class="mt-5">
                        <h5>Fatura</h5>
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Pagamento</th>
                                    <th>Data Vencimento</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data->fatura as $item)
                                <tr>
                                    <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                                    <td>{{ __data_pt($item->data_vencimento, 0) }}</td>
                                    <td>{{ __moeda($item->valor) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Nfe sem informações de pagamento</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

