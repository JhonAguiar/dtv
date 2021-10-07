@extends('layouts.backend')


@section('styles')
	<style type="text/css">
    	.margin-check{margin-bottom: 0px;}
    	.bg-parent{background: #DDE3E9;}
    	.role{color: green; font-weight: bold;}
    	.fa-check-square{color: green; font-weight: bold;}
    	.fa-ban{color: red; font-weight: bold;}
    </style>
@endsection


@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])


@section('content')
    <form name="permisions" id="permisions" action="{{ url('roles/permisions') }}" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="{{csrf_token()}}" readonly="readonly">
        <input type="hidden" name="role_id" value="{{$role->id}}" readonly="readonly">
		<div class="ibox float-e-margins">
			<div class="ibox-content">
				<div class="row">
				    <div class="col-lg-8">
				    	<h2>{{trans('messages.000148')}}: <span class="text-navy font-bold">“{{$role->name}}”</span></h2>
			        </div>
				    <div class="col-lg-4 text-right"><br>
			    		<a href="{{url('roles')}}" class="btn btn-outline btn-xs btn-success">
			    			<i class="fa fa-exchange"></i> {{ trans('messages.buttons.18') }}
			    		</a>
			        </div>
				</div>
				<div class="row">
				    <div class="col-lg-12">
				    	<p>En cada grupo si realiza algún cambio debe confirmar presionando el boton <b>Actualizar</b></p>
				    </div>
				</div>
			</div>
		</div>

		@if (count($menus)>0)
	        <div class="row">
	            <div id="data_return" class="col-sm-10">&nbsp;</div>
	            <div class="col-sm-2 text-right p-sm">
					<button id="btnEnviar" type="submit" class="btn btn-sm btn-success">
	            		<i class="fa fa-refresh"></i> {{ trans('messages.buttons.06') }}
	            	</button>
	            </div>            
	        </div>

		    <div id="accordion" class="panel-group">
				@foreach($menus as $k => $module)
		            <div class="panel panel-default">
		                <div class="panel-heading">
		                    <h5 class="panel-title">
		                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse_{{$module['id']}}" @if($k==0) aria-expanded="true" @else aria-expanded="false" @endif>
		                            {{ $module['name'] }}
		                        </a>
		                    </h5>
		                </div>
		                <div id="collapse_{{$module['id']}}" @if($k==0) class="panel-collapse collapse in" aria-expanded="true" style="" @else class="panel-collapse collapse" aria-expanded="false" style="height: 0px;" @endif>
		                    <div class="panel-body">
		                    	@foreach($module['items'] as $menu)
							       	<div id="{{'row_'.$menu['id']}}" class="input-group margin-check">
			                            <span class="input-group-addon"> 
			                                <input 
			                                	type="checkbox" 
			                                	name="menus[]" 
			                                	id="{{'menu_'.$menu['id']}}" 
			                                	data-id="{{$menu['id']}}" 
			                                	value="{{$menu['id']}}" 
			                                	class="ajaxcheck" 
			                                	@if($menu['id']==2) disabled="true" @endif 
			                                	@if($role->id>2 and $menu['id']<5) disabled="true" @endif>
			                            </span> 
			                            <label for="{{'menu_'.$menu['id']}}" class="form-control change-status">
			                                <i class="fa fa-ban" title="{{trans('messages.000112')}}"></i>&nbsp;&nbsp;
			                                <b title="Protected">@if($menu['id']==2)<i class="fa fa-lock"></i>@endif</b>&nbsp;&nbsp; 
			                                {!! $menu['country'].' - '.$menu['name'].': <span class="font-normal">'.$menu['description'].'</span>' !!}
			                            </label>
			                        </div>
		                        @endforeach
		                        <br class="hr-line-dashed">	                	
		                    </div>
		                </div>
		            </div>
			    @endforeach
		    </div>
	    @endif
	</form>
@endsection



@section('scripts')
	<script type="text/javascript">
	    $(document).ready(function () {
	    	var status="";

	    	@foreach($permisions as $check)
	    		status = "{{trans('messages.000111')}}";
	    		$("#menu_{{$check->menu_id}}").prop( "checked", true );
	    		$("#row_{{$check->menu_id}} label i").attr('class','fa fa-check-square').attr('title', status);
	    		@if($check->protected=='S')
	    			$("#menu_{{$check->menu_id}}").attr('disabled', true);
	    			$("#row_{{$check->menu_id}} label b").html('<i class="fa fa-lock"></i>');
	    		@endif
	    	@endforeach

			$(".ajaxcheck").change(function() {
				var menu_id = $(this).attr('data-id');
				if( $(this).is(':checked') ){
					status = "{{trans('messages.000111')}}";
					$("#row_"+menu_id+" label i").attr('class','fa fa-check-square').attr('title', status);
				}else{
					status = "{{trans('messages.000112')}}";
					$("#row_"+menu_id+" label i").attr('class','fa fa-ban').attr('title', status);
				}
			});

            // ------------------------------------------------------
            // Enviar el formulario por Ajax.
			$("form#permisions").submit(function(evt){	 
				evt.preventDefault();
				swal({// Mensaje de confirmar.
                    title: "{{ trans('messages.000085') }}", // El titulo
                    text: "{{ trans('messages.000052') }}", // La pregunta
                    showCancelButton: true,
                    cancelButtonText: "{{ trans('messages.buttons.03') }}",//Cancelar
                    confirmButtonText: "{{ trans('messages.buttons.04') }}",//Confirmar
                    confirmButtonColor: "#293846",
                    closeOnConfirm: true,
                    closeOnCancel: true
				},function(isConfirm) {
				    if (isConfirm) {
						$("#spinnerLoader").show();
						$("#data_return").html('&nbsp;');
						var formData = new FormData($('form#permisions')[0]);
						$.ajax({
						    url : $('form#permisions').attr('action'),// la URL para la petición
						    //data : { id : 123 },// (también es posible utilizar una cadena de datos)
						    type: 'POST',// especifica si será una petición POST o GET
						    data: formData,
							cache: false,
							processData: false,
							contentType: false,
							async: true,
							enctype: 'multipart/form-data',
						    dataType : 'json',// el tipo de información que se espera de respuesta
						    success : function( response ) {
                                var renderHtml ='';
                                if( response.errors.length!==0 ) {
                                    $.each(response.errors, function(id, dato){
                                        renderHtml+='<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'+dato+'</div>';
                                    });
                                }else{
	                                $.each(response.result, function(id, dato){
	                                    renderHtml+='<div class="alert alert-info alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'+dato+'</div>';
	                                });
                                }
                                $("#data_return").html( renderHtml );
                                $('html, body').stop().animate({
                                    scrollTop: $("#data_return").offset().top
                                }, 1500, 'easeInOutExpo');
						    },error : function( data, status ) {
						    	showMessage("error", "{{ trans('messages.000023') }}:", "{{ trans('messages.000059') }}" );
						    },complete : function( data, status ) {
						    	$("#spinnerLoader").hide();
						    }
						});
				    }
				});
			});

	    });
	</script>
@endsection