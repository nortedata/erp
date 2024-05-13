@extends('layouts.app', ['title' => 'Manifesto'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                    <h4>Nova Consulta</h4>

                    <p id="aguarde" class="text-info d-none">
                        <a id="btn-enviar" class="btn btn-success spinner-white spinner spinner-right">
                            Consultado novos documentos, aguarde ...
                        </a>
                    </p>
                    <p id="sem-resultado" style="display: none" class="center-align text-danger">Nenhum novo resultado...</p>
                    <div class="col-xl-12" id="table" style="display: none">
                        <a href="{{ route('manifesto.index') }}" class="btn btn-info">
                            <i class="la la-undo"></i>
                            Voltar para os documentos
                        </a>
                        <div class="table-responsive">
                            <table class="table table-striped table-centered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF/CNPJ</th>
                                        <th>Valor</th>
                                        <th>Chave</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript">


</script>
<script type="text/javascript" src="/js/dfe.js"></script>
@endsection


@endsection

