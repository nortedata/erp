<div class="row">
    <div class="col-md-4">
        {!!Form::select('produto_id', 'Produto')
        ->attrs(['class' => 'form-select'])->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('quantidade', 'Quantidade')
        ->attrs(['class' => 'quantidade'])->required()
        !!}
    </div>
    <input name="produto_variacao_id" id="produto_variacao_id" type="hidden">
    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>
@include('modals._variacao')

@section('js')
<script type="text/javascript">
    $(function(){
        $('#produto_variacao_id').val('')
    })

    $(document).on("change", "#inp-produto_id", function () {
        $('#produto_variacao_id').val('')

        let product_id = $(this).val()
        $.get(path_url + "api/produtos/find", 
        { 
            produto_id: product_id
        })
        .done((e) => {
            console.log(e)
            let codigo_variacao = $(this).select2('data')[0].codigo_variacao

            if(e.variacao_modelo_id && !codigo_variacao){
                buscarVariacoes(product_id)
            }

            if(codigo_variacao > 0){
                $('#produto_variacao_id').val(codigo_variacao)
            }
        })
        .fail((err) => {
            console.log(err)
        })
    })

    function buscarVariacoes(produto_id){
        $.get(path_url + "api/variacoes/find", { produto_id: produto_id })
        .done((res) => {
            $('#modal_variacao .modal-body').html(res)
            $('#modal_variacao').modal('show')
        })
        .fail((err) => {
            console.log(err)
            swal("Algo deu errado", "Erro ao buscar variações", "error")
        })
    }

    function selecionarVariacao(id, descricao, valor){
        $('#produto_variacao_id').val(id)
        $('#modal_variacao').modal('hide')
    }
</script>
@endsection