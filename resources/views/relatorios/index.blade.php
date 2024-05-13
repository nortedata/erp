@extends('layouts.app', ['title' => 'Relatórios'])
@section('css')
<style type="text/css">
    .card-header {
        border-bottom: 1px solid #999;
        margin-left: 5px;
        margin-right: 5px;
    }

</style>
@endsection
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.produtos') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de produtos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                {!!Form::select('estoque', 'Estoque',
                                [
                                '' => 'Selecione',
                                '1' => 'Positivo',
                                '-1' => 'Negativo',
                                ])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                            <div class="col-md-6 col-12">
                                {!!Form::select('tipo', 'Tipo',
                                [
                                '' => 'Selecione',
                                '1' => 'Mais vendidos',
                                '-1' => 'Menos vendidos',
                                ])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                            <div class="col-md-6 col-12 mt-2">
                                {!!Form::select('marca_id', 'Marca', ['' => 'Selecione'] + $marcas->pluck('nome', 'id')->all())
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                            <div class="col-md-6 col-12 mt-2">
                                {!!Form::select('categoria_id', 'Categoria', ['' => 'Selecione'] + $categorias->pluck('nome', 'id')->all())
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.clientes') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de clientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::select('tipo', 'Tipo',
                                [
                                '' => 'Selecione',
                                '1' => 'Mais vendas',
                                '-1' => 'Menos vendas',
                                ])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.fornecedores') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de fornecedores</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::select('tipo', 'Tipo',
                                [
                                '' => 'Selecione',
                                '1' => 'Mais compras',
                                '-1' => 'Menos compras',
                                ])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.cte') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de CTe</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                            <div class="col-md-3 col-12">
                                {!!Form::select('estado', 'Estado',
                                ['novo' => 'Novas',
                                'rejeitado' => 'Rejeitadas',
                                'cancelado' => 'Canceladas',
                                'aprovado' => 'Aprovadas',
                                '' => 'Todos'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>


        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.nfe') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de NFe</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::select('tipo', 'Tipo',
                                [
                                '' => 'Selecione',
                                '1' => 'Saída',
                                '-1' => 'Entrada',
                                ])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>

                            <div class="col-md-6 col-12 mt-2">
                                {!!Form::select('cliente', 'Cliente')
                                ->attrs(['class' => 'form-select cliente_id'])
                                !!}
                            </div>

                            <div class="col-md-3 col-12 mt-2">
                                {!!Form::select('finNFe', 'Finalidade NFe', [
                                '1' => 'NFe normal',
                                '2' => 'NFe complementar',
                                '3' => 'NFe de ajuste',
                                '4' => 'Devolução de mercadoria'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>

                            <div class="col-md-3 mt-2">
                                {!!Form::select('estado', 'Estado',
                                ['novo' => 'Novas',
                                'rejeitado' => 'Rejeitadas',
                                'cancelado' => 'Canceladas',
                                'aprovado' => 'Aprovadas',
                                '' => 'Todos'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.nfce') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de NFCe</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>

                            <div class="col-md-4 col-12">
                                {!!Form::select('cliente_id', 'Cliente')
                                ->attrs(['class' => 'form-select cliente_id'])
                                !!}
                            </div>

                            <div class="col-md-3 mt-2">
                                {!!Form::select('estado', 'Estado',
                                ['novo' => 'Novas',
                                'rejeitado' => 'Rejeitadas',
                                'cancelado' => 'Canceladas',
                                'aprovado' => 'Aprovadas',
                                '' => 'Todos'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.conta_pagar') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de Contas a Pagar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>

                            <div class="col-md-4 col-12">
                                {!!Form::select('status', 'Estado',
                                ['1' => 'Quitadas', '-1' => 'Pendentes', '' => 'Todas'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.conta_receber') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de Contas a Receber</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>

                            <div class="col-md-4 col-12">
                                {!!Form::select('status', 'Estado',
                                ['1' => 'Recebidas',
                                '-1' => 'Pendentes',
                                '' => 'Todos'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>


        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.comissao') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de Comissão</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>

                            <div class="col-md-4 col-12">
                                {!!Form::select('funcionario_id', 'Funcionário', ['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.mdfe') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de MDFe</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                            <div class="col-md-3 col-12">
                                {!!Form::select('estado', 'Estado',
                                ['novo' => 'Novas',
                                'rejeitado' => 'Rejeitadas',
                                'cancelado' => 'Canceladas',
                                'aprovado' => 'Aprovadas',
                                '' => 'Todos'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.vendas') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de Vendas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                            <div class="col-md-3 col-12">
                                {!!Form::select('estado', 'Estado',
                                ['novo' => 'Novas',
                                'rejeitado' => 'Rejeitadas',
                                'cancelado' => 'Canceladas',
                                'aprovado' => 'Aprovadas',
                                '' => 'Todos'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.compras') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de Compras</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                            {{-- <div class="col-md-3 col-12">
                                {!!Form::select('estado', 'Estado',
                                ['novo' => 'Novas',
                                'rejeitado' => 'Rejeitadas',
                                'cancelado' => 'Canceladas',
                                'aprovado' => 'Aprovadas',
                                '' => 'Todos'])
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div> --}}
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6">
            <form method="get" action="{{ route('relatorios.taxas') }}">
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de Taxas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                {!!Form::date('start_date', 'Dt. inicial')
                                !!}
                            </div>
                            <div class="col-md-4 col-12">
                                {!!Form::date('end_date', 'Dt. final')
                                !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-dark w-100">
                            <i class="ri-printer-line"></i> Gerar relatório
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
