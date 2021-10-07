<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="author" content="Ing. Alfonso Chávez <achavezb@directvla.com.co>">
        <title>{{ config('app.name', '') }}</title>
        <link href="{{ url('img/favicon.ico?_v=0.3.6') }}" rel="icon" type="image/png" />
        <link href="{{ url('inspinia/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ url('inspinia/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
        <link href="{{ url('inspinia/css/animate.css') }}" rel="stylesheet">
        <link href="{{ url('inspinia/css/style.css') }}" rel="stylesheet">
        <style type="text/css">
            .gray-bg{background: url("{{ url('img/dtv2.jpg') }}") no-repeat center top / 100%  100% fixed;}
            .loginColumns{ margin: 80px auto auto; padding: 50px 20px 20px 20px; border: solid 1px #D2D4DF; box-shadow: rgba(200, 200, 200, 0.5) 0px 10px 18px 5px; background-color: rgba(8, 25, 41, 0.4); color: #D1D3DE; }
            .form-login {background-color: rgba(8, 25, 41, 0.4); border: none; border-image: none; }
            label{margin: left; width: 100%;}
            h2{font-size: 20px;}
            input[type='text'], input[type='password'], input[type='username']{color: #233646 !important;}
            .invalid-feedback strong{color: #F7A54B;}
        </style>
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="gray-bg">
        <div id="app">
            <div class="loginColumns">
                @if(Session::has('message'))
                <div class="row">
                    <div class="col-md-10 col-sm-offset-1">
                        <div class="alert alert-danger m-sm alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            {{ Session::get('message') }}
                        </div>
                        <br>
                    </div>
                </div>
                @endif
                <div class="row">

                    <div class="col-md-6">
                        <center>
                            <a href="{{ url('/') }}" class="text-center">
                                <img src="{{ url('img/dtv-logo-blue.svg') }}" class="d-inline-block align-top" alt="">
                                <hr>
                            </a>
                        </center>
                        <h2 class="font-bold">{{ config('app.name', '') }}</h2>

                        <p>{{ trans('messages.000073') }}</p>

                        <p><small></small></p>
                    </div>
                    <div class="col-md-6">
                        <div class="ibox-content form-login">
                            <form method="POST" action="{{ route('login') }}">
                                <div class="form-group">
                                    <br><br>
                                    @csrf
                                </div>
                                <div class="form-group">
                                    <label for="username" class="col-form-label text-left">{{ trans('messages.000017') }}</label>
                                    <input id="username" type="username" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>
                                    @error('username')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="password" class="col-form-label text-left">{{ trans('messages.000004') }}</label>
                                    <input type="password" id="password" name="password" placeholder="Password" class="form-control" required>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <br>

                                <button type="submit" class="btn btn-success block full-width m-b">
                                    {{ trans('messages.buttons.10') }}
                                </button>
                            </form>
                            <p class="m-t">
                                <small></small>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div>{!! '<strong>Copyright &copy;</strong> '.config('appross.broadband', '') !!}</div>
                    </div>
                    <div class="col-md-6 text-right">
                       <small>{!! date('Y').' - '.(date('Y')+2) !!}</small>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
