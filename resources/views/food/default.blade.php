<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Ogani Template">
    <meta name="keywords" content="Ogani, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }} - {{ $config->nome }}</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">
    <style type="text/css">
        :root {
            --color-main: {{$config->cor_principal}};
        }
    </style>
    <!-- Css Styles -->
    <link rel="stylesheet" href="/delivery/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/nice-select.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/style.css" type="text/css">
    <link rel="stylesheet" href="/delivery/css/main.css" type="text/css">

    <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">
    <style type="text/css">
        body.loading .modal-loading {
            display: block;
        }

        .modal-loading {
            display: none;
            position: fixed;
            z-index: 10000;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: rgba(255, 255, 255, 0.8)
            url("http://i.stack.imgur.com/FhHRx.gif") 50% 50% no-repeat;
        }
    </style>
    @yield('css')

</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <!-- Humberger Begin -->
    <div class="humberger__menu__overlay"></div>
    <div class="humberger__menu__wrapper">
        <div class="humberger__menu__logo">
            <a href="{{ route('food.index', ['link='.$config->loja_id]) }}"><img height="80" src="{{ $config->logoApp }}" alt=""></a>
        </div>
        <div class="humberger__menu__cart">
            <ul>
                <li><a href="#"><i class="fa fa-shopping-bag"></i> <span class="bg-main">@if($carrinho != []) {{ sizeof($carrinho->itens) }} @else 0 @endif</span></a></li>
            </ul>
            <div class="header__cart__price">carrinho: @if($carrinho != [])<span>R$ {{ __moeda($carrinho->valor_total) }}</span> @else <span>R$ 0,00</span>@endif</div>
        </div>
       
        <nav class="humberger__menu__nav mobile-menu">
            <ul>
                <li class="active"><a href="{{ route('food.index', ['link='.$config->loja_id]) }}">Home</a></li>
                <li><a href="#">Categorias</a>
                    <ul class="header__menu__dropdown">
                        @foreach($categorias as $c)
                        <li><a href="{{ route('food.produtos-categoria', [$c->hash_delivery, 'link='.$config->loja_id]) }}">{{ $c->nome }}</a></li>
                        @endforeach
                        
                    </ul>
                </li>
                <li><a href="{{ route('food.conta', ['link='.$config->loja_id]) }}">Minha conta</a></li>
            </ul>
        </nav>
        <div id="mobile-menu-wrap"></div>

        
    </div>
    <!-- Humberger End -->

    <!-- Header Section Begin -->
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="header__logo">
                        <a href="{{ route('food.index', ['link='.$config->loja_id]) }}"><img height="60" src="{{ $config->logoApp }}" alt=""></a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <nav class="header__menu">
                        <ul>
                            <li class="active"><a href="{{ route('food.index', ['link='.$config->loja_id]) }}">Home</a></li>
                            <li><a href="#">Categorias</a>

                                <ul class="header__menu__dropdown">
                                    @foreach($categorias as $c)
                                    <li><a href="{{ route('food.produtos-categoria', [$c->hash_delivery, 'link='.$config->loja_id]) }}">{{ $c->nome }}</a></li>
                                    @endforeach
                                </ul>
                            </li>

                            <li><a href="{{ route('food.conta', ['link='.$config->loja_id]) }}">Minha conta</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-lg-3">
                    <div class="header__cart">
                        <ul>
                            <li><a href="{{ route('food.carrinho', ['link='.$config->loja_id]) }}"><i class="fa fa-shopping-bag"></i> <span class="bg-main">@if($carrinho != []) {{ sizeof($carrinho->itens) }} @else 0 @endif</span></a></li>
                        </ul>
                        <div class="header__cart__price">carrinho: @if($carrinho != [])<span>R$ {{ __moeda($carrinho->valor_total) }}</span> @else <span>R$ 0,00</span>@endif</div>
                    </div>
                </div>
            </div>
            <div class="humberger__open">
                <i class="fa fa-bars"></i>
            </div>
        </div>
    </header>
    <!-- Header Section End -->

    @if(!isset($notSearch))
    <section class="hero">
        <div class="container">
            <div class="row">

                <div class="col-lg-12">
                    <div class="hero__search">
                        <div class="hero__search__form">
                            <form action="{{ route('food.pesquisa') }}">
                                <input type="hidden" value="{{ $config->loja_id }}" name="link">

                                <input value="{{ isset($pesquisa) ? $pesquisa : '' }}" type="text" name="pesquisa" placeholder="O que você precisa?">
                                <button type="submit" class="site-btn btn-main">PESQUISAR</button>
                            </form>
                        </div>
                    </div>
                    @isset($banners)
                    @include('food.partials.banner', ['banners' => $banners])
                    @endif
                </div>
            </div>

        </div>
    </section>
    @endif

    @yield('content')

    <!-- Footer Section Begin -->
    <footer class="footer spad">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="footer__about">
                        <div class="footer__about__logo">
                            <a href="{{ route('food.index', ['link='.$config->loja_id]) }}"><img height="50" src="{{ $config->logoApp }}" alt=""></a>
                        </div>
                        <ul>
                            <a href="tel:{{ $config->telefone }}"><li>Telefone: {{ $config->telefone }}</li></a>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="footer__widget">
                        <h6>Endereço</h6>
                        <ul>
                            <li>{{ $config->endereco }}</li>
                        </ul>
                        
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer__copyright">
                        <div class="footer__copyright__text"><p>Copyright &copy;
                            <script>document.write(new Date().getFullYear());</script> Todos os direitos reservados <i class="fa fa-heart" aria-hidden="true"></i> by <a href="#!">Slym</a></p></div>
                            <div class="footer__copyright__payment"><img src="/delivery/img/payment-item.png" alt=""></div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <div class="modal-loading loading-class"></div>
        <!-- Footer Section End -->

        <!-- Js Plugins -->
        <script src="/delivery/js/jquery-3.3.1.min.js"></script>
        <script src="/delivery/js/bootstrap.min.js"></script>
        <script src="/delivery/js/jquery.nice-select.min.js"></script>
        <script src="/delivery/js/jquery-ui.min.js"></script>
        <script src="/delivery/js/jquery.slicknav.js"></script>
        <script src="/delivery/js/mixitup.min.js"></script>
        <script src="/delivery/js/owl.carousel.min.js"></script>
        <script src="/delivery/js/main.js"></script>
        <script src="/assets/js/toastr.min.js"></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
        <script src="/assets/vendor/jquery-mask-plugin/jquery.mask.min.js"></script>

        <script type="text/javascript">
            @if(session()->has('flash_success'))
            toastr.success('{{ session()->get('flash_success') }}');
            @endif

            @if(session()->has('flash_error'))
            toastr.error('{{ session()->get('flash_error') }}');
            @endif

             @if(session()->has('flash_waning'))
            toastr.warning('{{ session()->get('flash_waning') }}');
            @endif

            let prot = window.location.protocol;
            let host = window.location.host;
            const path_url = prot + "//" + host + "/";
            $body = $("body");
            $(document).on({
                ajaxStart: function () {
                    $body.addClass("loading");
                },
                ajaxStop: function () {
                    $body.removeClass("loading");
                }
            });

            $(document).on("focus", ".cpf", function () {
                $(this).mask("000.000.000-00", { reverse: true })
            });

            $(document).on("focus", ".moeda", function () {
                $(this).mask("00000000,00", { reverse: true })
            });

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
            $(document).on("focus", ".cpf_cnpj", function () {
                $(this).mask(cpfMascara, cpfOptions);
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
        </script>

        @yield('js')

    </body>

    </html>