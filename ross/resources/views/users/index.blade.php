@extends('layouts.backend')

@section('styles')
    <style type="text/css">
		.product-imitation {text-align: center;padding: 10px 2px;}
		.product-name {font-size: 14px;}
		.reset-style{border: none;margin: auto;padding: 2px;}
	</style>
@endsection

@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])

@section('content')
    <div class="ibox float-e-margins animated fadeInRightBig">
        <div class="ibox-title">
            <h5>{{ trans('messages.000090') }}</h5>
            <div class="ibox-tools">
                <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
            </div>
        </div>
        <div class="ibox-content">
			
        	<!--
			<div class="row">
			    <div class="col-sm-4">
			        <div class="form-group">
			            <label class="control-label" for="product_name">Nombres</label>
			            <input type="text" id="product_name" name="product_name" value="" placeholder="Product Name" class="form-control">
			        </div>
			    </div>
			    <div class="col-sm-4">
			        <div class="form-group">
			            <label class="control-label" for="status">Email</label>
			            <select name="status" id="status" class="form-control">
			                <option value="1" selected="">Enabled</option>
			                <option value="0">Disabled</option>
			            </select>
			        </div>
			    </div>			    
			    <div class="col-sm-2">
			        <div class="form-group">
			            <label class="control-label" for="price">Rol</label>
			            <input type="text" id="price" name="price" value="" placeholder="Price" class="form-control">
			        </div>
			    </div>
			    <div class="col-sm-2">
			        <div class="form-group">
			            <label class="control-label" for="quantity">Pais</label>
			            <input type="text" id="quantity" name="quantity" value="" placeholder="Quantity" class="form-control">
			        </div>
			    </div>
			</div>

			<div class="hr-line-dashed"></div>
			-->

			@if (count($users)>0)
		        <div class="row">
		            <div id="data_return" class="col-sm-12">&nbsp;</div>            
		        </div>
				<div class="row">
                    @foreach($users as $info)
		                <div class="col-md-3">
		                    <div class="ibox">
		                        <div class="ibox-content product-box">
		                            <div class="product-imitation">
		                                <center><img src="{{$info->url_avatar}}" class="img-responsive img-rounded" width="92%"></center>
		                            </div>
		                            <div class="product-desc">
		                            	<a class="product-name">{{$info->fullname}}</a>
		                                <div class="text-normal">{{$info->email}}</div>
										<div class="row">
										    <div class="col-lg-10 col-sm-10" style="padding-left: 8px; padding-right: 8px;">
											    <select id="select_{{$info->username}}"  data-id="{{$info->username}}" class="form-control input-sm change-roles" disabled style="border: none; padding: 2px; margin: 2px;">
											    	@if (count($roles)>0)
											        	@foreach($roles as $role)
											            	<option value="{{$role->id}}" @if($info->role_id == $role->id)selected="true"@endif>{{$role->name}}</option>
											        	@endforeach
											    	@endif
											    </select>
										    </div>
										    <div class="col-lg-2 col-sm-2">
										    	<i data-id="{{$info->username}}" class="fa fa-edit pull-right toogle-role" title=" {{trans('messages.000152')}} " style="margin: 10px; cursor: pointer;"></i>
										    </div>
										</div>
		                                <div class="text-normal">{{$info->country}}</div>
		                                <div class="text-normal">{{$info->last_session}}</div>
		                            </div>
		                        </div>
		                    </div>
		                </div>
                    @endforeach
            	</div>
            @endif
        </div>
    </div>
@endsection


@section('scripts')
    <script type="text/javascript">
    	$(document).ready(function () {
			
			$.ajaxSetup({headers: {'x-csrf-token': $('meta[name="csrf-token"]').attr('content')}});

			$(".toogle-role").click(function() {
				var id=$(this).attr('data-id');
				if ($('#select_'+id).is('[disabled=""]')) {
					$('#select_'+id).prop("disabled", false);
				}else{
					$('#select_'+id).prop("disabled", true);
				}				
			});

			$(".change-roles").change(function() {
				var username = $(this).attr('data-id');
				var text_role = $("#select_"+username+" option:selected" ).text();
				var role_id = $("#select_"+username).val();
				var question = "¿{{trans('messages.000153')}} "+text_role+" {{trans('messages.000154')}} {{$info->fullname}}?";
				swal({
                    title: "{{trans('messages.000085')}}", // El titulo
                    text: question, // La pregunta
                    showCancelButton: true,
                    cancelButtonText: "{{trans('messages.buttons.03')}}",//Cancelar
                    confirmButtonText: "{{trans('messages.buttons.04')}}",//Confirmar
                    confirmButtonColor: "#293846",
                    closeOnConfirm: true,
                    closeOnCancel: true
				},function(isConfirm) {
				    if (isConfirm) {
						$("#spinnerLoader").show();
						$.ajax({
							type: "POST",
							url: "{{url('users/role')}}",
							data : { 
						    	_token : "{{csrf_token()}}",
						    	username : username,
						    	role_id : role_id
						    },
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
	                                $('#select_'+username).prop("disabled", true);
                                }
                                $("#data_return").html( renderHtml );
                                $('html, body').stop().animate({
                                    scrollTop: $("#data_return").offset().top
                                }, 1500, 'easeInOutExpo');
						    },error : function( data, status ) {
						    	showMessage("error", "{{ trans('messages.000023') }}:", "{{ trans('messages.000059') }}" );
						    },complete : function( data, status ) {
						    	$("#spinnerLoader").hide();
						    },
							dataType: 'json'
						});
				    }
				});

			});

    	});
    </script>
@endsection