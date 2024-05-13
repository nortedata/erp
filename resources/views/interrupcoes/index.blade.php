@extends('layouts.app', ['title' => 'Interrupção'])

@section('content')

<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <h4>Intervalos (ex: horário de almoço, horário de café)</h4>
                <hr>
                <a class="btn btn-success px-3" href="{{ route('interrupcoes.create') }}">
                    <i class="ri-add-circle-fill"></i>
                    Novo intervalo
                </a>
                <div class="table-responsive-sm mt-3">
                    <table class="table table-striped table-centered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Funcionário</th>
                                <th>Dia</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Motivo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->funcionario->nome }}</td>
                                <td>{{ \App\Models\DiaSemana::getDiaStr($item->dia_id) }}</td>
                                <td>{{ isset($item) ? $item->inicioParse : '--' }}</td>
                                <td>{{ isset($item) ? $item->finalParse : '--' }}</td>
                                <td>{{ $item->motivo }}</td>

                                <td>
                                    <form action="{{ route('interrupcoes.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" title="Deletar" class="btn btn-danger btn-delete btn-sm"><i class="ri-delete-bin-2-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nada encontrado</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {!! $data->appends(request()->all())->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
