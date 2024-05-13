var DESCONTO = 0;
var VALORACRESCIMO = 0;
var PERCENTUALMAXDESCONTO = false;

$(function () {
    $("#inp-variacao_id").val('')
    $("#lista_id").val('')

    if($('#pedido_desconto').length){
        DESCONTO = $('#pedido_desconto').val()
        VALORACRESCIMO = $('#pedido_valor_entrega').val()
        $("#valor_desconto").text("R$ " + convertFloatToMoeda(DESCONTO));
        $("#valor_acrescimo").text("R$ " + convertFloatToMoeda(VALORACRESCIMO));

    }
    $('#mousetrapTitle').click(() => {
        $('#codBarras').focus()
    })
    $('#codBarras').focus(() => {
        $('#mousetrapTitle').css('display', 'none');
        $('.leitor_ativado').removeClass('d-none')
        $('.leitor_desativado').addClass('d-none')
    });
    $('#codBarras').focusout(() => {
        $('#mousetrapTitle').css('display', 'flex');
        $('.leitor_desativado').removeClass('d-none')
        $('.leitor_ativado').addClass('d-none')
    });

    validateButtonSave()
    calcTotal()
    $('#inp-tipo_pagamento').val('').change()
    $('#inp-tipo_pagamento_row').val('').change()
    $('#inp-valor_row').val('')
    $('#inp-data_vencimento_row').val('')
    $('#inp-valor_recebido').val('')
    $('#inp-troco').val('')
})

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
                lista_id: $('#lista_id').val(),
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

                if(parseFloat(v.valor_unitario) > 0){
                    o.text += ' R$ ' + convertFloatToMoeda(v.valor_unitario);
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

$('#codBarras').keyup((v) => {
    setTimeout(() => {
        let barcode = v.target.value
        if (barcode.length > 7) {
            $('#codBarras').val('')
            $.get(path_url + "api/produtos/findByBarcode",
            {
                barcode: barcode,
                empresa_id: $('#empresa_id').val(),
                lista_id: $('#lista_id').val()
            })
            .done((e) => {
                console.log(e)
                if (e.valor_unitario) {

                    var newOption = new Option(e.nome, e.id, false, false);
                    $('#inp-produto_id').html('')
                    $('#inp-produto_id').append(newOption);

                    // $("#inp-produto_id").append(new Option(e.nome, e.id));
                    $("#inp-quantidade").val("1,00");
                    $("#inp-variacao_id").val(e.codigo_variacao);
                    $("#inp-valor_unitario").val(convertFloatToMoeda(e.valor_unitario));
                    $("#inp-subtotal").val(convertFloatToMoeda(e.valor_unitario));
                    setTimeout(() => {
                        $('.btn-add-item').trigger('click')
                    }, 100)
                } else {
                    buscarPorReferencia(barcode)
                }
                setTimeout(() => {
                    $('#codBarras').focus()
                }, 10)
            })
            .fail((err) => {
                console.log(err);
            });
        }
    }, 500)
})

$('.cliente-venda').click(() => {
    let vl_cashback = convertMoedaToFloat($('#inp-valor_cashback').val())
    if(vl_cashback > 0){
        console.log("setar desc")
        DESCONTO = vl_cashback
        $("#valor_desconto").html(convertFloatToMoeda(DESCONTO));
        calcTotal();
    }
})

$('.btn-selecionar_cliente').click(() => {
    $('#inp-valor_cashback').val('')
    $('#inp-permitir_credito').val('1').change()
})

function buscarPorReferencia(barcode) {
    $.get(path_url + "api/produtos/findByBarcodeReference",
    {
        barcode: barcode,
        empresa_id: $('#empresa_id').val(),
        usuario_id: $('#usuario_id').val()
    })
    .done((e) => {
        $(".table-itens tbody").append(e);
        calcTotal();
    })
    .fail((e) => {
        console.log(e);
        swal("Erro", "Produto não localizado!", "error")
    });
}

var CashBackConfig = null
var valorCashBack = 0

$(document).on("change", "#inp-cliente_id", function () {
    $('.cashback-div').addClass('d-none')
    $('#inp-valor_cashback').val('')
    $('#inp-permitir_credito').val('1').change()
    let cliente_id = $(this).val()
    $.get(path_url + "api/clientes/cashback/" + cliente_id)
    .done((e) => {
        if(e){
            CashBackConfig = e
            valorCashBack = e.valor_cashback

            $('.cashback-div').removeClass('d-none')
            $('.info_cash_back').text('*percentual de cashback para uso ' + e.percentual_maximo_venda + '%')

        }
        $('.valor-cashback-disponivel').text('R$ ' + convertFloatToMoeda(e.valor_cashback))
    })
    .fail((e) => {
        $('.cashback-div').addClass('d-none')
        console.log(e);
    });
})

$('#inp-valor_cashback').blur(() => {
    validaCashBack()
})

function validaCashBack(){

    let valor_setado = $('#inp-valor_cashback').val()
    valor_setado = valor_setado.replace(",", ".")
    valor_setado = parseFloat(valor_setado)
    let total = convertMoedaToFloat($(".total-venda").text())
    if(total == 0){
        swal("Alerta", "Informe ao menos um produto para continuar", "warning")
        return;
    }
    if(CashBackConfig){
        let percentual_maximo_venda = CashBackConfig.percentual_maximo_venda
        let valor_maximo = total * (percentual_maximo_venda/100)
        console.log("valor_maximo", valor_maximo)
        console.log("percentual_maximo_venda", percentual_maximo_venda)

        if(valor_setado > valor_maximo){
            swal("Erro", "Valor máximo permitido R$ " + convertFloatToMoeda(valor_maximo), "warning")
            $('#inp-valor_cashback').val('')
        }else if(valor_setado > valorCashBack){
            swal("Erro", "Valor ultrapassou R$ " + convertFloatToMoeda(valorCashBack), "warning")
            $('#inp-valor_cashback').val('')
        }else{

        }
    }
}

$(function () {
    setTimeout(() => {
        $('#cat_todos').first().trigger('click')
    }, 100)
})

function selectCat(id) {
    $('#cat_todos').removeClass('active')
    $('.btn_cat').removeClass('active')
    $('.btn_cat_' + id).addClass('active')
    $.get(path_url + "api/produtos/findByCategory",
    {
        lista_id: $('#lista_id').val(),
        id: id
    })
    .done((e) => {
        $('.cards-categorias').html(e)
    })
    .fail((e) => {
        console.log(e);
    });
}

function todos() {

    $('#cat_todos').addClass('active')
    $('.btn_cat').removeClass('active')

    $.get(path_url + "api/produtos/all", { 
        empresa_id: $('#empresa_id').val(),
        lista_id: $('#lista_id').val(),
    })
    .done((e) => {

        $('.cards-categorias').html(e)
    })
    .fail((e) => {
        console.log(e);
    });
}

$(function () {
    setTimeout(() => {
        $("#inp-produto_id").change(() => {
            let product_id = $("#inp-produto_id").val();

            if (product_id) {
                let codigo_variacao = $("#inp-produto_id").select2('data')[0].codigo_variacao
                $.get(path_url + "api/produtos/findWithLista",
                { 
                    produto_id: product_id,
                    lista_id: $('#lista_id').val(),
                })
                .done((e) => {
                    if(e.variacao_modelo_id){
                        if(!codigo_variacao){
                            buscarVariacoes(product_id)
                        }else{

                            $.get(path_url + "api/variacoes/findById", {codigo_variacao: codigo_variacao})
                            .done((e) => {
                                $("#inp-variacao_id").val(codigo_variacao);
                                $("#inp-quantidade").val("1,00");
                                $("#inp-valor_unitario").val(convertFloatToMoeda(e.valor));
                                $("#inp-subtotal").val(convertFloatToMoeda(e.valor));
                            })
                            .fail((e) => {
                                console.log(e);
                            });
                        }
                    }else{
                        $("#inp-quantidade").val("1,00");
                        $("#inp-valor_unitario").val(convertFloatToMoeda(e.valor_unitario));
                        $("#inp-subtotal").val(convertFloatToMoeda(e.valor_unitario));
                    }
                })
                .fail((e) => {
                    console.log(e);
                });
            }
        })
    }, 100)

    $("body").on("blur", ".value_unit", function () {
        let qtd = $("#inp-quantidade").val();
        let value_unit = $(this).val();
        value_unit = convertMoedaToFloat(value_unit);
        qtd = convertMoedaToFloat(qtd);
        $("#inp-subtotal").val(convertFloatToMoeda(qtd * value_unit));
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
    $("#inp-quantidade").val("1,00");
    $("#inp-valor_unitario").val(convertFloatToMoeda(valor));
    $("#inp-subtotal").val(convertFloatToMoeda(valor));
    $("#inp-variacao_id").val(id);

    $('#modal_variacao').modal('hide')

    if(PRODUTOID != null){
        addItem()
    }
    
}

function addItem(){
    $.get(path_url + "api/produtos/findId/" + PRODUTOID)
    .done((res) => {
        console.log(res)
        var newOption = new Option(res.nome, res.id, false, false);
        $('#inp-produto_id').html('')
        $('#inp-produto_id').append(newOption);
        setTimeout(() => {
            $('.btn-add-item').trigger('click')
        }, 10)
    })
    .fail((err) => {
        console.log(err)
    })
    PRODUTOID = null
}

var PRODUTOID = null
function addProdutos(id) {
    let qtd = 0;
    $('.produto_row').each(function () {
        if(id == $(this).val()){
            qtd = $(this).next().next().next().find('input').val()
        }
    })
    setTimeout(() => {
        $.get(path_url + "api/frenteCaixa/linhaProdutoVendaAdd", {
            id: id, 
            qtd: qtd,
            lista_id: $('#lista_id').val()
        })
        .done((e) => {
            if (e == false) {
                swal("Atenção", "Produto com estoque insuficiente!", "warning");
            } else {
                let idDup = 0
                $(".produto_row").each(function () {
                    if($(this).val() == id){
                        idDup = $(this).val()
                    }
                })

                if(idDup == 0){
                    $(".table-itens tbody").append(e);
                }else{
                    // console.clear()
                    $(".table-itens tbody tr").each(function(){
                        if($(this).find('.produto_row').val() == id){
                            let qtdAnt = convertMoedaToFloat($(this).find('.qtd_row').val())
                            $(this).find('.qtd_row').val(convertFloatToMoeda(qtdAnt+1))
                        }
                    })
                }
                setTimeout(() => {
                    calcSubTotal()
                }, 20)

            }
        })
        .fail((e) => {
            PRODUTOID = id
            // console.log(e);
            if(e.status == 402){
                buscarVariacoes(id)
            }
        });
    }, 10);
}

$(".btn-add-item").click(() => {

    let qtd = $("#inp-quantidade").val();
    let value_unit = $("#inp-valor_unitario").val();
    value_unit = convertMoedaToFloat(value_unit);
    qtd = convertMoedaToFloat(qtd);
    $("#inp-subtotal").val(convertFloatToMoeda(qtd * value_unit));
    setTimeout(() => {
        let abertura = $('#abertura').val()

        if (abertura) {
            let qtd = $("#inp-quantidade").val();
            let value_unit = $("#inp-valor_unitario").val();
            let sub_total = $("#inp-subtotal").val();
            let product_id = $("#inp-produto_id").val();
            let variacao_id = $("#inp-variacao_id").val();

            // let key = $("#inp-key").val()
            $("#inp-variacao_id").val('')
            if (qtd && value_unit && product_id && sub_total) {

                let dataRequest = {
                    qtd: qtd,
                    value_unit: value_unit,
                    sub_total: sub_total,
                    product_id: product_id,
                    variacao_id: variacao_id,
                };

                //valida item duplicado
                let idDup = 0
                let qtdDup = 0
                $(".produto_row").each(function () {
                    console.log($(this).val())
                    console.log(product_id)
                    if($(this).val() == product_id){
                        console.log($(this).val())
                        idDup = product_id
                    }
                })

                $(".qtd_row").each(function () {
                    if(idDup == product_id){
                        qtdDup = convertMoedaToFloat($(this).val())
                    }
                })
                setTimeout(() => {

                    if(idDup == 0){
                        $.get(path_url + "api/frenteCaixa/linhaProdutoVenda", dataRequest)
                        .done((e) => {
                            if (e == false) {
                                swal(
                                    "Atenção",
                                    "Produto com estoque insuficiente!",
                                    "warning"
                                    );
                            } else {
                                $(".table-itens tbody").append(e);
                                calcTotal();
                            }
                        })
                        .fail((e) => {
                            console.log(e);
                        });
                    }else{
                        let nQtd = qtdDup + convertMoedaToFloat(qtd)
                        let dataRequest = {
                            qtd: nQtd,
                            product_id: idDup,
                        };
                        $.get(path_url + "api/produtos/valida-estoque", dataRequest)
                        .done((success) => {
                            console.log(success)
                            $(".table-itens tbody tr").each(function(){
                                $(this).find('.qtd_row').val(convertFloatToMoeda(nQtd))
                            })
                            setTimeout(() => {
                                calcSubTotal()
                            }, 20)
                        })
                        .fail((err) => {
                            console.log(err)
                            swal("Erro", err.responseJSON, "error")
                        })

                    }
                }, 100)
            } else {
                swal(
                    "Atenção",
                    "Informe corretamente os campos para continuar!",
                    "warning"
                    );
            }
        } else {
            swal(
                "Atenção",
                "Abra o caixa para continuar!",
                "warning"
                ).then(() => {
                    validaCaixa()
                })
            }
        }, 100);
});

function validaCaixa() {
    let abertura = $('#abertura').val()
    if (!abertura) {
        $('#modal-abrir_caixa').modal('show')
        return
    }
}




var total_venda = 0;
function calcTotal() {
    var total = 0;
    $(".subtotal-item").each(function () {
        total += convertMoedaToFloat($(this).val());
    });
    setTimeout(() => {
        total_venda = total;
        $(".total-venda").html(convertFloatToMoeda(total + parseFloat(VALORACRESCIMO) - parseFloat(DESCONTO)));
        $('#inp-valor_total').val(convertFloatToMoeda(total + parseFloat(VALORACRESCIMO) - parseFloat(DESCONTO)));
        $(".total-venda-modal").html(convertFloatToMoeda(total + VALORACRESCIMO - DESCONTO));
        $('#inp-valor_integral').val(convertFloatToMoeda(total_venda))

        $('#inp-quantidade').val('')
        $('#inp-valor_unitario').val('')
        $('#inp-produto_id').val('').change()
    }, 100);
}

$("#inp-valor_recebido").on("keyup", (event) => {
    // esconderTodasMoedas();
    // let t = total_venda;
    let v = $("#inp-valor_recebido").val();
    v = v.replace(",", ".");

    let troco = v - (total_venda - DESCONTO + VALORACRESCIMO);
    if (troco > 0) {
        $("#valor-troco").html(convertFloatToMoeda(troco));
        $("#inp-troco").val(convertFloatToMoeda(troco));
    } else {
        $("#valor-troco").html("0,00");
    }
});

$("body").on("click", "#btn-incrementa", function () {
    let inp = $(this).closest('div.input-group-append').prev()[0]
    if (inp.value) {
        let v = convertMoedaToFloat(inp.value)
        v += 1
        inp.value = convertFloatToMoeda(v)
        calcSubTotal()
    }
})

$("body").on("click", "#btn-subtrai", function () {
    let inp = $(this).closest('.input-group').find('input')[0]
    if (inp.value) {
        let v = convertMoedaToFloat(inp.value)
        v -= 1
        inp.value = convertFloatToMoeda(v)

        calcSubTotal()
    }
})

$(".table-itens").on('click', '.btn-delete-row', function () {
    $(this).closest('tr').remove();
    swal("Sucesso", "Produto removido!", "success")
    calcTotal()
});

function calcSubTotal(e) {

    $(".line-product").each(function () {
        $qtd = $(this).find('.qtd')[0]
        $value = $(this).find('.value-unit')[0]
        $sub = $(this).find('.subtotal-item')[0]

        let qtd = convertMoedaToFloat($qtd.value)
        let value = convertMoedaToFloat($value.value)
        if (qtd <= 0) {
            $(this).remove()
        } else {
            $sub.value = convertFloatToMoeda(qtd * value)
        }
    })
    setTimeout(() => {
        calcTotal()
    }, 10)
}

function setaDesconto() {
    if (total_venda == 0) {
        swal("Erro", "Total da venda é igual a zero", "warning");
    } else {
        swal({
            title: "Valor desconto?",
            text: "Ultilize ponto(.) ao invés de virgula!",
            content: "input",
            button: {
                text: "Ok",
                closeModal: false,
                type: "error",
            },
        }).then((v) => {
            if (v) {
                let desconto = v;
                if (desconto.substring(0, 1) == "%") {
                    let perc = desconto.substring(1, desconto.length);
                    DESCONTO = TOTAL * (perc / 100);
                    if (PERCENTUALMAXDESCONTO > 0) {
                        if (perc > PERCENTUALMAXDESCONTO) {
                            swal.close();
                            setTimeout(() => {
                                swal(
                                    "Erro",
                                    "Máximo de desconto permitido é de " +
                                    PERCENTUALMAXDESCONTO +
                                    "%",
                                    "error"
                                    );
                                $("#valor_desconto").html("0,00");
                            }, 500);
                        }
                    }
                    if (DESCONTO > 0) {
                        $("#valor_item").attr("disabled", "disabled");
                        $(".btn-mini-desconto").attr(
                            "disabled",
                            "disabled"
                            );
                    } else {
                        $("#valor_item").removeAttr("disabled");
                        $(".btn-mini-desconto").removeAttr("disabled");
                    }
                } else {
                    desconto = desconto.replace(",", ".");
                    DESCONTO = parseFloat(desconto);
                    if (PERCENTUALMAXDESCONTO > 0) {
                        let tempDesc =
                        (TOTAL * PERCENTUALMAXDESCONTO) / 100;
                        if (tempDesc < DESCONTO) {
                            swal.close();

                            setTimeout(() => {
                                swal(
                                    "Erro",
                                    "Máximo de desconto permitido é de R$ " +
                                    parseFloat(tempDesc),
                                    "error"
                                    );
                                $("#valor_desconto").html("0,00");
                            }, 500);
                        }
                    }
                    if (DESCONTO > 0) {
                        $("#valor_item").attr("disabled", "disabled");
                        $(".btn-mini-desconto").attr(
                            "disabled",
                            "disabled"
                            );
                    } else {
                        $("#valor_item").removeAttr("disabled");
                        $(".btn-mini-desconto").removeAttr("disabled");
                    }
                }
                if (desconto.length == 0) DESCONTO = 0;
                $("#valor_desconto").text("R$ " + convertFloatToMoeda(DESCONTO));
                calcTotal();
            }
            swal.close();
            $("#codBarras").focus();
        });
    }
}

function setaAcrescimo() {
    if (total_venda == 0) {
        swal("Erro", "Total da venda é igual a zero", "warning");
    } else {
        swal({
            title: "Valor acrescimo?",
            text: "Ultilize ponto(.) ao invés de virgula!",
            content: "input",
            button: {
                text: "Ok",
                closeModal: false,
                type: "error",
            },
        }).then((v) => {
            if (v) {
                let acrescimo = v;
                if (acrescimo > 0) {
                    DESCONTO = 0;
                    $("#valor_desconto").html(convertFloatToMoeda(DESCONTO));
                }
                let total = total_venda;
                if (acrescimo.substring(0, 1) == "%") {
                    let perc = acrescimo.substring(1, acrescimo.length);
                    VALORACRESCIMO = total * (perc / 100);
                } else {
                    acrescimo = acrescimo.replace(",", ".");
                    VALORACRESCIMO = parseFloat(acrescimo);
                }
                if (acrescimo.length == 0) VALORACRESCIMO = 0;
                calcTotal();
                VALORACRESCIMO = parseFloat(VALORACRESCIMO);
                $("#valor_acrescimo").text("R$ " + convertFloatToMoeda(VALORACRESCIMO));

                calcTotal();
                $("#codBarras").focus();
            }
            swal.close();
        });
    }
}


$("#inp-tipo_pagamento").change(() => {
    $("#ipn-valor_recebido").val();
    let tipo = $("#inp-tipo_pagamento").val();
    let cliente = $("#inp-cliente_id").val();
    if (tipo == '06' && cliente == null) {
        swal("Alerta", "Informe o cliente!", "warning")
        $('#inp-tipo_pagamento').val('').change()
        $(".div-vencimento").addClass('d-none');
    }

    if (tipo == '06' && cliente != null) {
        $(".div-vencimento").removeClass('d-none');
    } else {
        $(".div-vencimento").addClass('d-none');
    }

    if (tipo == "03" || tipo == "04") {
        $('#cartao_credito').modal('show')
        $(".div-vencimento").addClass('d-none');

    }
    if (tipo == "99") {
        $("#modal-pag-outros").modal("show");
        $(".div-vencimento").addClass('d-none');

    }
    if (tipo == "01") {
        $("#inp-valor_recebido").removeAttr("disabled");
        $("#finalizar-venda").attr("disabled", true);
        $("#finalizar-rascunho").attr("disabled", true);
        $("#finalizar-consignado").attr("disabled", true);
        $(".div-troco").removeClass('d-none');
        $(".div-vencimento").addClass('d-none');
    } else {
        $("#inp-valor_recebido").attr("disabled", "true");
        $(".div-troco").addClass('d-none');
        $("#finalizar-venda").removeAttr("disabled");
        $("#finalizar-rascunho").removeAttr("disabled");
        $("#finalizar-consignado").removeAttr("disabled");
    }

    validateButtonSave()
});

$("#inp-tipo_pagamento_row").change(() => {
    let cliente = $("#inp-cliente_id").val();
    let tipo = $("#inp-tipo_pagamento_row").val();
    if (tipo == '06') {
        if (cliente == null) {
            swal("Alerta", "Informe o cliente!", "warning")
            $('#inp-tipo_pagamento_row').val('').change()
        }
    }

})

$('#inp-valor_recebido').blur(() => {
    validateButtonSave()
})


function validateButtonSave() {
    $('#salvar_venda').attr("disabled", 1)

    let total = convertMoedaToFloat($(".total-venda").text())
    var tipo = $('#inp-tipo_pagamento').val()
    var tipo_row = $('#inp-tipo_pagamento_row').val()
    var valor_recebido = convertMoedaToFloat($('#inp-valor_recebido').val())

    if (total > 0 && tipo != null) {

        if (tipo == '01' && valor_recebido >= total) {
            $('#salvar_venda').removeAttr("disabled")
        }
        else if (tipo != '01') {
            $('#salvar_venda').removeAttr("disabled")
        }
        else if (tipo_row) {
            $('#salvar_venda').removeAttr("disabled")
        }
        else {
            $('#salvar_venda').attr("disabled", 1)
        }
    }
}

$(".modal-funcioario select").each(function () {

    let id = $(this).prop("id");

    if (id == "inp-funcionario_id") {

        $(this).select2({
            minimumInputLength: 2,
            language: "pt-BR",
            placeholder: "Digite para buscar o funcionário",
            theme: "bootstrap4",
            dropdownParent: $(this).parent(),
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
    }
});

$("#lista_precos select").each(function () {

    let id = $(this).prop("id");

    if (id == "inp-lista_preco_id") {

        $(this).select2({
            minimumInputLength: 2,
            language: "pt-BR",
            placeholder: "Digite para buscar a lista de preço",
            theme: "bootstrap4",
            dropdownParent: $(this).parent(),
            ajax: {
                cache: true,
                url: path_url + "api/lista-preco/pesquisa",
                dataType: "json",
                data: function (params) {
                    console.clear();
                    var query = {
                        pesquisa: params.term,
                        empresa_id: $("#empresa_id").val(),
                        tipo_pagamento_lista: $("#inp-tipo_pagamento_lista").val(),
                        funcionario_lista_id: $("#inp-funcionario_lista_id").val(),
                    };
                    return query;
                },
                processResults: function (response) {
                    console.log(response)
                    var results = [];

                    $.each(response, function (i, v) {
                        var o = {};
                        o.id = v.id;

                        o.text = v.nome + " " + v.percentual_alteracao + "%";
                        o.value = v.id;
                        results.push(o);
                    });
                    return {
                        results: results,
                    };
                },
            },
        });
    }
});

$("#cliente select").each(function () {
    let id = $(this).prop("id");
    if (id == "inp-cliente_id") {
        $(this).select2({
            minimumInputLength: 2,
            language: "pt-BR",
            placeholder: "Digite para buscar o cliente",
            width: "100%",
            theme: "bootstrap4",
            dropdownParent: $(this).parent(),
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
                        $('.cliente_selecionado').text(v.razao_social);

                    });
                    return {
                        results: results,
                    };
                },
            },
        });
    }
});

$(".btn-add-payment").click(() => {
    let tipo_pagamento_row = $("#inp-tipo_pagamento_row").val();
    let vencimento = $("#inp-data_vencimento_row").val();
    let valor_integral_row = $("#inp-valor_row").val();
    let obs_row = $("#inp-observacao_row").val();

    validateButtonSave();

    let v = convertMoedaToFloat(valor_integral_row);

    if (v + total_payment <= total_venda) {
        if (vencimento && valor_integral_row && tipo_pagamento_row) {
            let dataRequest = {
                data_vencimento_row: vencimento,
                valor_integral_row: valor_integral_row,
                obs_row: obs_row,
                tipo_pagamento_row: tipo_pagamento_row,
            };

            $.get(path_url + "api/frenteCaixa/linhaParcelaVenda", dataRequest)
            .done((e) => {
                $(".table-payment tbody").append(e);
                calcTotalPayment();

            })
            .fail((e) => {
                console.log(e);
            });
        } else {
            swal(
                "Atenção",
                "Informe corretamente os campos para continuar!",
                "warning"
                );
        }
    } else {
        swal(
            "Atenção",
            "A soma das parcelas não bate com o valor total da venda",
            "warning"
            );
    }
});


$(".pagamento_multiplo").click(() => {
    // let cliente = $("#inp-cliente_id").val();
    let count_itens = $(".table-itens tbody tr").length

    setTimeout(() => {
        if (count_itens == 0) {
            swal("Erro", "Adicione um produto!", "warning");
        }
        // if (cliente == null) {
        //     swal("Erro", "Adicione um cliente", "warning");
        // }
    }, 200)
})

var total_payment = 0;
function calcTotalPayment() {
    $('#btn-pag_row').attr("disabled", true)

    var total = 0;
    $(".valor_integral").each(function () {
        total += convertMoedaToFloat($(this).val());
    });
    setTimeout(() => {
        total_payment = total;
        $(".sum-payment").html("R$ " + convertFloatToMoeda(total));

        $(".sum-restante").html("R$ " + convertFloatToMoeda(total_venda - total));
    }, 100);

    let dif = total_venda - total;

    let diferenca = dif.toFixed(2);

    if (diferenca <= 10) {
        $("#btn-pag_row").removeAttr("disabled")
    }
}


$(".table-payment").on("click", ".btn-delete-row", function () {
    $(this).closest("tr").remove();
    swal("Sucesso", "Parcela removida!", "success");
    calcTotalPayment();
});


$.fn.serializeFormJSON = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

function selecionaLista(){
    let tipo_pagamento_lista = $('#inp-tipo_pagamento_lista').val()
    let funcionario_lista_id = $('#inp-funcionario_lista_id').val()
    let lista_preco_id = $('#inp-lista_preco_id').val()

    if(!lista_preco_id){
        swal("Alerta", "Selecione a lista", "warning")
        return;
    }

    if(tipo_pagamento_lista){
        $('#inp-tipo_pagamento').val(tipo_pagamento_lista).change()
    }
    if(funcionario_lista_id){
        $.get(path_url + "api/funcionarios/find", {id: funcionario_lista_id})
        .done((res) => {
            console.log(res)
            var newOption = new Option(res.nome, res.id, true, false);
            $('#inp-funcionario_id').append(newOption);
            $('.funcionario_selecionado').text(res.nome)

        })
        .fail((err) => {
            console.log(err);
        });
    }

    $('#lista_id').val(lista_preco_id)
    setTimeout(() => {
        todos()
    }, 10)
    setTimeout(() => {
        $("#codBarras").focus();
    }, 500)
}

$("body").on("change", "#inp-lista_preco_id", function () {
    $.get(path_url + "api/lista-preco/find", {id: $(this).val()})
    .done((res) => {
        console.log(res)
        $('#inp-tipo_pagamento_lista').val(res.tipo_pagamento).change()

        if(res.funcionario_id){
            $('#inp-funcionario_lista_id').val(res.funcionario_id).change();
        }
    })
    .fail((err) => {
        console.log(err);
    });
})

$('#btn_fiscal').click(() => {
    emitirNfce = true
    $("#form-pdv").submit()
})

var emitirNfce = false
$("#form-pdv").on("submit", function (e) {

    e.preventDefault();
    const form = $(e.target);
    var json = $(this).serializeFormJSON();

    json.empresa_id = $('#empresa_id').val()
    json.usuario_id = $('#usuario_id').val()

    json.desconto = convertMoedaToFloat($('#valor_desconto').text())
    json.acrescimo = convertMoedaToFloat($('#valor_acrescimo').text())
    console.log(">>>>>>>> salvando ", json);
    $.post(path_url + 'api/frenteCaixa/store', json)
    .done((success) => {

        if (emitirNfce == true) {
            gerarNfce(success)
        } else {
            swal("Sucesso", "Venda finalizada com sucesso, deseja imprimir o comprovante?", "success")

            swal({
                title: "Sucesso",
                text: "Venda finalizada com sucesso, deseja imprimir o comprovante?",
                icon: "success",
                buttons: true,
                buttons: ["Não", "Sim"],
                dangerMode: true,
            }).then((isConfirm) => {
                if (isConfirm) {
                    window.open(path_url + 'frontbox/imprimir-nao-fiscal/' + success.id, "_blank")
                } else {
                    // location.reload()
                }
                if($('#pedido_delivery_id').length){
                    location.href = '/pedidos-delivery';
                }else if($('#pedido_id').length){
                    location.href = '/pedidos-cardapio';
                }else{
                    location.reload()
                }
            });
        }
    }).fail((err) => {
        console.log(err)
    })
});

function gerarNfce(venda) {

    let empresa_id = $("#empresa_id").val();

    $.post(path_url + "api/nfce_painel/emitir", {
        id: venda.id,
    })
    .done((success) => {
        swal("Sucesso", "NFe emitida " + success.recibo + " - chave: [" + success.chave + "]", "success")
        .then(() => {
            window.open(path_url + 'nfce/imprimir/' + venda.id, "_blank")
            setTimeout(() => {
                location.reload()
            }, 100)
        })
    })
    .fail((err) => {
        console.log(err)

        swal("Algo deu errado", err.responseJSON, "error")

    })
}

function adicionaZero(numero) {
    if (numero <= 9)
        return "0" + numero;
    else
        return numero;
}
$(function () {
    let data = new Date
    let dataFormatada = (data.getFullYear() + "-" + adicionaZero((data.getMonth() + 1)) + "-" + adicionaZero(data.getDate()));
    $('.data_atual').val(dataFormatada)
})


$('.funcionario-venda').click(() => {
    let funcionario_id = $('#inp-funcionario_id').val()
    $.get(path_url + "api/funcionarios/find/", {id: funcionario_id})
    .done((e) => {
        $('.funcionario_selecionado').text(e.nome)
    })
    .fail((e) => {
        console.log(e);
    });
})


