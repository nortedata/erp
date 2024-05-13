<div class="row">
    <div class="col-md-12">
        @isset($item)
        @if($item->fornecedor)
        @php
        $isCompra = 1;
        @endphp
        @endif
        @endif
        @isset($isCompra)
        <input type="hidden" id="is_compra" name="is_compra" value="1">
        @endif

        @isset($isOrdemServico)
        <input type="hidden" name="ordem_servico_id" value="{{$item->id}}">
        @endif

        @isset($isPedidoEcommerce)
        <input type="hidden" name="pedido_ecommerce_id" value="{{$item->id}}">
        @endif

        @isset($cotacao)
        <input type="hidden" name="cotacao_id" value="{{$cotacao->id}}">
        @endif

        <ul class="nav nav-tabs nav-primary" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#cliente" role="tab" aria-selected="true">
                    <div class="d-flex align-items-center">
                        <div class="tab-icon"><i class='fa fa-user me-2'></i>
                        </div>
                        <div class="tab-title">
                            <i class="ri-file-user-fill"></i>
                            @isset($isCompra) Fornecedor @else Cliente @endif
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#produtos" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-icon"><i class='fa fa-shopping-cart me-2'></i>
                        </div>
                        <div class="tab-title">
                            <i class="ri-box-2-line"></i>
                            Produtos
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#transportadora" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-icon"><i class='fa fa-truck me-2'></i>
                        </div>
                        <div class="tab-title">
                            <i class="ri-truck-line"></i>
                            Frete
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#fatura" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-icon"><i class='fa fa-money-bill me-2'></i>
                        </div>
                        <div class="tab-title">
                            <i class="ri-coins-line"></i>
                            Fatura
                        </div>
                    </div>
                </a>
            </li>
        </ul>
        <hr>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="cliente" role="tabpanel">
                <div class="card">
                    @if(!isset($isCompra))
                    <div class="row m-3">
                        <div class="col-md-5">
                            {!!Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2 cliente_id'])->options(isset($item) ? [$item->cliente_id => $item->cliente->razao_social] : [])
                            !!}
                        </div>
                        <hr class="mt-3">
                        <div class="row d-cliente">
                            <div class="col-md-3">
                                {!!Form::text('cliente_nome', 'Razão Social')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->cliente->razao_social : '')
                                !!}
                            </div>
                            <div class="col-md-3">
                                {!!Form::text('nome_fantasia', 'Nome Fantasia')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->cliente->nome_fantasia : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('cliente_cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required()
                                ->value(isset($item) ? $item->cliente->cpf_cnpj : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::text('ie', 'IE')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->cliente->ie : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('telefone', 'Fone')->attrs(['class' => 'fone'])
                                ->value(isset($item) ? $item->cliente->telefone : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::select('contribuinte', 'Contribuinte', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])
                                ->value(isset($item) ? $item->cliente->contribuinte : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::select('consumidor_final', 'Consumidor Final', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])->required()
                                ->value(isset($item) ? $item->cliente->consumidor_final : '')
                                !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                {!!Form::text('email', 'E-mail')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->cliente->email : '')
                                !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Cidade</label>
                                <select required class="form-control select2 cidade_id" name="cliente_cidade" id="inp-cidade_cliente">
                                    <option value="">Selecione..</option>
                                    @foreach ($cidades as $c)
                                    <option @isset($item) @if($item->cliente->cidade_id == $c->id) selected @endif @endisset value="{{$c->id}}">{{$c->nome}} - {{$c->uf}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::text('cliente_rua', 'Rua')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->cliente->rua : '')
                                !!}
                            </div>
                            <div class="col-md-1 mt-3">
                                {!!Form::text('cliente_numero', 'Número')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->cliente->numero : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::text('cep', 'CEP')->attrs(['class' => 'cep'])->required()
                                ->value(isset($item) ? $item->cliente->cep : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::text('cliente_bairro', 'Bairro')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->cliente->bairro : '')
                                !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                {!!Form::text('complemento', 'Complemento')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->cliente->complemento : '')
                                !!}
                            </div>
                        </div>
                    </div>

                    @else

                    <div class="row m-3">
                        <div class="col-md-5">
                            @isset($cotacao)
                            {!!Form::select('fornecedor_id', 'Fornecedor')->attrs(['class' => 'select2 fornecedor_id'])->options([$cotacao->fornecedor_id => $cotacao->fornecedor->razao_social])
                            !!}
                            @else
                            {!!Form::select('fornecedor_id', 'Fornecedor')->attrs(['class' => 'select2 fornecedor_id'])->options(isset($item) ? [$item->fornecedor_id => $item->fornecedor->razao_social] : [])
                            !!}
                            @endif
                        </div>
                        <hr class="mt-3">
                        <div class="row d-cliente">
                            <div class="col-md-3">
                                {!!Form::text('fornecedor_nome', 'Razão Social')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->fornecedor->razao_social : '')
                                !!}
                            </div>
                            <div class="col-md-3">
                                {!!Form::text('nome_fantasia', 'Nome Fantasia')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->fornecedor->nome_fantasia : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('fornecedor_cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required()
                                ->value(isset($item) ? $item->fornecedor->cpf_cnpj : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::text('ie', 'IE')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->fornecedor->ie : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('telefone', 'Fone')->attrs(['class' => 'fone'])
                                ->value(isset($item) ? $item->fornecedor->telefone : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::select('contribuinte', 'Contribuinte', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])
                                ->value(isset($item) ? $item->fornecedor->contribuinte : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::select('consumidor_final', 'Consumidor Final', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])->required()
                                ->value(isset($item) ? $item->fornecedor->consumidor_final : '')
                                !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                {!!Form::text('email', 'E-mail')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->fornecedor->email : '')
                                !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="">Cidade</label>
                                <select required class="form-control select2 cidade_id" name="fornecedor_cidade" id="inp-fornecedor_cidade">
                                    <option value="">Selecione..</option>
                                    @foreach ($cidades as $c)
                                    <option @isset($item) @if($item->fornecedor->cidade_id == $c->id) selected @endif @endisset value="{{$c->id}}">{{$c->nome}} - {{$c->uf}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::text('fornecedor_rua', 'Rua')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->fornecedor->rua : '')
                                !!}
                            </div>
                            <div class="col-md-1 mt-3">
                                {!!Form::text('fornecedor_numero', 'Número')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->fornecedor->numero : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::text('cep', 'CEP')->attrs(['class' => 'cep'])->required()
                                ->value(isset($item) ? $item->fornecedor->cep : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::text('fornecedor_bairro', 'Bairro')->attrs(['class' => ''])->required()
                                ->value(isset($item) ? $item->fornecedor->bairro : '')
                                !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                {!!Form::text('complemento', 'Complemento')->attrs(['class' => ''])
                                ->value(isset($item) ? $item->fornecedor->complemento : '')
                                !!}
                            </div>
                        </div>
                    </div>

                    @endif
                </div>
            </div>
            <div class="tab-pane fade" id="produtos" role="tabpanel">
                <div class="card">
                    <div class="row m-3">
                        <div class="table-responsive">
                            <table class="table table-dynamic table-produtos" style="width: 2800px">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Valor Unit.</th>
                                        <th>Subtotal</th>
                                        <th>%ICMS</th>
                                        <th>%PIS</th>
                                        <th>%COFINS</th>
                                        <th>%IPI</th>
                                        <th>%RED BC</th>
                                        <th>CFOP</th>
                                        <th>NCM</th>
                                        <th>Código benefício</th>
                                        <th>CST CSOSN</th>
                                        <th>CST PIS</th>
                                        <th>CST COFINS</th>
                                        <th>CST IPI</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($item))

                                    @foreach ($item->itens as $prod)

                                    @isset($isOrdemServico)
                                    @include('ordem_servico.partials.itens', ['prod' => $prod])
                                    @elseif(isset($isPedidoEcommerce))
                                    @include('pedido_ecommerce.partials.itens', ['prod' => $prod, 'cfop_estadual' => $item->cliente->cidade->uf])

                                    @else
                                    <tr class="dynamic-form">
                                        <td width="250">
                                            <select class="form-control select2 produto_id" name="produto_id[]" id="inp-produto_id">
                                                <option value="{{ $prod->produto_id }}">{{ $prod->produto->nome }}</option>
                                            </select>
                                            @if($prod->variacao_id)
                                            <span>variação: <strong>{{ $prod->produtoVariacao->descricao }}</strong></span>
                                            @endif
                                            <input name="variacao_id[]" type="hidden" value="{{ $prod->variacao_id }}">

                                        </td>
                                        <td width="80">
                                            <input value="{{ __moeda($prod->quantidade) }}" class="form-control qtd" type="tel" name="quantidade[]" id="inp-quantidade">
                                        </td>
                                        <td width="100">
                                            <input value="{{ __moeda($prod->valor_unitario) }}" class="form-control moeda valor_unit" type="tel" name="valor_unitario[]" id="inp-valor_unitario">
                                        </td>
                                        <td width="150">
                                            <input value="{{ __moeda($prod->sub_total) }}" class="form-control moeda sub_total" type="tel" name="sub_total[]" id="inp-subtotal">
                                        </td>
                                        <td width="80">
                                            <input value="{{ $prod->perc_icms }}" class="form-control percentual" type="tel" name="perc_icms[]" id="inp-perc_icms">
                                        </td>
                                        <td width="80">
                                            <input value="{{ $prod->perc_pis }}" class="form-control percentual" type="tel" name="perc_pis[]" id="inp-perc_pis">
                                        </td>
                                        <td width="80">
                                            <input value="{{ $prod->perc_cofins }}" class="form-control percentual" type="tel" name="perc_cofins[]" id="inp-perc_cofins">
                                        </td>
                                        <td width="80">
                                            <input value="{{ $prod->perc_ipi }}" class="form-control percentual" type="tel" name="perc_ipi[]" id="inp-perc_ipi">
                                        </td>
                                        <td width="80">
                                            <input value="{{ $prod->perc_red_bc }}" class="form-control percentual ignore" type="tel" name="perc_red_bc[]" id="inp-perc_red_bc">
                                        </td>
                                        <td width="80">
                                            <input value="{{ $prod->cfop }}" class="form-control cfop" type="tel" name="cfop[]" id="inp-cfop_estadual">
                                        </td>

                                        <td width="120">
                                            <input value="{{ $prod->ncm }}" class="form-control ncm" type="tel" name="ncm[]" id="inp-ncm">
                                        </td>
                                        <td width="120">
                                            <input value="{{ $prod->codigo_beneficio_fiscal }}" class="form-control ignore codigo_beneficio_fiscal" type="text" name="codigo_beneficio_fiscal[]">
                                        </td>

                                        <td width="250">
                                            <select name="cst_csosn[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
                                                <option @if($prod->cst_csosn == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="250">
                                            <select name="cst_pis[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                                <option @if($prod->cst_pis == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="250">
                                            <select name="cst_cofins[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                                <option @if($prod->cst_cofins == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="250">
                                            <select name="cst_ipi[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
                                                <option @if($prod->cst_ipi == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="30">
                                            <button class="btn btn-danger btn-remove-tr">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endisset
                                    @endforeach

                                    @elseif(isset($cotacao))
                                    @foreach ($cotacao->itens as $prod)
                                    @include('cotacoes.partials.itens', ['prod' => $prod, 'mesmo_estado' => $cotacao->fornecedor->cidade->uf == $empresa->cidade->uf])
                                    @endforeach

                                    @else
                                    <tr class="dynamic-form">
                                        <td width="250">
                                            <select required class="form-control select2 produto_id" name="produto_id[]" id="inp-produto_id">
                                            </select>

                                            <input name="variacao_id[]" type="hidden" value="">
                                        </td>
                                        <td width="80">
                                            <input class="form-control qtd" type="tel" name="quantidade[]" id="inp-quantidade">
                                        </td>
                                        <td width="100">
                                            <input class="form-control moeda valor_unit" type="tel" name="valor_unitario[]" id="inp-valor_unitario">
                                        </td>
                                        <td width="150">
                                            <input class="form-control moeda sub_total" type="tel" name="sub_total[]" id="inp-subtotal">
                                        </td>
                                        <td width="80">
                                            <input class="form-control percentual" type="tel" name="perc_icms[]" id="inp-perc_icms">
                                        </td>
                                        <td width="80">
                                            <input class="form-control percentual" type="tel" name="perc_pis[]" id="inp-perc_pis">
                                        </td>
                                        <td width="80">
                                            <input class="form-control percentual" type="tel" name="perc_cofins[]" id="inp-perc_cofins">
                                        </td>
                                        <td width="80">
                                            <input class="form-control percentual" type="tel" name="perc_ipi[]" id="inp-perc_ipi">
                                        </td>
                                        <td width="80">
                                            <input class="form-control percentual ignore" type="tel" name="perc_red_bc[]" id="inp-perc_red_bc">
                                        </td>
                                        <td width="80">
                                            <input class="form-control cfop" type="tel" name="cfop[]" id="inp-cfop_estadual">
                                        </td>

                                        <td width="120">
                                            <input class="form-control ncm" type="tel" name="ncm[]" id="inp-ncm">
                                        </td>

                                        <td width="120">
                                            <input class="form-control codigo_beneficio_fiscal ignore" type="text" name="codigo_beneficio_fiscal[]">
                                        </td>

                                        <td width="250">
                                            <select name="cst_csosn[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
                                                <option value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="250">
                                            <select name="cst_pis[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                                <option value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="250">
                                            <select name="cst_cofins[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                                <option value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="250">
                                            <select name="cst_ipi[]" class="form-control select2">
                                                @foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
                                                <option value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="30">
                                            <button class="btn btn-danger btn-remove-tr">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endisset
                                </tbody>
                            </table>
                        </div>
                        <div class="row col-12 col-lg-2 mt-3">
                            <br>
                            <button type="button" class="btn btn-dark btn-add-tr-nfe px-2">
                                <i class="ri-add-fill"></i>
                                Adicionar Produto
                            </button>
                        </div>
                        <div class="mt-3">
                            <h5>Total de Produtos: <strong class="total_prod">R$</strong></h5>
                        </div>
                        <input type="hidden" class="total_prod" name="valor_produtos" id="" value="">

                    </div>
                </div>
            </div>
            <div class="tab-pane fade show" id="transportadora" role="tabpanel">
                <div class="card">
                    <div class="row m-3">
                        <div class="col-md-5">
                            {!!Form::select('transportadora_id', 'Transportadora',['' => 'Selecine..'] + $transportadoras->pluck('razao_social', 'id')->all())
                            ->attrs(['class' => 'select2 transportadora_id'])
                            !!}
                        </div>
                        <hr class="mt-3">
                        <div class="row">
                            <div class="col-md-3">
                                {!!Form::text('razao_social_transp', 'Razão Social')
                                ->value(isset($item->transportadora) ? $item->transportadora->razao_social : '')
                                !!}
                            </div>
                            <div class="col-md-3">
                                {!!Form::text('nome_fantasia_transp', 'Nome Fantasia')
                                ->value(isset($item->transportadora) ? $item->transportadora->nome : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('cpf_cnpj_transp', 'CNPJ')
                                ->attrs(['class' => 'cpf_cnpj'])
                                ->value(isset($item->transportadora) ? $item->transportadora->cpf_cnpj : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('ie_transp', 'Incrição Estadual')
                                ->value(isset($item->transportadora) ? $item->transportadora->ie : '')
                                !!}
                            </div>
                            <div class="col-md-2">
                                {!!Form::tel('antt', 'ANTT')
                                ->value(isset($item->transportadora) ? $item->transportadora->antt : '')
                                !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::tel('rua_transp', 'Rua')
                                ->value(isset($item->transportadora) ? $item->transportadora->rua : '')
                                !!}
                            </div>
                            <div class="col-md-1 mt-3">
                                {!!Form::tel('numero_transp', 'Número')
                                ->value(isset($item->transportadora) ? $item->transportadora->numero : '')
                                !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::select('cidade_id', 'Cidade')
                                ->attrs(['class' => 'select2'])
                                ->options(isset($item->transportadora) ? [$item->transportadora->cidade->nome] : [])
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::tel('cep_transp', 'CEP')
                                ->attrs(['class' => 'cep'])
                                ->value(isset($item->transportadora) ? $item->transportadora->cep : '')
                                !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::text('email_transp', 'E-mail')
                                ->value(isset($item->transportadora) ? $item->transportadora->email : '')
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::tel('telefone_transp', 'Telefone')
                                ->attrs(['class' => 'fone'])
                                ->value(isset($item->transportadora) ? $item->transportadora->telefone : '')
                                !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::text('bairro_transp', 'Bairro')
                                ->value(isset($item->transportadora) ? $item->transportadora->bairro : '')
                                !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::text('complemento_transp', 'Complemento')
                                ->value(isset($item->transportadora) ? $item->transportadora->complemento : '')
                                !!}
                            </div>
                            <hr class="mt-3">
                            <h4 class="mt-3">Informações do Frete</h4>
                            <div class="col-md-2 mt-2">
                                {!!Form::tel('valor_frete', 'Valor do Frete')
                                ->attrs(['class' => 'moeda valor_frete'])
                                ->value(isset($item) ? __moeda($item->valor_frete) : (isset($cotacao) ? __moeda($cotacao->valor_frete) : ''))
                                !!}
                            </div>
                            <div class="col-md-2 mt-2">
                                {!!Form::tel('qtd_volumes', 'Qtd de Volumes')
                                ->attrs(['class' => ''])
                                !!}
                            </div>
                            <div class="col-md-3 mt-2">
                                {!!Form::tel('numeracao_volumes', 'Número de Volumes')
                                ->attrs(['class' => ''])
                                !!}
                            </div>
                            <div class="col-md-2 mt-2">
                                {!!Form::tel('peso_bruto', 'Peso Bruto')
                                ->attrs(['class' => 'peso'])
                                !!}
                            </div>
                            <div class="col-md-2 mt-2">
                                {!!Form::tel('peso_liquido', 'Peso Líquido')
                                ->attrs(['class' => 'peso'])
                                !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::text('especie', 'Espécie')
                                ->attrs(['class' => ''])
                                !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!!Form::select('tipo', 'Tipo', ['' => 'Selecione..'] + App\Models\Nfe::tiposFrete())
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!!Form::text('placa', 'Placa')
                                ->attrs(['class' => 'placa'])
                                !!}
                            </div>
                            <div class="col-md-1 mt-3">
                                {!!Form::select('uf', 'UF', App\Models\Cidade::estados())
                                ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade show" id="fatura" role="tabpanel">
                <div class="card">
                    <div class="row m-3">
                        <div class="col-md-3">
                            {!!Form::select('natureza_id', 'Natureza de Operação', ['' => 'Selecione'] + $naturezas->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'form-select'])
                            ->value(isset($item) ? $item->natureza_id : '')
                            ->required()
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::tel('acrescimo', 'Acréscimo')
                            ->attrs(['class' =>'acrescimo moeda'])
                            ->value(isset($item) ? __moeda($item->acrescimo) : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::tel('desconto', 'Desconto')
                            ->attrs(['class' => 'desconto moeda'])
                            ->value(isset($item) ? __moeda($item->desconto) : '')
                            !!}
                        </div>
                        <div class="col-md-5">
                            {!!Form::text('observacao', 'Observação')
                            ->attrs(['class' => ''])
                            !!}
                        </div>
                        @if(isset($isOrdemServico) || isset($isPedidoEcommerce))
                        <div class="col-md-2 mt-3">
                            {!!Form::tel('numero_nfe', 'Número NFe')
                            ->required()
                            ->value($numeroNfe)
                            !!}
                        </div>
                        @else
                        <div class="col-md-2 mt-3">
                            {!!Form::tel('numero_nfe', 'Número NFe')
                            ->required()
                            ->value(isset($item) ? $item->numero : $numeroNfe)
                            !!}
                        </div>
                        @endif

                        <div class="col-md-10 mt-3">
                            {!!Form::tel('referencia', 'Referência NFe')
                            !!}
                        </div>

                        <div class="col-md-2 mt-3">
                            {!!Form::date('data_emissao_saida', 'Data Emissão Saída')
                            !!}
                        </div>

                        <div class="col-md-2 mt-3">
                            {!!Form::date('data_emissao_retroativa', 'Data Emissão Retroativa')
                            !!}
                        </div>

                        @if(!isset($isCompra))
                        <div class="col-md-2 mt-3">
                            {!!Form::select('tpNF', 'Tipo NFe', ['1' => 'Saída', '0' => 'Entrada'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        @else
                        <div class="col-md-2 mt-3">
                            {!!Form::select('tpNF', 'Tipo NFe', ['0' => 'Entrada'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        @endif


                        <div class="col-md-2 mt-3">
                            {!!Form::select('finNFe', 'Finalidade NFe', [
                            '1' => 'NFe normal',
                            '2' => 'NFe complementar',
                            '3' => 'NFe de ajuste',
                            '4' => 'Devolução de mercadoria'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        @if(isset($item) && $item->orcamento == 0)
                        <div class="col-md-2 mt-3 div-conta-receber">
                            {!!Form::select('gerar_conta_receber', 'Gerar conta a receber', [
                            0 => 'Não',
                            1 => 'Sim'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        @endif

                        <div class="col-md-2 mt-3 div-conta-pagar d-none">
                            {!!Form::select('gerar_conta_pagar', 'Gerar conta a pagar', [
                            0 => 'Não',
                            1 => 'Sim'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        @if(!isset($isCompra))
                        @if(!isset($item))
                        <div class="col-md-2 mt-3">
                            {!!Form::select('orcamento', 'Salvar como Orçamento', [
                            0 => 'Não',
                            1 => 'Sim'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        @endif
                        @endif

                    </div>
                </div>
                <div class="card mt-1">
                    <div class="row m-3">
                        <div class="table-responsive">
                            <table class="table table-dynamic table-fatura" style="width: 800px">
                                <thead>
                                    <tr>
                                        <th>Tipo de Pagamento</th>
                                        <th>Data Vencimento</th>
                                        <th>Valor</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="body-pagamento" class="datatable-body">
                                    @isset($cotacao)
                                    @if(sizeof($cotacao->fatura) > 0)
                                    @foreach ($cotacao->fatura as $f)
                                    <tr class="dynamic-form">
                                        <td width="300">
                                            <select name="tipo_pagamento[]" class="form-control tipo_pagamento select2">
                                                <option value="">Selecione..</option>
                                                @foreach(App\Models\Nfe::tiposPagamento() as $key => $c)
                                                <option @if($f->tipo_pagamento == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="150">
                                            <input value="{{ $f->data_vencimento }}" type="date" class="form-control" name="data_vencimento[]" id="">
                                        </td>
                                        <td width="150">
                                            <input value="{{ __moeda($f->valor) }}" type="tel" class="form-control moeda valor_fatura" name="valor_fatura[]">
                                        </td>
                                        <td width="30">
                                            <button class="btn btn-danger btn-remove-tr">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr class="dynamic-form">
                                        <td width="300">
                                            <select name="tipo_pagamento[]" class="form-control tipo_pagamento select2">
                                                <option value="">Selecione..</option>
                                                @foreach(App\Models\Nfe::tiposPagamento() as $key => $c)
                                                <option value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="150">
                                            <input type="date" class="form-control date_atual" name="data_vencimento[]" id="" value="">
                                        </td>
                                        <td width="150">
                                            <input type="tel" class="form-control moeda valor_fatura" name="valor_fatura[]" id="valor">
                                        </td>
                                        <td width="30">
                                            <button class="btn btn-danger btn-remove-tr">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                    
                                    @else
                                    @if(isset($item) && isset($item->fatura) && sizeof($item->fatura) > 0 && !isset($isOrdemServico))
                                    @foreach ($item->fatura as $f)
                                    <tr class="dynamic-form">
                                        <td width="300">
                                            <select name="tipo_pagamento[]" class="form-control tipo_pagamento select2">
                                                <option value="">Selecione..</option>
                                                @foreach(App\Models\Nfe::tiposPagamento() as $key => $c)
                                                <option @if($f->tipo_pagamento == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="150">
                                            <input value="{{ $f->data_vencimento }}" type="date" class="form-control" name="data_vencimento[]" id="">
                                        </td>
                                        <td width="150">
                                            <input value="{{ __moeda($f->valor) }}" type="tel" class="form-control moeda valor_fatura" name="valor_fatura[]" id="valor">
                                        </td>
                                        <td width="30">
                                            <button class="btn btn-danger btn-remove-tr">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr class="dynamic-form">
                                        <td width="300">
                                            <select name="tipo_pagamento[]" class="form-control tipo_pagamento select2">
                                                <option value="">Selecione..</option>
                                                @foreach(App\Models\Nfe::tiposPagamento() as $key => $c)
                                                <option value="{{$key}}">{{$c}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="150">
                                            <input type="date" class="form-control date_atual" name="data_vencimento[]" id="" value="">
                                        </td>
                                        <td width="150">
                                            <input type="tel" class="form-control moeda valor_fatura" name="valor_fatura[]" id="valor">
                                        </td>
                                        <td width="30">
                                            <button class="btn btn-danger btn-remove-tr">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-info btn-add-tr px-5">
                                    Adicionar Pagamento
                                </button>
                            </div>
                        </div>
                        <div class="col-3 mt-4">
                            <h5>Total da Fatura: <strong class="total_fatura">R$</strong></h5>
                        </div>
                        <div class="col-3 mt-4">
                            <h5>Total de Produtos: <strong class="total_prod">R$</strong></h5>
                        </div>
                        <div class="col-3 mt-4">
                            <h5>Total do Frete: <strong class="total_frete">R$</strong></h5>
                        </div>
                        <div class="col-3 mt-4">
                            <h5>Total da NFe: <strong class="total_nfe text-success">R$</strong></h5>
                        </div>
                        <input type="hidden" class="valor_total" name="valor_total" id="" value="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success btn-salvar-nfe px-5 m-3">Salvar</button>
    </div>
</div>

@include('modals._cartao_credito', ['not_submit' => true])
@include('modals._variacao')


