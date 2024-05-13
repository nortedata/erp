
@section('css')
<style type="text/css">
    .image-variation{
        width: 180px;
        height: 100px;
        margin-top: 10px;
        border-radius: 10px;
    }
</style>
@endsection
<div class="row g-2">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')
        ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('valor_compra', 'Valor de Compra')
        ->required()
        ->value(isset($item) ? __moeda($item->valor_compra) : '')
        ->attrs(['class' => 'moeda'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('valor_unitario', 'Valor de Venda')
        ->required()
        ->value(isset($item) ? __moeda($item->valor_unitario) : '')
        ->attrs(['class' => 'moeda'])
        !!}
    </div>

    <div class="col-md-2">
        <label class="form-label">Código de barras</label>
        <div class="input-group input-group-merge" style="margin-top: -8px">
            <input type="text" name="codigo_barras" value="{{ isset($item) ? $item->codigo_barras : old('codigo_barras') }}" id="codigo_barras" class="form-control">
            <div class="input-group-text">
                <span class="ri-barcode-box-line" onclick="gerarCode()"></span>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        {!!Form::tel('referencia', 'Referência')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('categoria_id', 'Categoria', ['' => 'Selecione'] + $categorias->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('marca_id', 'Marca', ['' => 'Selecione'] + $marcas->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('gerenciar_estoque', 'Gerenciar estoque', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('estoque_minimo', 'Estoque Mínimo')
        ->attrs(['data-mask' => '00000.00', 'data-mask-reverse' => 'true'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('alerta_validade', 'Alerta de Validade (dias)')
        ->attrs(['data-mask' => '000'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('referencia_balanca', 'Referência Balança')
        ->attrs()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('status', 'Ativo', ['1' => 'Sim', '0' => 'Não'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('padrao_id', 'Padrão de tributação', ['' => 'Selecione'] + $padroes->pluck('descricao', 'id')->all())
        ->attrs(['class' => 'form-select'])
        ->value(isset($item) ? $item->padrao_id : ($padraoTributacao != null ? $padraoTributacao->id : ''))
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('ncm', 'NCM')
        ->required()
        ->options(isset($item) ? [$item->ncm => $item->_ncm->descricao] : [])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('cest', 'CEST')
        ->attrs(['class' => 'cest'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('unidade', 'Unidade', App\Models\Produto::unidadesMedida())
        ->required()
        ->attrs(['class' => 'form-select'])
        ->value(isset($item) ? $item->unidade_compra : 'UN')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('perc_icms', '% ICMS')
        ->attrs(['class' => 'percentual'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('perc_pis', '% PIS')
        ->required()
        ->attrs(['class' => 'percentual'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('perc_cofins', '% COFINS')
        ->required()
        ->attrs(['class' => 'percentual'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('perc_ipi', '% IPI')
        ->required()
        ->attrs(['class' => 'percentual'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('perc_red_bc', '% Red BC')
        ->attrs(['class' => 'percentual'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('origem', 'Origem', App\Models\Produto::origens())
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::select('cst_csosn', 'CST/CSOSN', $listaCTSCSOSN)
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('cst_pis', 'CST PIS', App\Models\Produto::listaCST_PIS_COFINS())
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('cst_cofins', 'CST COFINS', App\Models\Produto::listaCST_PIS_COFINS())
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('cst_ipi', 'CST IPI', App\Models\Produto::listaCST_IPI())
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::select('cEnq', 'Código de enquandramento de IPI', App\Models\Produto::listaCenqIPI())
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('cfop_estadual', 'CFOP Estadual')
        ->required()
        ->attrs(['class' => 'cfop'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('cfop_outro_estado', 'CFOP Inter Estadual')
        ->required()
        ->attrs(['class' => 'cfop'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('cfop_entrada_estadual', 'CFOP Entrada Estadual')
        ->required()
        ->attrs(['class' => 'cfop'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('cfop_entrada_outro_estado', 'CFOP Entrada Inter Estadual')
        ->required()
        ->attrs(['class' => 'cfop'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('codigo_beneficio_fiscal', 'Código benefício')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('composto', 'Composto', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('variavel', 'Com variações', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select'])
        ->value((isset($item) && $item->variacao_modelo_id != null) ? 1 : 0)
        !!}
    </div>

    <div class="col-12 div-variavel">
        <div class="table-responsive">
            <table class="table table-dynamic">
                <thead class="table-dark">
                    <tr>
                        <th>Variação</th>
                        <th>Valores da variação</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="200px">
                            {!!Form::select('variacao_modelo_id', '', ['' => 'Selecione'] + $variacoes->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'form-select'])
                            ->value(isset($item) ? $item->variacao_modelo_id : null)
                            !!}
                        </td>
                        <td>
                            <div class="row">
                                <table class="table table-dynamic table-variacao">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Descrição</th>
                                            <th>Valor</th>
                                            <th>Código de barras</th>
                                            <th>Referência</th>
                                            <th>Imagem</th>
                                            <th>

                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @isset($item)
                                        @foreach($item->variacoes as $v)
                                        <tr class="dynamic-form">
                                            <input type="hidden" name="variacao_id[]]]" value="{{ $v->id }}">
                                            <td>
                                                <input type="text" class="form-control" name="descricao_variacao[]" value="{{ $v->descricao }}" required readonly>
                                            </td>
                                            <td>
                                                <input type="tel" class="form-control moeda" name="valor_venda_variacao[]" value="{{ __moeda($v->valor) }}" required>
                                            </td>

                                            <td>
                                                <input type="tel" class="form-control ignore" name="codigo_barras_variacao[]" value="{{ $v->codigo_barras }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control ignore" name="referencia_variacao[]" value="{{ $v->referencia }}">
                                            </td>
                                            <td>
                                                <input class="ignore" accept="image/*" type="file" class="form-control" name="imagem_variacao[]" value="">
                                                <img src="{{ $v->img }}" class="image-variation"><br>
                                                <span>imagem atual</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-remove-tr-variacao">
                                                    <i class="ri-subtract-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="row col-12 col-lg-3 mt-3">
                                <button type="button" class="btn btn-dark btn-add-tr-variacao">
                                    <i class="ri-add-fill"></i>
                                    Adicionar linha
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12"></div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="inp-petroleo" @isset($item) @if($item->codigo_anp != '') checked @endif @endif> <strong>Derivado do petróleo</strong>
                        </div>
                    </h4>
                </div>
                <div class="card-body div-petroleo d-none m-card" style="margin-top: -40px">

                    <div class="row">

                        <div class="col-md-4">
                            {!!Form::select('codigo_anp', 'ANP', ['' => 'Selecione'] + App\Models\Produto::listaAnp())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>

                        <div class="col-md-1">
                            {!!Form::tel('perc_glp', '%GLP')
                            ->attrs(['class' => 'percentual'])
                            !!}
                        </div>
                        <div class="col-md-1">
                            {!!Form::tel('perc_gnn', '%GNN')
                            ->attrs(['class' => 'percentual'])
                            !!}
                        </div>
                        <div class="col-md-1">
                            {!!Form::tel('perc_gni', '%GNI')
                            ->attrs(['class' => 'percentual'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('valor_partida', 'Valor de partida')
                            ->attrs(['class' => 'moeda'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::text('unidade_tributavel', 'Un. tributável')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('quantidade_tributavel', 'Qtd. tributável')
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::tel('adRemICMSRet', 'Alíquota ad rem do imposto retido')
                            ->attrs(['data-mask' => '00,0000'])
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::tel('pBio', 'Indice de mistura do Biodiesel')
                            ->attrs(['class' => 'percentual'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::tel('pOrig', '% de origem')
                            ->attrs(['class' => 'percentual'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('indImport', 'Indicador de importação',
                            [ 0 => 'Não', 1 => 'Sim']
                            )
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::select('cUFOrig', 'UF de origem do produtor ou do importador', ['' => 'Selecione'] + App\Models\Cidade::getEstadosCodigo())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>
                        @isset($cardapio)
                        @if($cardapio == 1)
                        <input type="hidden" name="redirect_cardapio" value="1">
                        @endif
                        @endif
                        <div class="form-check form-switch form-checkbox-danger">
                            <input type="checkbox" name="cardapio" class="form-check-input" id="inp-cardapio" @isset($item) @if($item->cardapio) checked @endif @endif @isset($cardapio) @if($cardapio == 1) checked @endif @endif ><strong>Cardápio</strong>
                        </div>
                    </h4>
                </div>
                <div class="card-body div-cardapio d-none m-card" style="margin-top: -40px">

                    <div class="row">

                        <div class="col-md-2">
                            {!!Form::tel('valor_cardapio', 'Valor de Cardápio')
                            ->value((isset($item) && $item->valor_cardapio > 0) ? __moeda($item->valor_cardapio) : '')
                            ->attrs(['class' => 'moeda'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('tempo_preparo', 'Tempo de preparo (minutos)')
                            ->attrs(['data-mask' => '000'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('tipo_carne', 'Escolher ponto da carne', ['0' => 'Não', '1' => 'Sim'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        @if(__isInternacionalizar(Auth::user()->empresa))
                        <div class="col-md-3">
                            {!!Form::text('nome_en', 'Nome (em inglês)')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::text('nome_es', 'Nome (em espanhol)')
                            !!}
                        </div>
                        @endif

                        <div class="col-md-12">
                            {!!Form::tel('descricao', 'Descrição')
                            ->value(isset($item) ? $item->descricao_pt : '')
                            ->attrs(['class' => ''])
                            !!}
                        </div>

                        @if(__isInternacionalizar(Auth::user()->empresa))
                        <div class="col-md-12">
                            {!!Form::tel('descricao_en', 'Descrição (em inglês)')
                            ->value(isset($item) ? $item->descricao_en : '')
                            ->attrs(['class' => ''])
                            !!}
                        </div>

                        <div class="col-md-12">
                            {!!Form::tel('descricao_es', 'Descrição (em espanhol)')
                            ->value(isset($item) ? $item->descricao_es : '')
                            ->attrs(['class' => ''])
                            !!}
                        </div>

                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>
                        @isset($delivery)
                        @if($delivery == 1)
                        <input type="hidden" name="redirect_delivery" value="1">
                        @endif
                        @endif
                        <div class="form-check form-switch form-checkbox-success">
                            <input type="checkbox" name="delivery" class="form-check-input" id="inp-delivery" @isset($item) @if($item->delivery) checked @endif @endif @isset($delivery) @if($delivery == 1) checked @endif @endif ><strong>Delivery/MarketPlace</strong>
                        </div>
                    </h4>
                </div>

                <div class="card-body div-delivery d-none m-card" style="margin-top: -40px">

                    <div class="row">

                        <div class="col-md-2">
                            {!!Form::tel('valor_delivery', 'Valor de Deivery')
                            ->value((isset($item) && $item->valor_delivery > 0) ? __moeda($item->valor_delivery) : '')
                            ->attrs(['class' => 'moeda'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('destaque_delivery', 'Destaque', ['0' => 'Não', '1' => 'Sim'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-12">
                            {!!Form::textarea('texto_delivery', 'Descrição')
                            !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($configMercadoLivre && $configMercadoLivre->access_token)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>
                        @isset($mercadolivre)
                        @if($mercadolivre == 1)
                        <input type="hidden" name="redirect_mercadolivre" value="1">
                        @endif
                        @endif
                        <div class="form-check form-switch form-checkbox-warning">
                            <input type="checkbox" name="mercadolivre" class="form-check-input" id="inp-mercadolivre" @isset($item) @if($item->mercado_livre_id != null) checked @endif @endif @isset($mercadolivre) @if($mercadolivre == 1) checked @endif @endif ><strong>Mercado livre</strong>
                        </div>
                    </h4>
                </div>

                <div class="card-body div-mercadolivre d-none m-card" style="margin-top: -40px">

                    <div class="row">

                        <div class="col-md-2">
                            {!!Form::tel('mercado_livre_valor', 'Valor do anúcio')
                            ->value((isset($item) && $item->mercado_livre_valor > 0) ? __moeda($item->mercado_livre_valor) : '')
                            ->attrs(['class' => 'moeda input-ml'])
                            !!}
                        </div>

                        <div class="col-md-4">
                            {!!Form::select('mercado_livre_categoria', 'Categoria do anúcio')
                            ->attrs(['class' => 'form-select select2 input-ml'])
                            ->options((isset($item) && $item->mercado_livre_categoria) ? 
                            [$item->mercado_livre_categoria => $item->categoriaMercadoLivre->nome] : [])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('condicao_mercado_livre', 'Condição do item', ['new' => 'Novo', 'used' => 'Usado', 'not_specified' => 'Não especificado'])
                            ->attrs(['class' => 'form-select input-ml'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('quantidade_mercado_livre', 'Quantidade disponível')
                            ->attrs(['data-mask' => '00000', 'class' => 'input-ml'])
                            ->value((isset($item) && $item->estoque) ? number_format($item->estoque->quantidade,0) : '')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('mercado_livre_tipo_publicacao', 'Tipo publicação')
                            ->attrs(['class' => 'select2 input-ml'])
                            !!}
                        </div>

                        <input type="hidden" id="tipo_publicacao_hidden" value="{{ $item ? $item->mercado_livre_tipo_publicacao : '' }}">

                        <div class="col-md-6">
                            {!!Form::text('mercado_livre_youtube', 'Link do youtube')
                            ->attrs(['class' => ''])
                            !!}
                        </div>

                        <div class="col-md-12">
                            {!!Form::textarea('mercado_livre_descricao', 'Descrição')
                            ->attrs(['rows' => '12'])
                            !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>
                        @isset($ecommerce)
                        @if($ecommerce == 1)
                        <input type="hidden" name="redirect_ecommerce" value="1">
                        @endif
                        @endif
                        <div class="form-check form-switch form-checkbox-info">
                            <input type="checkbox" name="ecommerce" class="form-check-input" id="inp-ecommerce" @isset($item) @if($item->ecommerce) checked @endif @endif @isset($ecommerce) @if($ecommerce == 1) checked @endif @endif ><strong>Ecommerce</strong>
                        </div>
                    </h4>
                </div>

                <div class="card-body div-ecommerce d-none m-card" style="margin-top: -40px">

                    <div class="row">

                        <div class="col-md-2">
                            {!!Form::tel('valor_ecommerce', 'Valor de Ecommerce')
                            ->value((isset($item) && $item->valor_ecommerce > 0) ? __moeda($item->valor_ecommerce) : '')
                            ->attrs(['class' => 'moeda'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('percentual_desconto', '% de desconto')
                            ->attrs(['class' => 'percentual'])
                            !!}
                        </div>

                        <div class="col-md-8">
                            {!!Form::text('descricao_ecommerce', 'Descrição curta')
                            ->attrs(['class' => ''])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('largura', 'Largura')
                            ->attrs(['class' => 'dimensao'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::tel('comprimento', 'Comprimento')
                            ->attrs(['class' => 'dimensao'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::tel('altura', 'Altura')
                            ->attrs(['class' => 'dimensao'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::tel('peso', 'Peso')
                            ->attrs(['class' => 'peso'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('destaque_ecommerce', 'Destaque', ['0' => 'Não', '1' => 'Sim'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-12">
                            {!!Form::textarea('texto_ecommerce', 'Descrição longa')
                            !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card col-md-3 mt-3 form-input">
        <div class="preview">
            <button type="button" id="btn-remove-imagem" class="btn btn-link-danger btn-sm btn-danger">x</button>
            @isset($item)
            <img id="file-ip-1-preview" src="{{ $item->img }}">
            @else
            <img id="file-ip-1-preview" src="/imgs/no-image.png">
            @endif
        </div>
        <label for="file-ip-1">Imagem</label>
        @isset($item)
        <a class="btn btn-danger btn-sm w-50 mt-2 mb-1" href="{{ route('produtos.remove-image', [$item->id])}}">
            <i class="ri-close-line"></i>
            Remover imagem
        </a>
        @endif
        <input type="file" id="file-ip-1" name="image" accept="image/*" onchange="showPreview(event);">
    </div>
    <hr class="mt-4">
    @if(!isset($not_submit))
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
    @endif
</div>
@if(!isset($not_submit))
@section('js')

<script type="text/javascript" src="/js/produto.js"></script>
@endsection
@endif
