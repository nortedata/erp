@extends('layouts.app', ['title' => 'Home'])
@if(!__isContador())
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <h3>Painel</h3>
                <div class="row">
                    <div class="col-md-4 col-lg-2 col-12 mb-2">
                        {!!Form::select('periodo', 'Período', 
                        [
                        '1' => 'Hoje', 
                        '7' => 'Semana',
                        '30' => 'Mês',
                        '365' => 'Ano'
                        ]
                        )
                        ->value(7)
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-3 col-xl-2">
                        <div class="card widget-icon-box text-bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="text-uppercase fs-13 mt-0" title="Average Revenue">Contas a Receber</h5>
                                        <h3 class="my-3 total-receber" style="font-size: 16px;">R$ 0,00</h3>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-white bg-opacity-25 text-white rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-xl-2">
                        <div class="card widget-icon-box text-bg-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="text-uppercase fs-13 mt-0" title="Average Revenue">Contas a Pagar</h5>
                                        <h3 class="my-3 total-pagar" style="font-size: 16px;">R$ 0,00</h3>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-white bg-opacity-25 text-white rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-xl-2">
                        <div class="card widget-icon-box text-bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="text-uppercase fs-13 mt-0" title="Average Revenue">Produtos</h5>
                                        <h3 class="my-3 total-produtos" style="font-size: 16px;">0</h3>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-white bg-opacity-25 text-white rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                            <i class="ri-box-3-line"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-xl-2">
                        <div class="card widget-icon-box text-bg-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="text-uppercase fs-13 mt-0" title="Average Revenue">Clientes</h5>
                                        <h3 class="my-3 total-clientes" style="font-size: 16px;">0</h3>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-white bg-opacity-25 text-white rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                            <i class="ri-account-box-fill"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-xl-2">
                        <div class="card widget-icon-box text-bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="text-uppercase fs-13 mt-0" title="Average Revenue">Total de Vendas</h5>
                                        <h3 class="my-3 total-vendas" style="font-size: 16px;">R$ 0,00</h3>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-white bg-opacity-25 text-white rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                            <i class="ri-shopping-cart-fill"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-xl-2">
                        <div class="card widget-icon-box text-bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="text-uppercase fs-13 mt-0" title="Average Revenue">Total de Compras</h5>
                                        <h3 class="my-3 total-compras" style="font-size: 16px;">R$ 0,00</h3>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-white bg-opacity-25 text-white rounded rounded-3 fs-3 widget-icon-box-avatar shadow">
                                            <i class="ri-shopping-bag-2-fill"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">

                    @if($msgPlano != "")
                    <div class="col-lg-12 mb-2">
                        <p class="text-danger">{{ $msgPlano }}</p>
                        <a href="{{ route('payment.index') }}" class="btn btn-success btn-lg pulse-success">Contratar Plano</a>
                    </div>
                    @endif

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Volume</h5>
                            </div>
                            <div class="card-body">
                                <h4>R$ <strong>{{ __moeda($totalEmitidoMes)}}</strong></h4>
                                <p>notas emitidas neste mês</p>
                                <h6>Emissões de NFe: <strong class="text-success">{{ $totalNfeCount }}</strong></h6>
                                <h6>Emissões de NFCe: <strong class="text-success">{{ $totalNfceCount }}</strong></h6>
                                <h6>Emissões de CTe: <strong class="text-success">{{ $totalCteCount }}</strong></h6>
                                <h6>Emissões de MDFe: <strong class="text-success">{{ $totalMdfeCount }}</strong></h6>

                            </div>
                        </div>

                        @if($empresa->plano)
                        <div class="card mt-2">
                            <div class="card-header">
                                <h5>Plano</h5>
                            </div>
                            <div class="card-body">
                                <h4>{{ $empresa->plano->plano->nome }}</h4>
                                <h6>Total de emissões NFe: <strong class="text-danger">{{ $empresa->plano->plano->maximo_nfes }}</strong></h6>
                                <h6>Total de emissões NFCe: <strong class="text-danger">{{ $empresa->plano->plano->maximo_nfces }}</strong></h6>
                                <h6>Total de emissões CTe: <strong class="text-danger">{{ $empresa->plano->plano->maximo_ctes }}</strong></h6>
                                <h6>Total de emissões MDFe: <strong class="text-danger">{{ $empresa->plano->plano->maximo_mdfes }}</strong></h6>

                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Grafico de emissão mensal (valores por dia)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="grafico-emissao-mes"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Grafico de emissão mensal (quantidade emitida)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="grafico-emissao-mes-contador"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Grafico de emissão últimos meses (valor mensal acumulado)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="grafico-emissao-ult-meses"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-4">Contas a Receber</h4>
                                <div dir="ltr">
                                    <!-- <div class="mt-3 chartjs-chart" style="height: 320px;"> -->
                                        <canvas id="conta-receber" style="width: 100%" data-colors="#4A4AFD, #B6D7A8, #B6D7A8"></canvas>
                                        <!-- </div> -->
                                    </div>
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-4">Contas a Pagar</h4>
                                    <div dir="ltr">
                                        <!-- <div class="mt-3 chartjs-chart" style="height: 320px;"> -->
                                            <canvas id="conta-pagar" data-colors="#4A4AFD, #B6D7A8, #B6D7A8"></canvas>
                                            <!-- </div> -->
                                        </div>
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Grafico de emissão mensal CTe (quantidade emitida)</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="grafico-emissao-mes-cte"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Grafico de emissão mensal MDFe (quantidade emitida)</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="grafico-emissao-mes-mdfe"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endsection

        @section('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script type="text/javascript">
            $(function() {
                setTimeout(() => {
                    buscaDadosGraficoMes()
                    buscaDadosGraficoMesContador()
                    buscaDadosUlitmosMeses()
                    contaReceber()
                    contaPagar()
                    buscaDadosGraficoMesCte()
                    buscaDadosGraficoMesMdfe()

                    dadosCards($("#inp-periodo").val())
                }, 10)
            })

            $(document).on("change", "#inp-periodo", function () {
                dadosCards($(this).val())
            })

            function dadosCards(periodo){
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/dados-cards", {
                    empresa_id: empresa_id,
                    periodo: periodo
                })
                .done((success) => {
                    // console.log(success)
                    $('.total-clientes').text(success['clientes'])
                    $('.total-produtos').text(success['produtos'])
                    $('.total-vendas').text("R$ " + convertFloatToMoeda(success['vendas']))
                    $('.total-compras').text("R$ " + convertFloatToMoeda(success['compras']))
                    $('.total-receber').text("R$ " + convertFloatToMoeda(success['contas_receber']))
                    $('.total-pagar').text("R$ " + convertFloatToMoeda(success['contas_pagar']))
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function buscaDadosGraficoMes() {
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/grafico-mes", {
                    empresa_id: empresa_id
                })
                .done((success) => {
                    iniciaGraficoMes(success)
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function buscaDadosGraficoMesContador() {
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/grafico-mes-contador", {
                    empresa_id: empresa_id
                })
                .done((success) => {
                    iniciaGraficoMesContador(success)
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function buscaDadosGraficoMesCte() {
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/grafico-mes-cte", {
                    empresa_id: empresa_id
                })
                .done((success) => {
                    iniciaGraficoMesCte(success)
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function buscaDadosGraficoMesMdfe() {
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/grafico-mes-mdfe", {
                    empresa_id: empresa_id
                })
                .done((success) => {
                    iniciaGraficoMesMdfe(success)
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function buscaDadosUlitmosMeses() {
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/grafico-ult-meses", {
                    empresa_id: empresa_id
                })
                .done((success) => {
                    iniciaGraficoUltMeses(success)
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function contaReceber() {
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/grafico-conta-receber", {
                    empresa_id: empresa_id
                })
                .done((success) => {
                    contaReceberTotal(success)
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function contaPagar() {
                let empresa_id = $('#empresa_id').val()

                $.get(path_url + "api/graficos/grafico-conta-pagar", {
                    empresa_id: empresa_id
                })
                .done((success) => {
                    contaPagarTotal(success)
                })
                .fail((err) => {
                    console.log(err)
                })
            }

            function iniciaGraficoMes(data) {
                const ctx = document.getElementById('grafico-emissao-mes');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: montaLabels(data),
                        datasets: [{
                            label: 'emissão',
                            data: montaValues(data),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function iniciaGraficoMesContador(data) {
                const ctx = document.getElementById('grafico-emissao-mes-contador');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: montaLabels(data),
                        datasets: [{
                            label: 'emissão',
                            data: montaValues(data),
                            borderWidth: 1,
                            borderColor: '#19AC65',
                            backgroundColor: '#19AC65'
                        }],

                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function iniciaGraficoMesCte(data) {
                const ctx = document.getElementById('grafico-emissao-mes-cte');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: montaLabels(data),
                        datasets: [{
                            label: 'emissão',
                            data: montaValues(data),
                            borderWidth: 1,
                            borderColor: '#19AC65',
                            backgroundColor: '#19AC65'
                        }],

                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function iniciaGraficoMesMdfe(data) {
                const ctx = document.getElementById('grafico-emissao-mes-mdfe');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: montaLabels(data),
                        datasets: [{
                            label: 'emissão',
                            data: montaValues(data),
                            borderWidth: 1,
                            borderColor: '#19AC65',
                            backgroundColor: '#19AC65'
                        }],

                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            function iniciaGraficoUltMeses(data) {
                const ctx = document.getElementById('grafico-emissao-ult-meses');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: montaLabels(data),
                        datasets: [{
                            label: 'emissão',
                            data: montaValues(data),
                            borderWidth: 1,
                            borderColor: '#FF6384',
                            backgroundColor: '#FF6384'
                        }],

                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function montaLabels(data) {
                let temp = []
                data.map((x) => {
                    temp.push(x.dia)
                })
                return temp
            }

            function montaValues(data) {
                let temp = []
                data.map((x) => {
                    temp.push(x.valor)
                })
                return temp
            }

            function montaValuesPendente(data) {
                let temp = []
                data.map((x) => {
                    temp.push(x.valorPendente)
                })
                return temp
            }

            function montaValuesQuitado(data) {
                let temp = []
                data.map((x) => {
                    temp.push(x.valorQuitado)
                })
                return temp
            }

            function contaReceberTotal(data) {
                var chartElement = document.getElementById('conta-receber');
                var dataColors = chartElement.getAttribute('data-colors');
                var colors = dataColors ? dataColors.split(",") : this.defaultColors
                var ctx = chartElement.getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: montaLabels(data),
                        datasets: [{
                            label: 'Valor a Receber',
                            data: montaValuesPendente(data),
                            fill: '-1',
                            backgroundColor: '#000000',
                        }, 
                        {
                            label: 'Valor Recebido',
                            data: montaValuesQuitado(data),
                            fill: '-1',
                            backgroundColor: '#6AA84F', 
                        }, 
                        {
                            label: 'Total',
                            data: montaValues(data),
                            fill: '0',
                            backgroundColor: '#1261A9',
                        }]
                    }, 
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            filler: {
                                propagate: true
                            }, 
                        }, 
                        interaction: {
                            intersect: true, 
                        }
                    }
                });
            }

            function contaPagarTotal(data) {
                var chartElement = document.getElementById('conta-pagar');
                var dataColors = chartElement.getAttribute('data-colors');
                var colors = dataColors ? dataColors.split(",") : this.defaultColors
                var ctx = chartElement.getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'line', 
                    data: {
                        labels: montaLabels(data), 
                        datasets: [{
                            label: 'Valor a Pagar', 
                            data: montaValuesPendente(data),
                            fill: '-1', 
                            backgroundColor: '#000000', 
                        }, 
                        {
                            label: 'Valor Pago', 
                            data: montaValuesQuitado(data),
                            fill: '-1',
                            backgroundColor: '#6AA84F', 
                        }, 
                        {
                            label: 'Total', 
                            data: montaValues(data),
                            fill: '0',
                            backgroundColor: '#1261A9',
                        }]
                    }, 
                    options: {
                        responsive: true, 
                        maintainAspectRatio: true, 
                        plugins: {
                            filler: {
                                propagate: true
                            }, 
                        }, 
                        interaction: {
                            intersect: true, 
                        }

                    }, 
                });
            }
        </script>
        @endsection
        @else

        @include('contador.home')
        @endif
