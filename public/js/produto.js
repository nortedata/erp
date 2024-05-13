$(function() {
    changeDericadoPetroleo()
    changeCardapio()
    changeDelivery()
    changeVariavel()
    changeEcommerce()
    changeMeradoLivre()

    if($('.table-variacao tbody tr').length == 0){
        $('#inp-variacao_modelo_id').val('').change()
    }

    setTimeout(() => {
        if($('#inp-padrao_id').val()){
            $('#inp-padrao_id').change()
        }
    }, 100)
})

function gerarCode() {
    $.get(path_url + "produtos-gerar-codigo-ean")
    .done((res) => {
        $('#codigo_barras').val(res)
    })
    .fail((err) => {
        swal("Erro", "Erro ao buscar código", "error")
    })
}

$(document).on("change", "#inp-padrao_id", function() {
    let padrao = $(this).val()
    if (padrao) {
        $.get(path_url + "api/produtos/padrao", {
            padrao: padrao
        })
        .done((result) => {

            var newOption = new Option(result._ncm.descricao, result._ncm.codigo, 1, false);
            $('#inp-ncm').append(newOption);

            // $('#inp-ncm').val(result.ncm)
            $('#inp-cest').val(result.cest)
            $('#inp-perc_icms').val(result.perc_icms)
            $('#inp-perc_pis').val(result.perc_pis)
            $('#inp-perc_cofins').val(result.perc_cofins)
            $('#inp-perc_ipi').val(result.perc_ipi)
            $('#inp-cst_csosn').val(result.cst_csosn).change()
            $('#inp-cst_pis').val(result.cst_pis).change()
            $('#inp-cst_cofins').val(result.cst_cofins).change()
            $('#inp-cst_ipi').val(result.cst_ipi).change()
            $('#inp-cEnq').val(result.cEnq).change()
            $('#inp-cfop_estadual').val(result.cfop_estadual)
            $('#inp-cfop_outro_estado').val(result.cfop_outro_estado)
            $('#inp-codigo_beneficio_fiscal').val(result.codigo_beneficio_fiscal)

            $('#inp-cfop_entrada_estadual').val(result.cfop_entrada_estadual)
            $('#inp-cfop_entrada_outro_estado').val(result.cfop_entrada_outro_estado)
        })
        .fail((err) => {
            console.log(err)
        })
    }
});

function changeDericadoPetroleo() {
    let check = $('#inp-petroleo').is(':checked')
    if (check) {
        $('.div-petroleo').removeClass('d-none')
    } else {
        $('.div-petroleo').addClass('d-none')
    }
}

$('#inp-petroleo').change(() => {
    changeDericadoPetroleo()
})

function changeCardapio() {
    let check = $('#inp-cardapio').is(':checked')
    if (check) {
        $('.div-cardapio').removeClass('d-none')
    } else {
        $('.div-cardapio').addClass('d-none')
    }
}

$('#inp-cardapio').change(() => {
    changeCardapio()
})

function changeDelivery() {
    let check = $('#inp-delivery').is(':checked')
    if (check) {
        $('.div-delivery').removeClass('d-none')
    } else {
        $('.div-delivery').addClass('d-none')
    }
}

$('#inp-delivery').change(() => {
    changeDelivery()
})

function changeEcommerce() {
    let check = $('#inp-ecommerce').is(':checked')
    if (check) {
        $('.div-ecommerce').removeClass('d-none')
    } else {
        $('.div-ecommerce').addClass('d-none')
    }
}

$('#inp-ecommerce').change(() => {
    changeEcommerce()
})

function changeMeradoLivre() {
    let check = $('#inp-mercadolivre').is(':checked')
    if (check) {
        $('.div-mercadolivre').removeClass('d-none')
        $('.input-ml').attr('required', 1)
        getTiposPublicacao()
    } else {
        $('.div-mercadolivre').addClass('d-none')
        $('.input-ml').removeAttr('required')
    }
}

function getTiposPublicacao(){

    $.get(path_url + "api/mercadolivre/get-tipo-publicacao", {
        empresa_id: $('#empresa_id').val()
    })
    .done((res) => {

        $('#inp-mercado_livre_tipo_publicacao').html('')
        var newOption = new Option('Selecione', '', false, false);
        $('#inp-mercado_livre_tipo_publicacao').append(newOption);
        res.map((x) => {
            var newOption = new Option(x.name, x.id, false, false);
            $('#inp-mercado_livre_tipo_publicacao').append(newOption);
        })

        setTimeout(() => {
            $('#inp-mercado_livre_tipo_publicacao').val($('#tipo_publicacao_hidden').val()).change()
        }, 100)
    })
    .fail((err) => {
        console.log(err)
        swal("Erro", "Algo deu errado", "error")
    })
}
$('#inp-mercadolivre').change(() => {
    changeMeradoLivre()
})

$(document).ready(function() {
    $("form").bind("keypress", function(e) {
        if (e.keyCode == 13) {
            return false;
        }
    });
});

function changeVariavel() {
    let variavel = $('#inp-variavel').val()
    if (variavel == 1) {
        $('.div-variavel').removeClass('d-none')
        $('#inp-valor_unitario').val('0')
        $('#inp-valor_compra').val('0')
    } else {
        $('.div-variavel').addClass('d-none')
    }
}

$('#inp-variavel').change(() => {
    changeVariavel()
})

// variacoes

$(document).on("change", "#inp-variacao_modelo_id", function () {

    let variacao_modelo_id = $(this).val()
    if(variacao_modelo_id){
        $.get(path_url + "api/variacoes/modelo", {
            variacao_modelo_id: variacao_modelo_id
        })
        .done((res) => {
            $('.table-variacao tbody').html(res)
        })
        .fail((err) => {
            console.log(err)
            swal("Erro", "Algo deu errado", "error")
        })
    }
})

$(document).delegate(".btn-remove-tr-variacao", "click", function (e) {
    e.preventDefault();
    swal({
        title: "Você esta certo?",
        text: "Deseja remover esse item mesmo?",
        icon: "warning",
        buttons: true
    }).then(willDelete => {
        if (willDelete) {
            var trLength = $(this)
            .closest("tr")
            .closest("tbody")
            .find("tr")
            .not(".dynamic-form-document").length;
            if (!trLength || trLength > 1) {
                $(this)
                .closest("tr")
                .remove();
            } else {
                swal("Atenção", "Você deve ter ao menos um item na lista", "warning");
            }
        }
    });
});


$('.btn-add-tr-variacao').on("click", function () {
    console.clear()
    var $table = $(this)
    .closest(".row")
    .prev()
    .find(".table-variacao");

    console.log($table)

    var hasEmpty = false;

    $table.find("input, select").each(function () {
        if (($(this).val() == "" || $(this).val() == null) && $(this).attr("type") != "hidden" && $(this).attr("type") != "file" && !$(this).hasClass("ignore")) {
            hasEmpty = true;
        }
    });

    if (hasEmpty) {
        swal(
            "Atenção",
            "Preencha todos os campos antes de adicionar novos.",
            "warning"
            );
        return;
    }
    // $table.find("select.select2").select2("destroy");
    var $tr = $table.find(".dynamic-form").first();
    $tr.find("select.select2").select2("destroy");
    var $clone = $tr.clone();
    $clone.show();

    $clone.find("input,select").val("");
    $clone.find("input,select").removeAttr('readonly');
    $table.append($clone);
    setTimeout(function () {
        $("tbody select.select2").select2({
            language: "pt-BR",
            width: "100%",
            theme: "bootstrap4"
        });
    }, 100);

})
