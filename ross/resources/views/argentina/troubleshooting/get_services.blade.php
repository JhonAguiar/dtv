@extends('layouts.backend')

@section('styles')
    <style type="text/css">
        .ibox-content{}
        .margin-check{margin-bottom: 4px;}
    </style>
@endsection

@section('title', @$headers['title'])
@section('controller', @$headers['controller'])
@section('method', @$headers['method'])

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{{ trans('messages.000066') }}</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content ibox-form">

                <form name="formgeters" id="formgeters" target="targetFrame" action="{{ url('Argentina/troubleshooting/services').'?render=pdf' }}" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" readonly="readonly">
                    <p class="text-justify">{{ trans('messages.000074') }}</p>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="load_type" class="control-label">{{ trans('messages.000009') }}</label>
                                <select id="load_type" name="load_type" class="form-control">
                                    <option value="individual" selected="selected">{{ trans('messages.000027') }}</option>
                                    <option value="multiple">{{ trans('messages.000026') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div id="unitaria">
                                    <label for="subscriber_identity" class="control-label">{{ trans('messages.000027') }}</label>
                                    <textarea name="subscriber_identity" id="subscriber_identity" class="form-control" rows="9" maxlength="1200" placeholder=""></textarea>
                                </div>
                                <div id="multiple" style="display: none;">
                                    <label for="subscriber_identity_file" class="control-label">{{ trans('messages.000026') }}</label>
                                    <input type="file" id="subscriber_identity_file" name="subscriber_identity_file" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <br>
                            <h2>{{ trans('messages.000170') }}</h2><br>
                            <div class="form-group">
                                <label for="technology" class="control-label">{{ trans('messages.000086') }}</label>
                                <select id="technology" name="technology" class="form-control">
                                    @if (count($technologies)>0)
                                        @foreach($technologies as $data)
                                            <option value="{{ $data['technology'] }}" {{ $data['selected'] }}>{{ $data['technology'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="input-group margin-check">
                                <span class="input-group-addon"> 
                                    <input name="read" id="read" type="checkbox">
                                </span> 
                                <label for="read" class="form-control">{{ trans('messages.000025').': Read' }}</label>
                            </div>
                            <div class="input-group margin-check">
                                <span class="input-group-addon"> 
                                    <input name="get_params" id="get_params" type="checkbox">
                                </span> 
                                <label for="get_params" class="form-control">{{ trans('messages.000025').': Get Params' }}</label>
                            </div>
                            <br>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <button type="button" id="btnEnviar" class="btn btn-success btn-block">
                                            {{ trans('messages.buttons.11') }}
                                        </button>
                                    </div>                                    
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <button type="button" id="exportPdf" class="btn btn-success btn-block">
                                            <i class="fa fa-file-pdf-o"></i> {{trans('messages.buttons.19')}}
                                        </button> 
                                    </div>                                    
                                </div>
                            </div>

                        </div>
                    </div>
                </form>

                <div class="hr-line-dashed"></div>
                
                <div class="row">
                    <div id="renderHtml" class="col-sm-12">
                        &nbsp;
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<iframe id="targetFrame" name="targetFrame" src="" width="0" height="0" style="border: none;"></iframe>
@endsection


@section('scripts')
    <script src="{{ url('js/jquery.numeric.min.js') }}"></script>
    <script type="text/javascript">

        function validar(){
            var validate=true;
            var labels='';
            var checks=0;

            if ( $("#load_type").val()=='individual' && $("#subscriber_identity").val().length<15 ) {
                validate=false;
                labels+="{{ trans('messages.000027').' ' }}";
            }

            if ( $("#load_type").val()=='multiple' && $("#subscriber_identity_file").val().length==0 ) {
                validate=false;
                labels+="{{ trans('messages.000026').' ' }}";
            }

            if( $('#read').prop('checked') ) {
                checks++;
            }

            if( $('#get_location').prop('checked') ) {
                checks++;
            }

            if( $('#get_params').prop('checked') ) {
                checks++;
            }

            if( $('#massive_fail').prop('checked') ) {
                checks++;
            }
            
            if ( checks==0 ) {
                validate=false;
                labels+="{{ trans('messages.000068').' ' }}";
            }

            if (!validate) {
                showAlert("{{ trans('messages.000024') }}", "{{ trans('messages.000069') }} "+labels);
                return false;
            }
            return true;
        }

        $(document).ready(function() { 

            // ------------------------------------------------------
            //$("form#formgeters").submit(function(evt){
            //evt.preventDefault(); 
            //});

            // ------------------------------------------------------
            // Validación de campos numéricos.
            $('.numeric').numeric();

            // ------------------------------------------------------
            // Habilitar el tipo de carga de IMSI.
            $( "#load_type" ).change(function() {
                $("#subscriber_identity").val('');
                if( $(this).val()=='individual' ){
                    $("div#unitaria").show(1000);
                    $("div#multiple").hide(1000);
                    $("#subscriber_identity_file").val('');
                }else{
                    $("div#unitaria").hide(1000);
                    $("div#multiple").show(1000);
                    $("#subscriber_identity").val('');
                }
            });

            // ------------------------------------------------------
            // Enviar el formulario por Ajax.            
            $( "#btnEnviar" ).click(function() {
                if( !validar()){
                    return false;
                }
                // ------------------------------------------------------
                $("#spinnerLoader").show();
                $("#renderHtml").html('&nbsp;');
                var formData = new FormData($('form#formgeters')[0]);
                $.ajax({
                    url : "{{ url('Argentina/troubleshooting/services').'?render=html' }}",
                    type: 'POST',
                    data: formData,
                    cache: false,
                    processData: false,
                    contentType: false,
                    async: true,
                    enctype: 'multipart/form-data',
                    dataType : 'html',
                    success : function( response ) {
                        // --------------------------------------------------
                        // código a ejecutar si la petición es satisfactoria;
                        $("#renderHtml").html( response );
                        $('html, body').stop().animate({
                            scrollTop: $("#renderHtml").offset().top
                        }, 1500, 'easeInOutExpo');
                        //$("#renderHtml").append( renderHtml );
                        //console.log(response);

                        // --------------------------------------------------
                        /*if( response.errors.length!==0 ) { 
                            var renderError ='';
                            $.each(response.errors, function(id, dato){
                                renderError+='<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>';
                                renderError+=dato+'</div>';
                            });
                            showModal("{{ trans('messages.00052') }}", renderError, "&nbsp;");
                        }*/
                        // --------------------------------------------------
                    },error : function( data, status ) {
                        showMessage("error", "{{ trans('messages.000023') }}:", "{{ trans('messages.000059') }}" );
                    },complete : function( data, status ) {
                        $("#spinnerLoader").hide();
                    }
                });
                // ------------------------------------------------------
            });

            // ------------------------------------------------------
            $( "#exportPdf" ).click(function() {
                if( !validar()){
                    return false;
                }
                var msj ='<div class="bg-info p-xs b-r-sm text-center"> En unos momentos se generará el pdf y se descargará a su PC.</div>';
                $("#renderHtml").html(msj);
                setTimeout(function(){
                    $("#renderHtml div").hide( "slow" );
                }, 8000);
                //$( "#exportPdf" ).attr("disabled", "disabled");
                $( "form#formgeters" ).submit();
            });

        });
    </script>
@endsection