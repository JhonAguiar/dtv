@extends('layouts.frontend')

@section('styles')
    <style type="text/css">
        #nofound {background: rgb(133, 133, 133); }
        .mid_center {width: 500px; margin: 0 auto; text-align: center; padding: 10px 20px; color: #F1F1F1; }
        .ibox-content{ background: #D3DCE3 url('img/svg/layers.svg') no-repeat center top / 100%  100%;/*fixed*/  }
        .wrapper .middle-box {margin-top: 20px; }
        .middle-box h1 {font-size: 100px;}
    </style>
@endsection

@section('content')
    <div class="row animated fadeInRightBig">
        <div class="col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>{{ trans('messages.000082') }}</h5>
                    <div class="ibox-tools"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></div>
                </div>
                <div class="ibox-content text-center">
                    <div class="middle-box text-center animated fadeInDown">
                        <h1>404</h1>
                        @if ( isset($message) and !empty($message) )
                            <h3 class="font-bold">{{ $message }}</h3>
                        @endif                        
                        <div class="error-desc text-justify">
                            <p>{{ trans('messages.000083') }}</p>
                            <h5>{{ trans('messages.000084') }}</h5>
                            <a href="{{ url('/') }}">Volver al Inicio</a>
                        </div>
                        <div class="hr-line-dashed"><br></div>
                    </div>
                </div>
                <div class="ibox-footer">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>
@endsection
