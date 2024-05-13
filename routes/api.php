<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/cidadePorNome/{nome}', 'HelperController@cidadePorNome');
Route::get('/cidadePorCodigoIbge/{codigo}', 'HelperController@cidadePorCodigoIbge');
Route::get('/cidadePorId/{id}', 'HelperController@cidadePorId');
Route::get('/buscaCidades', 'HelperController@buscaCidades');

Route::middleware(['valid'])->group(function () {
    Route::group(['prefix' => 'nfe'], function () {
        Route::post('/emitir', 'NFeController@emitir');
        Route::post('/xml-temporario', 'NFeController@xmlTemporario');
        Route::post('/danfe-temporario', 'NFeController@danfeTemporario');
        Route::post('/consultar', 'NFeController@consultar');
        Route::post('/corrigir', 'NFeController@corrigir');
        Route::post('/cancelar', 'NFeController@cancelar');
        Route::post('/inutilizar', 'NFeController@inutilizar');
        Route::post('/gerarNfe', 'NFeController@gerarNfe');
    });
});

Route::middleware(['validNfce'])->group(function () {
    Route::group(['prefix' => 'nfce'], function () {
        Route::post('/emitir', 'NFCeController@emitir');
        Route::post('/xml-temporario', 'NFCeController@xmlTemporario');
        Route::post('/cancelar', 'NFCeController@cancelar');
        Route::post('/consultar', 'NFCeController@consultar');
        Route::post('/inutilizar', 'NFCeController@inutilizar');
        // Route::post('/gerarNfce', 'NFCeController@gerarNfce');
    });
});

Route::post('/nfce/gerarVenda', 'NFCeController@gerarVenda');
Route::post('/nfce/gerarNfce', 'NFCeController@gerarNfce');


Route::middleware(['validaCTe'])->group(function () {
    Route::group(['prefix' => 'cte'], function () {
        Route::post('/emitir', 'CTeController@emitir');
        Route::post('/xml-temporario', 'CTeController@xmlTemporario');
        Route::post('/dacte-temporario', 'CTeController@dacteTemporario');

        Route::post('/cancelar', 'CTeController@cancelar');
        Route::post('/consultar', 'CTeController@consultar');
    });
});

//grupo para emissão painel
Route::group(['prefix' => 'nfe_painel'], function () {
    Route::post('/emitir', 'NFePainelController@emitir')->middleware('validaNFe');
    Route::post('/cancelar', 'NFePainelController@cancelar');
    Route::post('/corrigir', 'NFePainelController@corrigir');
    Route::post('/consultar', 'NFePainelController@consultar');
    Route::post('/inutilizar', 'NFePainelController@inutilizar');
    Route::post('/consulta-status-sefaz', 'NFePainelController@consultaStatusSefaz');
});

Route::group(['prefix' => 'nfce_painel'], function () {
    Route::post('/emitir', 'NFCePainelController@emitir')->middleware('validaNFCe');
    Route::post('/cancelar', 'NFCePainelController@cancelar');
    Route::post('/consultar', 'NFCePainelController@consultar');
    Route::post('/consulta-status-sefaz', 'NFCePainelController@consultaStatusSefaz');
});

Route::group(['prefix' => 'cte_painel'], function () {
    Route::post('/emitir', 'CTePainelController@emitir')->middleware('validaCTe');
    Route::post('/cancelar', 'CTePainelController@cancelar');
    Route::post('/corrigir', 'CTePainelController@corrigir');
    Route::post('/consultar', 'CTePainelController@consultar');
});

Route::group(['prefix' => 'cte_os_painel'], function () {
    Route::post('/emitir', 'CTeOsPainelController@emitir')->middleware('validaCTe');
    Route::post('/cancelar', 'CTeOsPainelController@cancelar');
    Route::post('/corrigir', 'CTeOsPainelController@corrigir');
    Route::post('/consultar', 'CTeOsPainelController@consultar');
});

Route::group(['prefix' => 'mdfe_painel'], function () {
    Route::post('/emitir', 'MDFePainelController@emitir')->middleware('validaMDFe');
    Route::post('/cancelar', 'MDFePainelController@cancelar');
    Route::post('/corrigir', 'MDFePainelController@corrigir');
    Route::post('/consultar', 'MDFePainelController@consultar');
});

Route::group(['prefix' => 'mdfe'], function () {
    Route::get('/linhaInfoDescarregamento', 'MdfeController@linhaInfoDescarregamento');
    Route::get('/vendas-aprovadas', 'MdfeController@vendasAprovadas');
    Route::post('/cancelar', 'MdfeController@cancelar');
});

Route::group(['prefix' => 'graficos'], function () {
    Route::get('/grafico-mes', 'GraficoController@graficoMes');
    Route::get('/grafico-mes-contador', 'GraficoController@graficoMesContador');
    Route::get('/grafico-ult-meses', 'GraficoController@graficoUltMeses');
    Route::get('/grafico-conta-receber', 'GraficoController@graficoContaReceber');
    Route::get('/grafico-conta-pagar', 'GraficoController@graficoContaPagar');
    Route::get('/grafico-mes-cte', 'GraficoController@graficoMesCte');
    Route::get('/grafico-mes-mdfe', 'GraficoController@graficoMesMdfe');
    Route::get('/dados-cards', 'GraficoController@dadosDards');
});

Route::group(['prefix' => 'cardapio'], function () {
    Route::get('/switch-categoria', 'ProdutoCardapioController@switchCategoria');
});

Route::group(['prefix' => 'produtos-delivery'], function () {
    Route::get('/switch-categoria', 'ProdutoDeliveryController@switchCategoria');
});

Route::group(['prefix' => 'produtos-ecommerce'], function () {
    Route::get('/switch-categoria', 'ProdutoEcommerceController@switchCategoria');
});

Route::get('/paymentStatus/{id}', 'PaymentController@status');

Route::group(['prefix' => 'empresas'], function () {
    Route::get('/', 'EmpresaController@pesquisa');
    Route::get('/find-all', 'EmpresaController@findAll');
});

Route::group(['prefix' => 'servicos'], function () {
    Route::get('/', 'ServicoController@pesquisa');
    Route::get('/find/{id}', 'ServicoController@find');
});

Route::group(['prefix' => 'variacoes'], function () {
    Route::get('/modelo', 'VariacaoController@modelo');
    Route::get('/find', 'VariacaoController@find');
    Route::get('/findById', 'VariacaoController@findById');
});

Route::get('/produtos-composto', 'ProdutoController@pesquisaCompostos');
Route::group(['prefix' => 'produtos'], function () {
    Route::get('/', 'ProdutoController@pesquisa');
    Route::get('/cardapio', 'ProdutoController@pesquisaCardapio');
    Route::get('/delivery', 'ProdutoController@pesquisaDelivery');
    Route::get('/find', 'ProdutoController@find');
    Route::get('/findId/{id}', 'ProdutoController@findId');
    Route::get('/findWithLista', 'ProdutoController@findWithLista');
    Route::get('/padrao', 'ProdutoController@padrao');
    Route::get('/findByCategory', 'ProdutoController@findByCategory');
    Route::get('/all', 'ProdutoController@all');

    Route::get('/get-pizzas', 'ProdutoController@getPizzas');
    Route::get('/calculo-pizza', 'ProdutoController@calculoPizza');

    Route::get('/findByBarcode', 'ProdutoController@findByBarcode');
    Route::get('/findByBarcodeReference', 'ProdutoController@findByBarcodeReference');
    Route::get('/info-vencimento/{id}', 'ProdutoController@infoVencimento');
    Route::get('/valida-estoque', 'ProdutoController@validaEstoque');

});

Route::group(['prefix' => 'nfse'], function () {
    Route::post('/transmitir', 'NotaServicoController@transmitir');
    Route::post('/consultar', 'NotaServicoController@consultar');
    Route::post('/cancelar', 'NotaServicoController@cancelar');
});

Route::group(['prefix' => 'ncm'], function () {
    Route::get('/', 'NcmController@pesquisa');
    Route::get('/valida', 'NcmController@valida');
    Route::get('/carregar', 'NcmController@carregar');
});

Route::group(['prefix' => 'usuarios'], function () {
    Route::post('/set-sidebar', 'UserController@setSidebar');
});

Route::group(['prefix' => 'clientes'], function () {
    Route::get('/find/{id}', 'ClienteController@find');
    Route::get('/cashback/{id}', 'ClienteController@cashback');
    Route::get('/pesquisa', 'ClienteController@pesquisa');
    Route::get('/pesquisa-delivery', 'ClienteController@pesquisaDelivery');
});

Route::group(['prefix' => 'motoboys'], function () {
    Route::get('/calc-comissao', 'MotoboyController@calcComissao');
});

Route::group(['prefix' => 'fornecedores'], function () {
    Route::get('/find/{id}', 'FornecedorController@find');
    Route::get('/pesquisa', 'FornecedorController@pesquisa');
});

Route::group(['prefix' => 'funcionarios'], function () {
    Route::get('/pesquisa', 'FuncionarioController@pesquisa');
    Route::get('/find', 'FuncionarioController@find');
});

Route::group(['prefix' => 'lista-preco'], function () {
    Route::get('/pesquisa', 'ListaPrecoController@pesquisa');
    Route::get('/find', 'ListaPrecoController@find');
});

Route::group(['prefix' => 'transportadoras'], function () {
    Route::get('/find/{id}', 'TransportadoraController@find');
});

Route::group(['prefix' => 'interrupcao'], function () {
    Route::post('/store-motivo', 'InterrupcaoController@storeMotivo');
});

Route::group(['prefix' => 'conta-receber'], function () {
    Route::get('/recorrencia', 'ContaReceberController@recorrencia');
});

Route::group(['prefix' => 'conta-pagar'], function () {
    Route::get('/recorrencia', 'ContaPagarController@recorrencia');
});

Route::group(['prefix' => 'ecommerce'], function () {
    Route::get('/calcular-frete', 'EcommerceController@calcularFrete');
    Route::get('/valida-email', 'EcommerceController@validaEmail');
    Route::get('/consulta-pix', 'EcommerceController@consultaPix');
    Route::get('/variacao', 'EcommerceController@variacao');
});

Route::group(['prefix' => 'frenteCaixa'], function () {
    Route::get('/linhaProdutoVenda', 'FrontBoxController@linhaProdutoVenda');
    Route::get('/linhaProdutoVendaAdd', 'FrontBoxController@linhaProdutoVendaAdd');
    Route::get('/linhaParcelaVenda', 'FrontBoxController@linhaParcelaVenda');
    Route::post('/store', 'FrontBoxController@store');
    Route::get('/buscaFuncionario/{id}', 'FrontBoxController@buscaFuncionario');
});

Route::group(['prefix' => 'manifesto'], function () {
    Route::post('/novos-documentos', 'ManifestoController@novosDocumentos');
});

Route::group(['prefix' => 'mercadolivre'], function () {
    Route::get('/get-categorias', 'MercadoLivreController@getCategorias');
    Route::get('/get-tipo-publicacao', 'MercadoLivreController@getTiposPublicacao');
});

Route::get('/notificacoes-pedido', 'NotificacaoController@index');
Route::get('/notificacoes-delivery', 'NotificacaoController@delivery');
Route::get('/notificacoes-ecommerce', 'NotificacaoController@ecommerce');
Route::post('/notificacoes-set-status', 'NotificacaoController@setStatus');
Route::get('/notificacoes-alertas', 'NotificacaoController@alertas');
Route::get('/notificacoes-alertas-super', 'NotificacaoController@alertaSuper');

Route::group(['prefix' => 'ordemServico'], function () {
    Route::get('/linhaServico', 'OrdemServicoController@linhaServico');
    Route::get('/linhaProduto', 'OrdemServicoController@linhaProduto');
    Route::get('/find/{id}', 'OrdemServicoController@find');
    Route::get('/findProduto/{id}', 'OrdemServicoController@findProduto');
    Route::get('/findFuncionario/{id}', 'OrdemServicoController@findFuncionario');
    Route::get('/linhaFuncionario', 'OrdemServicoController@linhaFuncionario');
});

Route::group(['prefix' => 'agendamentos'], function () {
    Route::get('/buscar-horarios', 'AgendamentoController@buscarHorarios');
    Route::post('/verificaDia', 'AgendamentoController@verificaDia');
});

Route::group(['prefix' => 'funcionamentos'], function () {
    Route::get('/diasDoFuncionario', 'FuncionamentoController@diasDoFuncionario');
});

Route::group(['prefix' => 'pedidos'], function () {
    Route::get('/itens-pendentes', 'PedidoController@itensPendentes');
});

Route::post('/cardapio-set-config', 'CardapioController@setConfig');
Route::get('/get-tipos-pagamento', 'CardapioController@tiposDePagamento');

Route::middleware(['authCardapio'])->group(function () {
    Route::group(['prefix' => 'app-cardapio'], function () {
        Route::get('/get-categorias', 'CardapioController@categorias');
        Route::get('/get-categoria/{id}', 'CardapioController@categoria');
        Route::get('/get-produto/{id}', 'CardapioController@produto');
        Route::post('/get-ingredientes', 'CardapioController@ingredientes');
        Route::get('/get-produtos-pesquisa', 'CardapioController@pesquisa');
        Route::get('/get-destaques', 'CardapioController@destaques');
        Route::get('/get-config', 'CardapioController@config');
        Route::post('/store-pedido', 'CardapioController@storePedido');
        Route::post('/store-mesa', 'CardapioController@storeMesa');
        Route::get('/get-conta', 'CardapioController@conta');
        Route::post('/call-garcom', 'CardapioController@chamarGarcom');
        Route::post('/finalizar-conta', 'CardapioController@finalizarConta');
        Route::get('/pedido-emAtendimento', 'CardapioController@emAtendimento');
        Route::get('/tamanhos-pizza', 'CardapioController@tamanhosPizza');
    });
});

Route::group(['prefix' => 'pre-venda'], function () {
    Route::get('/finalizar/{id}', 'PreVendaController@finalizar');
});

Route::group(['prefix' => 'delivery-link'], function () {
    Route::get('/cupom', 'Delivery\\HelperController@cupom');
    Route::get('/valida-fone', 'Delivery\\HelperController@validaFone');
    Route::post('/cliente-store', 'Delivery\\HelperController@clienteStore');
    Route::get('/set-endereco', 'Delivery\\HelperController@setEndereco');
    Route::get('/hash-pizzas', 'Delivery\\HelperController@hashPizzas');
    Route::get('/valor-pizza', 'Delivery\\HelperController@valorPizza');

    Route::post('/store-order-pix', 'Delivery\\HelperController@storePix');
    Route::get('/consulta-pix', 'Delivery\\HelperController@consultaPix');
    Route::get('/consulta-pedido', 'Delivery\\HelperController@consultaPedido');

});

//rotas de delivery
Route::middleware(['authDelivery'])->group(function () {
    Route::group(['prefix' => 'delivery'], function(){
        Route::get('/categorias', 'Delivery\\ProdutoController@all');
        Route::get('/produto/{id}', 'Delivery\\ProdutoController@find');
        Route::get('/config', 'Delivery\\ConfigController@index');
        Route::get('/cupom', 'Delivery\\ConfigController@cupom');

        Route::post('/endereco-save', 'Delivery\\ClienteController@enderecoSave');
        Route::post('/endereco-update', 'Delivery\\ClienteController@enderecoUpdate');
        Route::post('/update-endereco-padrao', 'Delivery\\ClienteController@updateEnderecoPadrao');

        Route::post('/login', 'Delivery\\ClienteController@login');
        Route::post('/send-code', 'Delivery\\ClienteController@sendCode');
        Route::post('/refresh-code', 'Delivery\\ClienteController@refreshCode');
        Route::post('/cliente-save', 'Delivery\\ClienteController@clienteSave');
        Route::post('/cliente-update', 'Delivery\\ClienteController@clienteUpdate');
        Route::post('/cliente-update-senha', 'Delivery\\ClienteController@clienteUpdateSenha');
        Route::get('/find-cliente', 'Delivery\\ClienteController@findCliente');
        Route::post('/pedido-save', 'Delivery\\PedidoController@save');

        Route::get('/adicionais', 'Delivery\\ProdutoController@adicionais');
        Route::get('/carrossel', 'Delivery\\ProdutoController@carrossel');
        Route::get('/bairros', 'Delivery\\ConfigController@bairros');
        Route::post('/gerar-qrcode', 'Delivery\\PedidoController@gerarQrcode');
        Route::post('/status-pix', 'Delivery\\PedidoController@consultaPix');
        Route::post('/ultimo-pedido-confirmar', 'Delivery\\PedidoController@ultimoPedidoParaConfirmar');
        Route::post('/consulta-pedido-lido', 'Delivery\\PedidoController@consultaPedidoLido');

    });
});

Route::post('/nfse-webhook', 'NfseWebHookController@index');

Route::group(['prefix' => 'pdv'], function () {
    Route::post('/login', 'PDV\\LoginController@login');
    Route::post('/produtos', 'PDV\\ProdutoController@produtos');
    Route::post('/categorias', 'PDV\\ProdutoController@categorias');
    Route::post('/clientes', 'PDV\\ClienteController@all');
});



