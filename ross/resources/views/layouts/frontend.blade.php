<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="author" content="Ing. Alfonso Chávez <achavezb@directvla.com.co>">
        <title>{{ config('app.name', '') }}</title>
        <link href="{{ asset('img/favicon.ico?_v=0.3.6') }}" rel="icon" type="image/png" />
        <link href="{{ asset('inspinia/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('inspinia/css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('inspinia/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
        <link href="{{ asset('inspinia/css/animate.css') }}" rel="stylesheet">
        <link href="{{ asset('inspinia/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">
        <link href="{{ asset('inspinia/css/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet">
        <link href="{{ asset('css/app_ross.css') }}" rel="stylesheet">
        @yield('styles')

    </head>
    <body class="top-navigation">
        <div id="wrapper">

            <div class="row border-bottom">
                <nav id="navbar" class="navbar navbar-static-top" role="navigation">
                    <div class="container">
                        <div class="navbar-header">
                            <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
                                <i class="fa fa-reorder"></i>
                            </button>
                            <a href="{{ url('/') }}" class="navbar-brand">
                                <img src="{{ url('img/dtv-logo-white.svg') }}" height="30" class="d-inline-block align-top" alt="">
                            </a>
                        </div>
                        <div class="navbar-collapse collapse" id="navbar">
                            <ul class="nav navbar-nav">
                                <!--
                                <li class="dropdown">
                                    <a aria-expanded="false" role="button" href="#" class="dropdown-toggle" data-toggle="dropdown"> Menu item <span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu">
                                        <li><a href="">Menu item</a></li>
                                        <li><a href="">Menu item</a></li>
                                        <li><a href="">Menu item</a></li>
                                        <li><a href="">Menu item</a></li>
                                    </ul>
                                </li>
                                -->
                            </ul>

                            <ul class="nav navbar-top-links navbar-right">
                                <li><span class="m-r-sm welcome-message">{{ config('app.name', '') }}</span></li>
                                @auth
                                    <li><a href="{{ url('/home') }}">{{ Auth::user()->name }}</a></li>
                                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ trans('messages.000081') }}</a></li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                @else
                                    <li><a href="{{ route('login') }}">{{ trans('messages.000011') }}</a></li>
                                @endauth
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>



            <div id="page-wrapper" class="gray-bg">
                <div class="wrapper wrapper-content">
                    @yield('content')
                </div>
                <!-- Pie de página -->
                <div class="footer">
                    <div>{!! '<strong>Copyright &copy;</strong> '.config('appross.broadband', '').' '.date('Y').' - '.(date('Y')+2) !!}</div>
                    <div class="pull-right"><strong><!-- texto derecho --></strong></div>
                </div>
            </div>

        </div>
        <!-- Basic scripts -->
        <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
        <script src="{{ asset('js/jquery.easing.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/pace/pace.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/toastr/toastr.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/sweetalert/sweetalert.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/inspinia.js') }}"></script>
        @yield('scripts')

        <script type="text/javascript">
            //-----------------------------------------------------------------
            // GENERAR BOTON DE VOLVER ARRIBA.            
            $(document).ready(function() { 
                // Genera el boton y cuando se hace click regresa al inicio de página.          
                $("body").append("<a href='#' id='volverarriba'>Volver arriba</a>");
                $(window).scroll(function(){
                    if($(this).scrollTop() > 70){ 
                        $('#volverarriba').fadeIn();
                    }else{
                        $("#volverarriba").fadeOut();
                    }
                });
                $(document).on("click","#volverarriba",function(e){
                    e.preventDefault();
                    $("html, body").stop().animate({ scrollTop: 0 }, 2000);
                    //$("html, body").stop().animate({ scrollTop: 0 }, "slow");
                    return false; 
                });
            });
        </script>
    </body>
</html>