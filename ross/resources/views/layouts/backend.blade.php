<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <?php 
            $controller_action = class_basename( \Route::getCurrentRoute()->getActionName() );
            $parts = explode("@", $controller_action);
            $controller = isset($headers['controller']) ? trim($headers['controller']) : substr($parts[0], 0, -10);
            $method = isset($headers['method']) ? trim($headers['method']) : $parts[1];
            $temp_regions = \App\Customs\Collections::getCollectionRegionsActive();
            $temp_languages = \App\Customs\Collections::getLanguage();           
        ?>        
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="author" content="Ing. Alfonso Chávez <achavezb@directvla.com.co>">
        <title>{{ config('appross.sigla', '') }} - @yield('title')</title>
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
    <body class="" onload="activateSessionAccount()">
        <div id="wrapper">
            
            <!-- Menú izquierdo -->
            @include('components.navigation')

            <div id="page-wrapper" class="gray-bg">
                
                <!-- Menú superior -->
                @include('components.toptnavbar')

                <!-- Seccion de herramientas -->
                @include('components.tools')


                <!-- Contenido dinámico -->
                <div class="wrapper wrapper-content">
                    @include ('components.mensajes', array('info-mensajes' => $errors))
                    <div id="messagesAjax"></div>

                    @yield('content')
                </div>

                <!-- Pie de página -->
                <div class="footer">
                    <div>{!! '<strong>Copyright &copy;</strong> '.config('appross.broadband', '').' '.date('Y').' - '.(date('Y')+2) !!}</div>
                    <div class="pull-right"><strong><!-- texto derecho --></strong></div>
                </div>

            </div>
        </div>

        <!-- Ventana modal para los videos -->
        @include('components.video_interfaz')

        <!-- Ventana modal -->
        @include('components.modal')

        <!-- Ventana modal para formularios Ajax-->
        @include('components.modal_ajax')

        <!-- Elemento de precarga -->
        @include('components.spiner')

        <!-- Basic and Complements scripts -->
        <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
        <script src="{{ asset('js/jquery.easing.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/pace/pace.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/toastr/toastr.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/plugins/sweetalert/sweetalert.min.js') }}"></script>
        <script src="{{ asset('inspinia/js/inspinia.js') }}"></script>
        <script src="{{ asset('js/app_ross.js') }}"></script>
        @yield('scripts')

        @show
        <script type="text/javascript">
            var time_session=0;
            function activateSessionAccount() {
                time_session = setTimeout(function() { 
                    $.ajax({ 
                        method: "GET", 
                        url: "{{ url('/sessiontime') }}",
                    }).done(function( msg ) {
                        if (msg=="caducado") { 
                            document.location.href="{{ url('/sessionredirect') }}";
                        }
                    },"json");
                }, 1800000);// milisegundos = 30minutos
            }
            function resetSession() {
                clearTimeout(time_session);//limpia el timeout para resetear el tiempo desde cero 
                time_session = setTimeout(function() { 
                    $.ajax({ 
                        method: "GET", 
                        url: "{{ url('/sessiontime') }}",
                    }).done(function( msg ) {
                        if (msg=="caducado") { 
                            document.location.href="{{url('/sessionredirect')}}";
                        }
                    },"json");
                }, 1800000);// milisegundos = 30minutos
            }
            $(document).ready(function () {
                $(document).mousemove(function( event ) {
                    resetSession();
                });
                $(document).keypress(function() {
                    resetSession();
                });
                $(document).click(function() {
                    resetSession();
                });
            });
        </script>
    </body>
</html>