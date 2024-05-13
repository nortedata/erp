<!doctype html>
    <html lang="pt-br">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{$title}}</title>

        <link rel="shortcut icon" href="/logo-sm.png">
        <link href="/assets/vendor/fullcalendar/main.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css">
        <link href="/assets/vendor/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />

        <script rel="stylesheet" src="/assets/js/config.js"></script>
        <link href="/assets/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">
        <link rel="stylesheet" type="text/css" href="/css/style.css">

        <link href="/bs5-tour/css/bs5-intro-tour.css" rel="stylesheet" />
        @yield('css')

    </head>
    <body>

        <div class="loader"></div>
        @if(isset(Auth::user()->empresa->empresa))
        <input type="hidden" value="{{ Auth::user()->empresa->empresa->id }}" id="empresa_id">
        @endif
        <input type="hidden" value="{{ Auth::user()->id }}" id="usuario_id">

        <div class="wrapper">
            <!-- ========== Topbar Start ========== -->
            <div class="navbar-custom">
                <div class="topbar container-fluid">
                    <div class="d-flex align-items-center gap-lg-2 gap-1" id="step1">

                        <!-- Topbar Brand Logo -->
                        <div class="logo-topbar">
                            <!-- Logo light -->
                            <a href="/" class="logo-light">
                                <span class="logo-lg">
                                    <img src="/logo-sm.png" alt="logo">

                                </span>
                                <span class="logo-sm">
                                    <img src="/logo-sm.png" alt="small logo">
                                </span>
                            </a>

                            <!-- Logo Dark -->
                            <a href="/" class="logo-dark">
                                <span class="logo-lg">
                                    <img src="/logo-sm.png" alt="dark logo">
                                </span>
                                <span class="logo-sm">
                                    <img src="/logo-sm.png" alt="small logo">
                                </span>
                            </a>
                        </div>

                        <!-- Sidebar Menu Toggle Button -->
                        <button class="button-toggle-menu">
                            <i class="ri-menu-2-fill"></i>
                        </button>

                        <!-- Horizontal Menu Toggle Button -->
                        <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </button>

                        <!-- Topbar Search Form -->
                        @if(Auth::user()->empresa && !__isContador())
                        <div class="app-search dropdown d-none d-lg-block">
                            <span class="badge bg-primary">AMBIENTE: {{ Auth::user()->empresa->empresa->ambiente == 2 ? 'HOMOLOGAÇÃO' : 'PRODUÇÃO'}}</span>
                        </div>
                        @endif

                        @if(sizeof(Auth::user()->acessos) > 0)
                        <div class="app-search dropdown d-none d-lg-block float-end">
                            <span class="badge bg-info">IP: {{ Auth::user()->acessos ? Auth::user()->acessos->first()->ip : ''}}</span>
                        </div>
                        @endif

                        @if(Auth::user()->empresa && Auth::user()->empresa->empresa->empresa_selecionada != null)
                        <div class="app-search dropdown d-none d-lg-block float-end">
                            <a href="{{ route('contador.show') }}" class="badge bg-success p-2">Empresa selecionada: {{ Auth::user()->empresa->empresa->empresaSelecionada->info }}</a>
                        </div>
                        @endif

                        @if(Auth::user()->empresa && Auth::user()->empresa->empresa->plano)
                        <div class="app-search dropdown d-lg-block" style="margin-left: 20px;">
                            <span class="badge bg-dark p-2">Plano: 
                                <strong class="text-success">{{ Auth::user()->empresa->empresa->plano->plano->nome }}</strong> - data de expiração: 
                                <strong>{{ __data_pt(Auth::user()->empresa->empresa->plano->data_expiracao, 0) }}</strong>
                            </span>
                            <a class="btn btn-light btn-sm" href="{{ route('upgrade.index') }}">Fazer upgrade</a>
                            <button class="btn btn-info btn-sm ml-1" id="click-tour">Tour do sistema</button>

                        </div>
                        @endif

                    </div>


                    <ul class="topbar-menu d-flex align-items-center gap-3">
                        <li class="dropdown d-lg-none">
                            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <!-- <i class="ri-search-line fs-22"></i> -->
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg p-0">

                                <!-- inicio alertas -->
                                <li class="dropdown notification-list">
                                    <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                        <i class="ri-notification-3-fill fs-22"></i>
                                        <div class="spinner-border spinner-border-sm text-danger" role="status">
                                            <span class="visually-hidden"></span>
                                      </div>
                                      <span class="noti-icon-badge d-none"></span>
                                  </a>
                                  <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0" style="">
                                    <div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h6 class="m-0 fs-16 fw-medium"> Notificações</h6>
                                            </div>
                                            <div class="col-auto">
                                                <a href="{{ route('notificacao.clear-all') }}" class="text-dark text-decoration-underline">
                                                    <small>Limpar tudo</small>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="max-height: 300px;" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: auto; overflow: hidden scroll;"><div class="simplebar-content alertas-main" style="padding: 0px;">

                                        
                                    </div></div></div></div><div class="simplebar-placeholder" style="width: auto; height: 432px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: visible;"><div class="simplebar-scrollbar" style="height: 208px; transform: translate3d(0px, 0px, 0px); display: block;"></div></div></div>


                                </div>
                            </li>
                            <!-- fim alertas -->
                            @if(!__isContador())
                            <li class="d-none d-sm-inline-block">
                                <a title="PDV" class="nav-link" href="{{ route('frontbox.create')}}">
                                    <i class="ri-shopping-basket-2-fill fs-22"></i>
                                </a>
                            </li>
                            @endif

                            <li class="d-none d-sm-inline-block" id="step2">
                                <div class="nav-link" id="light-dark-mode">
                                    <i class="ri-moon-fill fs-22"></i>
                                </div>
                            </li>

                            <li class="d-none d-md-inline-block">
                                <a class="nav-link" href="" data-toggle="fullscreen">
                                    <i class="ri-fullscreen-line fs-22"></i>
                                </a>
                            </li>

                            <li class="dropdown me-md-2" id="step3">
                                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <span class="account-user-avatar">
                                        @if(Auth::user()->imagem != null)
                                        <img src="{{ Auth::user()->img }}" height="32" class="rounded-circle">
                                        @else
                                        <img src="/assets/images/users/avatar-1.jpg" alt="user-image" width="32" class="rounded-circle">
                                        @endif
                                    </span>
                                    <span class="d-lg-flex flex-column gap-1 d-none">
                                        <h5 class="my-0"> {{ Auth::user()->name }}</h5>
                                        <h6 class="my-0 fw-normal">{{ Auth::user()->tipo }}</h6>

                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                                    <!-- item-->
                                    <div class=" dropdown-header noti-title">
                                        <h6 class="text-overflow m-0">Bem vindo!</h6>
                                    </div>

                                    @if(!__isContador())
                                    <a href="{{ route('usuarios.profile', Auth::user()->id) }}" class="dropdown-item">
                                        <i class="ri-account-circle-fill align-middle me-1"></i>
                                        <span>Minha Conta</span>
                                    </a>

                                    <!-- item-->
                                    <a href="{{ route('config.index') }}" class="dropdown-item">
                                        <i class="ri-settings-4-fill align-middle me-1"></i>
                                        <span>Configuração</span>
                                    </a>

                                    <a href="{{ route('ticket.index') }}" class="dropdown-item">
                                        <i class="ri-information-fill align-middle me-1"></i>
                                        <span>Abrir chamado</span>
                                    </a>
                                    @endif

                                    <!-- item-->
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                        <i class="ri-logout-box-line  align-middle me-1"></i>
                                        Sair
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <!-- ========== Topbar End ========== -->


        <div class="leftside-menu">

            <!-- Brand Logo Light -->
            <a href="/" class="logo logo-light">
                <span class="logo-lg">
                    <img class="logo-painel" src="/logo.png" alt="logo">
                </span>
                <span class="logo-sm">
                    <img src="/logo-sm.png" alt="small logo">
                </span>
            </a>

            <!-- Brand Logo Dark -->
            <a href="/" class="logo logo-dark">
                <span class="logo-lg">
                    <img class="logo-painel" src="/logo.png" alt="dark logo">
                </span>
                <span class="logo-sm">
                    <img src="/logo-sm.png" alt="small logo">
                </span>
            </a>

            <!-- Sidebar Hover Menu Toggle Button -->
            <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
                <i class="ri-checkbox-blank-circle-line align-middle"></i>
            </div>

            <!-- Full Sidebar Menu Close Button -->
            <div class="button-close-fullsidebar">
                <i class="ri-close-fill align-middle"></i>
            </div>

            <!-- Sidebar -left -->
            <div class="h-100" id="leftside-menu-container" data-simplebar>
                <!-- Leftbar User -->
                <div class="leftbar-user p-3 text-white">
                    <a href="{{ route('usuarios.profile', Auth::user()->id) }}" class="d-flex align-items-center text-reset">
                        <div class="flex-shrink-0">
                            @if(Auth::user()->imagem != null)
                            <img src="{{ Auth::user()->img }}" height="42" class="rounded-circle shadow">
                            @else
                            <img src="/assets/images/users/avatar-1.jpg" height="42" class="rounded-circle shadow">
                            @endif
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <span class="fw-semibold fs-15 d-block"> {{ Auth::user()->name }}</span>
                            <span class="fs-13">{{ Auth::user()->tipo }}</span>
                        </div>
                        <div class="ms-auto">
                            <i class="ri-arrow-right-s-fill fs-20"></i>
                        </div>
                    </a>
                </div>

                <!--- Sidemenu -->
                <ul class="side-nav" id="step4">

                    <li class="side-nav-title mt-1"> Menu</li>

                    <li class="side-nav-item">
                        <a href="{{ route('home') }}" class="side-nav-link">
                            <i class="ri-dashboard-2-fill"></i>
                            <span class="badge bg-success float-end"></span>
                            <span> Home </span>
                        </a>
                    </li>

                    @if(__isMaster())

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPages2" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-stack-fill"></i>
                            <span> SuperAdmin </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPages2">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('empresas.index') }}">Empresas</a>
                                </li>

                                @if(env("CONTADOR") == 1)
                                <li>
                                    <a href="{{ route('contadores.index') }}">Contadores</a>
                                </li>
                                @endif

                                <li>
                                    <a href="{{ route('planos.index') }}">Planos</a>
                                </li>
                                <li>
                                    <a href="{{ route('segmentos.index') }}">Segmentos</a>
                                </li>
                                <li>
                                    <a href="{{ route('cidades.index') }}">Cidades</a>
                                </li>
                                <li>
                                    <a href="{{ route('usuario-super.index') }}">Usuários</a>
                                </li>
                                <li>
                                    <a href="{{ route('gerenciar-planos.index') }}">Gerenciar planos</a>
                                </li>
                                <li>
                                    <a href="{{ route('planos-pendentes.index') }}">Planos pendentes</a>
                                </li>
                                <li>
                                    <a href="{{ route('ncm.index') }}">NCM</a>
                                </li>

                                <li>
                                    <a href="{{ route('ibpt.index') }}">IBPT</a>
                                </li>

                                <li>
                                    <a href="{{ route('ticket-super.index') }}">Ticket</a>
                                </li>
                                <li>
                                    <a href="{{ route('configuracao-super.index') }}">Configuração</a>
                                </li>
                                <li>
                                    <a href="{{ route('notificacao-super.index') }}">Notificações</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPages1" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-file-mark-fill"></i>
                            <span> Emissões </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPages1">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('nfe-all') }}">NFe</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfce-all') }}">NFCe</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @if(env("MARKETPLACE") == 1)
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarMarketPlace" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-store-2-line"></i>
                            <span>Delivery </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarMarketPlace">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('bairros-super.index') }}">Bairros</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    @endif

                    @endif

                    @if(!__isMaster())

                    @if(__isActivePlan(Auth::user()->empresa, 'Produtos'))
                    <li class="side-nav-item" id="step7">
                        <a data-bs-toggle="collapse" href="#sidebarExtendedProd" aria-expanded="false" aria-controls="sidebarExtendedUI" class="side-nav-link">
                            <i class="ri-product-hunt-fill"></i>
                            <span> Produtos </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarExtendedProd">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('categoria-produtos.index') }}">Categorias</a>
                                </li>

                                <li>
                                    <a href="{{ route('produtos.index') }}">Listar</a>
                                </li>

                                <li>
                                    <a href="{{ route('produtos.create') }}">Novo Produto</a>
                                </li>

                                <li>
                                    <a href="{{ route('estoque.index') }}">Estoque</a>
                                </li>

                                <li>
                                    <a href="{{ route('variacoes.index') }}">Variações</a>
                                </li>

                                <li>
                                    <a href="{{ route('lista-preco.index') }}">Lista de preços</a>
                                </li>

                                <li>
                                    <a href="{{ route('produtopadrao-tributacao.index') }}">Configuração Padrão Fiscal</a>
                                </li>

                                <li>
                                    <a href="{{ route('marcas.index') }}">Marcas</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Atendimento'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarAtendimento" aria-expanded="false" aria-controls="sidebarAtendimento" class="side-nav-link">
                            <i class="ri-store-2-line"></i>
                            <span> Atendimento </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarAtendimento">
                            <ul class="side-nav-third-level">
                                <li>
                                    <a href="{{ route('atendimentos.index') }}">Dias de Atendimento</a>
                                </li>
                                <li>
                                    <a href="{{ route('interrupcoes.index') }}">Interrupções</a>
                                </li>
                                <li>
                                    <a href="{{ route('funcionamentos.index') }}">Horário de Funcionamento</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Serviços'))

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarExtendedServ" aria-expanded="false" aria-controls="sidebarExtendedSer" class="side-nav-link">
                            <i class="ri-tools-fill"></i>
                            <span> Serviços </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarExtendedServ">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('categoria-servico.index') }}">Categorias</a>
                                </li>
                                <li>
                                    <a href="{{ route('servicos.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('servicos.create') }}">Novo serviço</a>
                                </li>

                                <li>
                                    <a data-bs-toggle="collapse" href="#ordemservico" aria-expanded="false" aria-controls="ordemservico">
                                        <span> Ordem Serviço</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="ordemservico">
                                        <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('ordem-servico.index') }}">Listar</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('ordem-servico.create') }}">Nova OS</a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Agendamentos'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarAgendamentos" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-calendar-event-fill"></i>

                            <span>Agendamentos</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarAgendamentos">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('agendamentos.index') }}">Listar</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Pessoas'))
                    <li class="side-nav-item" id="step6">
                        <a data-bs-toggle="collapse" href="#sidebarPessoas" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-group-2-fill"></i>

                            <span>Pessoas</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPessoas">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('clientes.index') }}">Clientes</a>
                                </li>
                                <li>
                                    <a href="{{ route('fornecedores.index') }}">Fornecedores</a>
                                </li>
                                <li>
                                    <a href="{{ route('transportadoras.index') }}">Transportadoras</a>
                                </li>
                                <li>
                                    <a href="{{ route('usuarios.index') }}">Usuários</a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarGestaoPessoal" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-folder-user-line"></i>

                            <span>Gestão Pessoal</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarGestaoPessoal">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('funcionarios.index') }}">Funcionários</a>
                                </li>
                                <li>
                                    <a href="{{ route('evento-funcionarios.index') }}">Eventos</a>
                                </li>
                                <li>
                                    <a href="{{ route('funcionario-eventos.index') }}">Funcionários x Eventos</a>
                                </li>
                                <li>
                                    <a href="{{ route('apuracao-mensal.index') }}">Apuração Mensal</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Veiculos'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarVeiculos" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-roadster-line"></i>
                            <span> Veículos </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarVeiculos">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('veiculos.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('veiculos.create') }}">Novo Veículo</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Compras'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCompra" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-logout-box-line"></i>

                            <span>Compras</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCompra">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('compras.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('compras.create')}}" data-toggle="fullscreen" class="dropdown-item">Nova
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('compras.xml')}}" data-toggle="fullscreen" class="dropdown-item">
                                        Importar XML
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('manifesto.index')}}" data-toggle="fullscreen" class="dropdown-item">
                                        Manifesto
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('cotacoes.index')}}" data-toggle="fullscreen" class="dropdown-item">
                                        Cotação
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('nfe-entrada-xml.index') }}">Arquivos XML</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif



                    @if(__isActivePlan(Auth::user()->empresa, 'PDV'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPDV" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-shopping-cart-fill"></i>

                            <span>PDV</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPDV">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('frontbox.index') }}">Listar</a>
                                </li>
                                <li class="d-none d-sm-inline-block">
                                    <a href="{{ route('frontbox.create')}}" data-toggle="fullscreen" class="dropdown-item">PDV</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'NFe'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarNfe" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-file-list-fill"></i>

                            <span>Vendas</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarNfe">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('nfe.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfe.create') }}">Nova</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfe.inutilizar') }}">Inutilizar NFe</a>
                                </li>
                                <li>
                                    <a href="{{ route('orcamentos.index') }}">Orçamentos</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfe-xml.index') }}">Arquivos XML</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'NFCe'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarNfce" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-bill-line"></i>
                            <span>NFCe</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarNfce">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('nfce.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfce.create') }}">Nova</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfce.inutilizar') }}">Inutilizar</a>
                                </li>
                                <li>
                                    <a href="{{ route('nfce-xml.index') }}">Arquivos XML</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPreVenda" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class=" ri-list-ordered"></i>
                            <span>Pré Venda</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPreVenda">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('pre-venda.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('pre-venda.create') }}">Nova</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'NFSe'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarNfse" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-file-code-line"></i>

                            <span>NFSe</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarNfse">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('nota-servico.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('nota-servico.create') }}">Nova</a>
                                </li>
                                <li>
                                    <a href="{{ route('nota-servico-config.index') }}">Emitente</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'CTe'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCte" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-truck-fill"></i>
                            <span>CTe</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCte">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('cte.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('cte.create') }}">Nova</a>
                                </li>
                                <li>
                                    <a href="{{ route('cte.inutilizar') }}">Inutilizar</a>
                                </li>
                                <li>
                                    <a href="{{ route('cte-xml.index') }}">Arquivos XML</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCteOs" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-focus-3-line"></i>
                            <span>CTe Os</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCteOs">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('cte-os.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('cte-os.create') }}">Nova</a>
                                </li>
                                {{-- <li>
                                    <a href="{{ route('cte-os.inutilizar') }}">Inutilizar</a>
                                </li> --}}
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'MDFe'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarMdfe" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-file-lock-line"></i>
                            <span>MDFe</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarMdfe">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('mdfe.index') }}">Listar</a>
                                </li>
                                <li>
                                    <a href="{{ route('mdfe.create') }}">Nova</a>
                                </li>
                                <li>
                                    <a href="{{ route('mdfe.inutilizar') }}">Inutilizar</a>
                                </li>
                                <li>
                                    <a href="{{ route('mdfe-xml.index') }}">Arquivos XML</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Financeiro'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarPagar" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-money-dollar-box-fill"></i>
                            <span>Financeiro</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPagar">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a data-bs-toggle="collapse" href="#caixa" aria-expanded="false" aria-controls="caixa">
                                        <span> Caixa </span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="caixa">
                                        <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('caixa.index') }}">Movimentação</a>
                                            </li>

                                            <li>
                                                <a href="{{ route('caixa.create') }}">Abrir caixa</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('caixa.list') }}">Listar</a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li>
                                    <a data-bs-toggle="collapse" href="#pagar" aria-expanded="false" aria-controls="pagar">
                                        <span> Contas a pagar </span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="pagar">
                                        <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('conta-pagar.index') }}">Listar</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('conta-pagar.create') }}">Nova conta</a>
                                            </li>

                                        </ul>
                                    </div>
                                </li>
                                <li>
                                    <a data-bs-toggle="collapse" href="#receber" aria-expanded="false" aria-controls="receber">
                                        <span> Contas a receber </span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="receber">
                                        <ul class="side-nav-third-level">
                                            <li>
                                                <a href="{{ route('conta-receber.index') }}">Listar</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('conta-receber.create') }}">Nova conta</a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li>
                                    <a href="{{ route('relatorios.index') }}">Relatorios</a>
                                </li>
                                <li>
                                    <a href="{{ route('taxa-cartao.index') }}">Taxas de cartão</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(__isActivePlan(Auth::user()->empresa, 'Cardapio'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCardapio" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-restaurant-2-line"></i>
                            <span>Cardápio</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCardapio">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('config-cardapio.index') }}">Configuração</a>
                                </li>
                                <li>
                                    <a href="{{ route('produtos-cardapio.categorias') }}">Categorias</a>
                                </li>

                                <li>
                                    <a href="{{ route('produtos-cardapio.index') }}">Produtos</a>
                                </li>

                                <li>
                                    <a href="{{ route('adicionais.index') }}">Adicionais</a>
                                </li>

                                <li>
                                    <a href="{{ route('pedidos-cardapio.index') }}">Comandas</a>
                                </li>

                                <li>
                                    <a href="{{ route('pedido-cozinha.index') }}">Controle de pedidos</a>
                                </li>

                                <li>
                                    <a href="{{ route('carrossel.index') }}">Carrossel destaque</a>
                                </li>

                                <li>
                                    <a href="{{ route('avaliacao-cardapio.index') }}">Avaliações</a>
                                </li>

                                <li>
                                    <a href="{{ route('tamanhos-pizza.index') }}">Tamanhos de pizza</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(env("ECOMMERCE") == 1)
                    @if(__isActivePlan(Auth::user()->empresa, 'Ecommerce'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarEcommerce" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-store-3-line"></i>
                            <span> Ecommerce </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarEcommerce">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('config-ecommerce.index') }}">Configuração</a>
                                </li>
                                <li>
                                    <a href="{{ route('produtos-ecommerce.categorias') }}">Categorias de produtos</a>
                                </li>
                                <li>
                                    <a href="{{ route('produtos-ecommerce.index') }}">Produtos</a>
                                </li>

                                <li>
                                    <a href="{{ route('pedidos-ecommerce.index') }}">Pedidos</a>
                                </li>
                                
                                <li>
                                    <a target="_blank" href="{{ route('config-ecommerce.site') }}">Ver site</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                    @endif

                    @if(env("MERCADOLIVRE") == 1)
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarMarketPlace" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-box-1-line"></i>
                            <span> Mercado livre </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarMarketPlace">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('mercado-livre-config.index') }}">Configuração</a>
                                </li>
                                <li>
                                    <a href="{{ route('mercado-livre.produtos-news') }}">Produtos</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(env("MARKETPLACE") == 1)
                    @if(__isActivePlan(Auth::user()->empresa, 'Delivery'))
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarMarketPlace" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                            <i class="ri-store-2-line"></i>
                            <span> Delivery </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarMarketPlace">
                            <ul class="side-nav-second-level">

                                <li>
                                    <a href="{{ route('config-marketplace.index') }}">Configuração</a>
                                </li>

                                <li>
                                    <a href="{{ route('pedidos-delivery.index') }}">Pedidos</a>
                                </li>
                                <li>
                                    <a href="{{ route('produtos-delivery.categorias') }}">Categorias de produtos</a>
                                </li>
                                <li>
                                    <a href="{{ route('produtos-delivery.index') }}">Produtos</a>
                                </li>
                                <li>
                                    <a href="{{ route('funcionamento-delivery.index') }}">Funcionamento</a>
                                </li>

                                <li>
                                    <a href="{{ route('bairros-empresa.index') }}">Bairros</a>
                                </li>
                                <li>
                                    <a href="{{ route('adicionais.index') }}">Adicionais</a>
                                </li>
                                <li>
                                    <a href="{{ route('destaque-marketplace.index') }}">Destaques</a>
                                </li>
                                <li>
                                    <a href="{{ route('cupom-desconto.index') }}">Cupom de desconto</a>
                                </li>
                                <li>
                                    <a href="{{ route('tamanhos-pizza.index') }}">Tamanhos de pizza</a>
                                </li>
                                <li>
                                    <a href="{{ route('motoboys.index') }}">Motoboys</a>
                                </li>

                                <li>
                                    <a href="{{ route('pedido-cozinha.index') }}">Controle de pedidos</a>
                                </li>
                                <li>
                                    <a href="{{ route('clientes-delivery.index') }}">Clientes</a>
                                </li>
                                <li>
                                    <a target="_blank" href="{{ route('config-marketplace.loja') }}">Ver loja</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                    @endif

                    <li class="side-nav-item" id="step5">
                        <a data-bs-toggle="collapse" href="#sidebarConfig" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-settings-4-fill"></i>
                            <span>Configuração</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarConfig">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('config.index') }}">Emitente</a>
                                </li>
                                <li>
                                    <a href="{{ route('natureza-operacao.index') }}">Natureza de Operação</a>
                                </li>
                                <li>
                                    <a href="{{ route('config-geral.create') }}">Geral</a>
                                </li>
                                <li>
                                    <a href="{{ route('difal.index') }}">Op. Interestadual - Difal</a>
                                </li>
                                <li>
                                    <a href="{{ route('cash-back-config.index') }}">CashBack</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @endif

                    @if(Auth::user()->empresa && __isContador())
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCad" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-draft-fill"></i>
                            <span>Cadastros</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCad">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('contador-empresa.produtos') }}">Produtos</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.clientes') }}">Clientes</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.fornecedores') }}">Fornecedores</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarDoc" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                            <i class="ri-clipboard-fill"></i>
                            <span>Documentos</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarDoc">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('contador-empresa.nfe') }}">NFe</a>
                                </li>

                                <li>
                                    <a href="{{ route('contador-empresa.nfce') }}">NFCe</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.cte') }}">CTe</a>
                                </li>
                                <li>
                                    <a href="{{ route('contador-empresa.mdfe') }}">MDFe</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    @yield('content')

                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <script>
                                document.write(new Date().getFullYear())

                            </script> {{ env("APP_NAME") }}
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        @if(!isset($not_loading))
        <div class="control-loading">
            <div class="modal-loading loading-class"></div>
        </div>
        @endif

        <div class="modal fade" id="modal-notificacao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Notificações</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-notificacao-delivery" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Notificações de Delivery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-notificacao-ecommerce" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Notificações de Ecommerce</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            let prot = window.location.protocol;
            let host = window.location.host;
            const path_url = prot + "//" + host + "/";

        </script>
        <script src="/assets/js/vendor.min.js"></script>
        <script src="/assets/vendor/select2/js/select2.min.js"></script>
        <script src="/assets/js/toastr.min.js"></script>
        <script src="/assets/vendor/dropzone/dropzone.js"></script>
        <script src="/assets/js/pages/component.fileupload.js"></script>
        <script src="/assets/vendor/daterangepicker/moment.min.js"></script>
        <script src="/assets/vendor/daterangepicker/daterangepicker.js"></script>
        <script src="/assets/vendor/jquery-mask-plugin/jquery.mask.min.js"></script>
        <script src="/assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="/assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
        <script src="/assets/vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="/assets/vendor/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="/js/uploadImagem.js"></script>
        <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
        <script src="/assets/js/app.js"></script>
        <script type="text/javascript" src="/js/main.js"></script>
        <script src="/assets/vendor/flatpickr/flatpickr.min.js"></script>
        <script src="/assets/js/pages/demo.flatpickr.js"></script>

        @if(__isNotificacao(Auth::user()->empresa) && Auth::user()->notificacao_cardapio)
        <script src="/js/notificacao.js"></script>
        @endif

        @if(__isNotificacaoMarketPlace(Auth::user()->empresa) && Auth::user()->notificacao_marketplace)
        <script src="/js/notificacao_marketplace.js"></script>
        @endif

        @if(__isNotificacaoEcommerce(Auth::user()->empresa) && Auth::user()->notificacao_ecommerce)
        <script src="/js/notificacao_ecommerce.js"></script>
        @endif

        @yield('js')

        <script type="text/javascript">
            toastr.options = {
                "progressBar": true
                , "onclick": null
                , "showDuration": "300"
                , "hideDuration": "1000"
                , "timeOut": "10000"
                , "extendedTimeOut": "1000"
                , "showEasing": "swing"
                , "hideEasing": "linear"
                , "showMethod": "fadeIn"
                , "hideMethod": "fadeOut"
            }
            @if(session()->has('flash_success'))
            toastr.success('{{ session()->get('flash_success') }}');
            @endif

            @if(session()->has('flash_error'))
            toastr.error('{{ session()->get('flash_error') }}');
            @endif

            @if(session()->has('flash_warning'))
            toastr.warning('{{ session()->get('flash_warning') }}');
            @endif

            @if(!Auth::user()->sidebar_active)

            $(html).attr('data-sidenav-size', 'condensed')
            @endif

            window.addEventListener("load", () => {
                setTimeout(() => {
                    document.querySelector(".loader").classList.add("loader--hidden")                
                }, 100)
            })

        </script>

        <script src="/bs5-tour/js/bs5-intro-tour.js"></script>
        <script src="/js/tour.js"></script>
    </body>
    </html>
