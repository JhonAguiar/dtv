@extends('layouts.backend')

@section('styles')
    <style type="text/css">
		.product-imitation {text-align: center;padding: 10px 2px;}
		.bg-warning{background: orange;}
        .cell{margin-bottom: 5px;}
	</style>
@endsection

@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])

@section('content')
    <div class="ibox float-e-margins animated fadeInRightBig">
        <div class="ibox-title">
            <h5>{{ trans('messages.000092') }}</h5>
            <div class="ibox-tools">
                <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
            </div>
        </div>
        <div class="ibox-content">

            <form name="check_systems" id="check_systems" action="{{ url('systems/check') }}" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" readonly="readonly">
                <p class="text-justify">{{ trans('messages.000093') }}</p>
                <div class="hr-line-dashed"></div>
				@if (count($servers)>0)
					<div class="row">
	                    @foreach($servers as $key => $info)
			                <div class="col-md-3 cell">
	                            <div class="input-group margin-check">
	                                <span class="input-group-addon"> 
	                                    <input type="checkbox" id="{{ 'chec_'.$key}}" name="servers[]" value="{{ $info['ip'] }}">
	                                </span> 
	                                <label for="{{ 'chec_'.$key}}" class="form-control"><i class="fa fa-desktop"></i> {{ $info['name'] }}</label>
	                            </div>
			                </div>
	                    @endforeach
	                    <div class="col-md-3 cell">
                            <div class="form-group">
                                <button type="submit" id="btnEnviar" class="btn btn-success btn-block">
                                    {{ trans('messages.buttons.14') }}
                                </button>
                            </div>
                        </div>
	            	</div>
	            @endif
            </form>

            <div class="hr-line-dashed"></div>
            
            <div class="row">
                <div id="data_return" class="col-sm-12">
                    &nbsp;
                </div>
            </div>

        </div>
    </div>
@endsection


@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() { 
            

            // ------------------------------------------------------
            // Enviar el formulario por Ajax.
            $("form#check_systems").submit(function(evt){  
                evt.preventDefault();

				var checkedNum = $('input[name="servers[]"]:checked').length;
				if (!checkedNum) {
                	showAlert("{{ trans('messages.000024') }}", "{{ trans('messages.000069') }} ");
                	return false;
				}

                // ------------------------------------------------------
                $("#spinnerLoader").show();
                $("#data_return").html('&nbsp;');
                var formData = new FormData($('form#check_systems')[0]);
                $.ajax({
                    url : $('form#check_systems').attr('action'),// la URL para la petición
                    type: 'POST',// especifica si será una petición POST o GET
                    data: formData,
                    cache: false,
                    processData: false,
                    contentType: false,
                    async: true,
                    enctype: 'multipart/form-data',
                    dataType : 'json',// el tipo de información que se espera de respuesta
                    success : function( response ) {
                        //console.log(response);
                        if( response.result.length!==0 ) {
	                        var render ='<div class="row">';
	                        $.each(response.result, function(id, dato){
	                        	var img = "{{ url('img/icons/server.png') }}";
	                        	var style = "background: #A9DFBF;";
	                    		if (dato.status !=='Worked') {
	                    			style = "background: #F5B7B1;";
	                    		}	                    		
								render+='<div class="col-md-3">';
								render+='<div class="ibox">';
								render+='<div class="ibox-content product-box">';
								render+='<div class="product-imitation" style="'+style+'"><center><img src="'+img+'" width="128"></center></div>';
								render+='<div class="product-desc">';
								render+='<small class="text-muted"><b>'+dato.name+'</b></small><br>';
								render+='<small class="text-muted">'+dato.ip+'</small><br>';
								render+='<small class="text-muted">'+dato.status+'</small><br>';
								render+='<small class="text-muted">'+dato.time+'</small><br>';
								render+='</div>';
								render+='</div>';
								render+='</div>';
								render+='</div>';
	                        });
	                        render+='</div>';
	                        $("#data_return").html( render );
	                        $('html, body').stop().animate({ 
	                        	scrollTop: $("#data_return").offset().top 
	                        }, 1500, 'easeInOutExpo');
                        }
                    },error : function( data, status ) { // código a ejecutar si la petición falla;
                        showMessage("error", "{{ trans('messages.000023') }}:", "{{ trans('messages.000059') }}" );
                    },complete : function( data, status ) {// código a ejecutar sin importar si la petición falló o no
                        $("#spinnerLoader").hide();
                    }
                });
                // ------------------------------------------------------

            });

        });
    </script>
@endsection