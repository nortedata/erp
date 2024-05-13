@extends('layouts.app', ['title' => 'Estoque'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <a href="{{ route('estoque.create') }}" class="btn btn-success">
                            <i class="ri-add-circle-fill"></i>
                            Adicionar estoque
                        </a>
                    </div>
                    <div class="col-md-10"  style="text-align: right;">
                        <a href="{{ route('apontamento.create') }}" class="btn btn-info">
                            <i class="ri-settings-3-line"></i>
                            Apontamento Produção
                        </a>
                    </div>
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('produto', 'Pesquisar por produto')
                            !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('estoque.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th></th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor de venda</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td><img class="img-60" src="{{ $item->produto->img }}"></td>
                                    <td>
                                        {{ $item->descricao() }}
                                    </td>
                                    <td>{{ number_format($item->quantidade, 3, '.', '') }}</td>
                                    <td>{{ __moeda($item->produto->valor_unitario) }}</td>

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {!! $data->appends(request()->all())->links() !!}

            </div>
        </div>
    </div>
</div>
@endsection
