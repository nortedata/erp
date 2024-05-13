<div id="pagamento_multiplo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="standard-modalLabel">Pagamento Múltiplo <strong class="total-venda-modal">@isset($item) {{__moeda($item->valor_total)}}@endif</strong></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::select('tipo_pagamento_row', 'Tipo de Pagamento',['' => 'Selecione'] + App\Models\Nfce::tiposPagamento())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('valor_row', 'Valor')->attrs(['class' => 'moeda']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('data_vencimento_row', 'Vencimento')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::text('observacao_row', 'Observação')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-1 mt-3">
                        <button type="button" style="margin-left: 15px" class="btn btn-info btn-add-payment"><i class="ri-add-line"></i></button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-centered mb-0 mt-2 table-payment">
                        <thead>
                            <tr>
                                <th>Tipo de Pagamento</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Observações</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($item)
                            @if($item != null)
                            @foreach ($item->fatura as $fatura)
                            <tr>
                                <td>
                                    <input readonly type="sel" name="tipo_pagamento_row[]" class="form-control" value="{{ $fatura->forma_pagamento }}">
                                </td>
                                <td>
                                    <input readonly type="date" name="vencimento_parcela_row[]" class="form-control" value="{{ $fatura->vencimento }}">
                                </td>
                                <td>
                                    <input readonly type="text" name="valor_integral_row[]" class="form-control valor_integral" value="{{ __moeda($fatura->valor) }}">
                                </td>
                                <td>
                                    <input readonly type="text" name="obs_row[]" class="form-control" value="{{ $fatura->obs_row }}">
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-delete-row">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            @endisset
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Soma pagamento:</td>
                                @isset($data)
                                <td class="sum-payment">R$ {{ __moeda($data->valor_total) }}</td>
                                @else
                                <td class="sum-payment">R$ 0,00</td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                    <div class="mt-3">
                        <h6 style="color: rgb(218, 19, 19); size:25px" class="mt-2">Diferença: <strong class="sum-restante"></strong></h6>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                {{-- <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button> --}}
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Salvar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
