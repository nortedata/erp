$('input[type=file]').change(() => {
    var filename = $('input[type=file]').val().replace(/.*(\/|\\)/, '');
    $('#filename').html(filename)
})

var mask = "00";

var SPMaskBehavior = function (val) {
    return val.replace(/\D/g, "").length === 11
    ? "(00) 00000-0000"
    : "(00) 0000-00009";
},
spOptions = {
    onKeyPress: function (val, e, field, options) {
        field.mask(SPMaskBehavior.apply({}, arguments), options);
    },
};

$(".fone").mask(SPMaskBehavior, spOptions);

var cpfMascara = function (val) {
    return val.replace(/\D/g, "").length > 11
    ? "00.000.000/0000-00"
    : "000.000.000-009";
},
cpfOptions = {
    onKeyPress: function (val, e, field, options) {
        field.mask(cpfMascara.apply({}, arguments), options);
    },
};

$(document).on("focus", ".cnpj", function () {
    $(this).mask("00.000.000/0000-00", { reverse: true })
});

$(document).on("focus", ".moeda", function () {
    $(this).mask("00000000,00", { reverse: true })
});

$(document).on("focus", ".coordenada", function () {
    $(this).mask("-00.0000000", {placeholder: "-11.1111111"})
});

$(document).on("focus", ".comissao", function () {
    $(this).mask("000,00", { reverse: true })
});

$(document).on("focus", ".timer", function () {
    $(this).mask("00:00", { reverse: true })
});

$(document).on("focus", ".qtd", function () {
    $(this).mask("00000000,00", { reverse: true })
});

$(document).on("focus", ".quantidade", function () {
    $(this).mask("0000000.000", { reverse: true })
});

$(document).on("focus", ".peso", function () {
    $(this).mask("00000000.000", { reverse: true })
});

$(document).on("focus", ".percentual", function () {
    $(this).mask("000.00", { reverse: true })
});

$(document).on("focus", ".cpf_cnpj", function () {
    $(this).mask(cpfMascara, cpfOptions);
});

$(document).on("focus", ".dimensao", function () {
    $(this).mask("00000.00", { reverse: true })
});
$(document).on("focus", ".peso", function () {
    $(this).mask("000000.000", { reverse: true })
});

$(function () {

    $(".cep").mask("00000-000", { reverse: true });
    $(".ncm").mask("0000.00.00", { reverse: true });
    $(".cest").mask("00.000.00", { reverse: true });
    $(".placa").mask("AAA-AAAA", { reverse: true });
    $(".cfop").mask("0000", { reverse: true });
    $(".ie").mask("0000000000", { reverse: true });

    $body = $("body");

    $(document).on({
        ajaxStart: function () {
            $body.addClass("loading");
        },
        ajaxStop: function () {
            $body.removeClass("loading");
        }
    });

    $("input[required], select[required], textarea[required]")
    .siblings("label")
    .addClass("required");

    $("input.tooltipp, select.tooltipp, textarea.tooltipp")
    .siblings("label")
    .append('<button type="button" class="btn btn-link btn-tooltip btn-sm" data-toggle="tooltip" data-placement="top" title="Tooltip on top"><i class="ri-file-info-fill"></i></button>')

    $(document).on("focus", "#chave_nfe", function () {
        $(this).mask("0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000", {
            reverse: true
        });
    });

    $("#datetime-datepicker2").flatpickr({ enableTime: !0, dateFormat: "Y-m-d H:i" })

    if($('.text-tooltip')){
        let texto = $('.text-tooltip').html()
        $('.btn-tooltip').prop('title', texto)
        $('.btn-tooltip').tooltip()
    }

    $("input.tooltipp2, select.tooltipp2, textarea.tooltipp2")
    .siblings("label")
    .append('<button type="button" class="btn btn-link btn-tooltip2 btn-sm" data-toggle="tooltip" data-placement="top" title="Tooltip on top"><i class="ri-file-info-fill"></i></button>')
    
    if($('.text-tooltip2')){
        let texto = $('.text-tooltip2').html()

        $('.btn-tooltip2').prop('title', texto)
        $('.btn-tooltip2').tooltip()
    }

    $("input.tooltipp3, select.tooltipp3, textarea.tooltipp3")
    .siblings("label")
    .append('<button type="button" class="btn btn-link btn-tooltip3 btn-sm" data-toggle="tooltip" data-placement="top" title="Tooltip on top"><i class="ri-file-info-fill"></i></button>')
    
    if($('.text-tooltip3')){
        let texto = $('.text-tooltip3').html()

        $('.btn-tooltip3').prop('title', texto)
        $('.btn-tooltip3').tooltip()
    }

    setTimeout(() => {
        notifications()
    }, 10)
    
});


function convertMoedaToFloat(value) {
    if (!value) {
        return 0;
    }

    var number_without_mask = value.replaceAll(".", "").replaceAll(",", ".");
    return parseFloat(number_without_mask.replace(/[^0-9\.]+/g, ""));
}

function convertFloatToMoeda(value) {
    value = parseFloat(value)
    return value.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

$(".btn-delete").on("click", function (e) {
    e.preventDefault();
    var form = $(this).parents("form").attr("id");
    
    swal({
        title: "Você está certo?",
        text: "Uma vez deletado, você não poderá recuperar esse item novamente!",
        icon: "warning",
        buttons: true,
        buttons: ["Cancelar", "Excluir"],
        dangerMode: true,
    }).then((isConfirm) => {
        if (isConfirm) {

            document.getElementById(form).submit();
        } else {
            swal("", "Este item está salvo!", "info");
        }
    });
});

$(".btn-confirm").on("click", function (e) {
    e.preventDefault();
    var form = $(this).parents("form").attr("id");
    swal({
        title: "Você está certo?",
        text: "Uma vez alterado, você não poderá voltar o estado desse item!",
        icon: "warning",
        buttons: true,
        buttons: ["Cancelar", "OK"],
        dangerMode: true,
    }).then((isConfirm) => {
        if (isConfirm) {
            document.getElementById(form).submit();
        } else {
            swal("", "Este item não foi alterado", "info");
        }
    });
});

$(".select2").select2({
    // theme: "bootstrap4",
    width: $(this).data("width")
    ? $(this).data("width")
    : $(this).hasClass("w-100")
    ? "100%"
    : "style",
    placeholder: $(this).data("placeholder"),
    allowClear: Boolean($(this).data("allow-clear")),
});

$("#inp-cidade_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar a cidade",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/buscaCidades",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.info;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-mercado_livre_categoria").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar a categoria do anúncio",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/mercadolivre/get-categorias",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v._id;

                o.text = v.nome;
                o.value = v._id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-ncm").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o NCM",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/ncm",
        dataType: "json",
        data: function (params) {
            console.clear();

            var query = {
                pesquisa: params.term,
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.codigo;
                if(v.codigo.length != 10){
                    o.disabled = 1;
                }

                o.text = v.descricao
                o.value = v.codigo;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-empresa").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar a empresa",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/empresas/find-all",
        dataType: "json",
        data: function (params) {

            var query = {
                pesquisa: params.term,
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.info;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-servico_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o seviço",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/servicos",
        dataType: "json",
        data: function (params) {
            let empresa_id = $('#empresa_id').val()
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: empresa_id
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome + ' R$ ' + convertFloatToMoeda(v.valor);
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-produto_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o produto",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/produtos",
        dataType: "json",
        data: function (params) {
            let empresa_id = $('#empresa_id').val()
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: empresa_id
            };
            return query;
        },
        processResults: function (response) {
            var results = [];
            let compra = 0
            if($('#is_compra') && $('#is_compra').val() == 1){
                compra = 1
            }

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                if(v.codigo_variacao){
                    o.codigo_variacao = v.codigo_variacao
                }

                o.text = v.nome
                if(compra == 0){
                    if(parseFloat(v.valor_unitario) > 0){
                        o.text += ' R$ ' + convertFloatToMoeda(v.valor_unitario);
                    }
                }else{
                    o.text += ' R$ ' + convertFloatToMoeda(v.valor_compra);
                }
                if(v.codigo_barras){
                    o.text += ' [' + v.codigo_barras  + ']';
                }
                o.value = v.id;
                console.log(o)
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-produto_composto_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o produto composto",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/produtos-composto",
        dataType: "json",
        data: function (params) {
            let empresa_id = $('#empresa_id').val()
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: empresa_id
            };
            return query;
        },
        processResults: function (response) {
            var results = [];
            let compra = 0
            if($('#is_compra') && $('#is_compra').val() == 1){
                compra = 1
            }

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                if(v.codigo_variacao){
                    o.codigo_variacao = v.codigo_variacao
                }

                o.text = v.nome
                if(compra == 0){
                    if(parseFloat(v.valor_unitario) > 0){
                        o.text += ' R$ ' + convertFloatToMoeda(v.valor_unitario);
                    }
                }else{
                    o.text += ' R$ ' + convertFloatToMoeda(v.valor_compra);
                }
                if(v.codigo_barras){
                    o.text += ' [' + v.codigo_barras  + ']';
                }
                o.value = v.id;
                console.log(o)
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-empresa_contador_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar a empresa",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/empresas",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {

                var o = {};
                o.id = v.id;
                o.text = v.info
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-ingrediente_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o produto",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/produtos",
        dataType: "json",
        data: function (params) {
            let empresa_id = $('#empresa_id').val()
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: empresa_id
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-funcionario_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o funcionário",
    theme: "bootstrap4",

    ajax: {
        cache: true,
        url: path_url + "api/funcionarios/pesquisa",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: $("#empresa_id").val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-cliente_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o cliente",
    theme: "bootstrap4",

    ajax: {
        cache: true,
        url: path_url + "api/clientes/pesquisa",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: $("#empresa_id").val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.razao_social + " - " + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$("#inp-cliente_delivery_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o cliente",
    theme: "bootstrap4",

    ajax: {
        cache: true,
        url: path_url + "api/clientes/pesquisa-delivery",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: $("#empresa_id").val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.razao_social + " - " + v.telefone;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$(".cliente_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o cliente",
    theme: "bootstrap4",

    ajax: {
        cache: true,
        url: path_url + "api/clientes/pesquisa",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: $("#empresa_id").val(),
            };

            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.razao_social + " - " + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});


$("#inp-fornecedor_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o fornecedor",
    theme: "bootstrap4",

    ajax: {
        cache: true,
        url: path_url + "api/fornecedores/pesquisa",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: $("#empresa_id").val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.razao_social + " - " + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});


$('.button-toggle-menu').on("click", function () {

    $.post(path_url+'api/usuarios/set-sidebar',{ usuario_id: $('#usuario_id').val() })
    .done((success) => {
    })
    .fail((err) => {
        console.log(err)
    })
})

$('.btn-add-tr').on("click", function () {
    console.clear()
    var $table = $(this)
    .closest(".row")
    .prev()
    .find(".table-dynamic");

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
    $table.append($clone);
    setTimeout(function () {
        $("tbody select.select2").select2({
            language: "pt-BR",
            width: "100%",
            theme: "bootstrap4"
        });
    }, 100);

})

$(document).delegate(".btn-remove-tr", "click", function (e) {
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

function notifications(){

    if($('#empresa_id').val()){
        $.get(path_url + "api/notificacoes-alertas", {empresa_id: $('#empresa_id').val()})
        .done((success) => {
            $('.spinner-border').addClass('d-none')
            if(success.length > 0){
                $('.noti-icon-badge').removeClass('d-none')
            }
            $('.alertas-main').html(success)
        })
        .fail((err) => {
            $('.spinner-border').addClass('d-none')

        })
    }else{
        if($('#usuario_id').val()){

            $.get(path_url + "api/notificacoes-alertas-super", {usuario_id: $('#usuario_id').val()})
            .done((success) => {
                $('.spinner-border').addClass('d-none')
                if(success.length > 0){
                    $('.noti-icon-badge').removeClass('d-none')
                }
                $('.alertas-main').html(success)
            })
            .fail((err) => {
                $('.spinner-border').addClass('d-none')

            })
            $('.spinner-border').addClass('d-none')
        }
    }
}

