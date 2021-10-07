@extends('layouts.backend')


@section('styles')
    <style type="text/css">
    	.bg-change{background: #D6DCDC;}
    	.required{color: red; font-weight: bold;}
    </style>
@endsection


@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])


@section('content')
	<div class="row">
	    <div class="col-lg-12">
	        <div class="ibox float-e-margins">
	            <div class="ibox-title">
	                <h5>{{ trans('messages.000098') }}</h5>
	                <div class="ibox-tools">
	                	<button id="btnAdd" type="button" class="btn btn-outline btn-xs btn-info">
	                		<i class="fa fa-plus"></i> {{ trans('messages.buttons.16') }}
	                	</button>
	                    <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
	                </div>
	            </div>
	            <div class="ibox-content ibox-form">
					<div class="table-responsive">
						<table class="table table-hover table-bordered">
						    <thead>
							    <tr>
							        <th>{{ trans('messages.000107') }}</th>
							        <th>{{ trans('messages.000108') }}</th>
							        <th>{{ trans('messages.000109') }}</th>
							        <th>{{ trans('messages.000119') }}</th>
							        <th width="6%" colspan="2">{{ trans('messages.000120') }}</th>
							    </tr>
						    </thead>
						    <tbody id="table_rows">
								@if (count($roles)>0)
									@foreach($roles as $info)
								    <tr id="row_{{ $info->id }}">
								        <td>{!! '<b><i class="fa fa-user"></i> '.$info->name.'</b>' !!}</td>
								        <td>{{ $info->description }}</td>
								        <td>
								        	<strong>
									        	@if($info->status==1)
									        		<span style="color: green;">{{ trans('messages.000111') }}</span>
									        	@else
									        		<span style="color: red;">{{ trans('messages.000112') }}</span>
									        	@endif
									        </strong>
									    </td>
									    <td><a href="{{ url('roles/permisions/'.base64_encode($info->id)) }}">{{ trans('messages.000119') }}</a></td>
								        <td class="text-center">
								        	@if($info->protected=='N')
								        		<a href="javascript:updateRecord('{{ base64_encode($info->id) }}')" class="btn btn-outline btn-xs btn-success"><i class="fa fa-edit"></i></a>
								        	@else
								        		<b title="Protected"><i class="fa fa-lock"></i></b>
								        	@endif
								        </td>
										
								        <td class="text-center">
								        	@if($info->protected=='N')
								        		<a href="javascript:deleteRecord('{{ base64_encode($info->id) }}')" class="btn btn-outline btn-xs btn-danger"><i class="fa fa-trash-o"></i></a>
								        	@else
								        		<b title="Protected"><i class="fa fa-lock"></i></b>
								        	@endif
								        </td>
								    </tr>
									@endforeach
								@endif
						    </tbody>
						</table>
						{{ $roles->links() }}
					</div>
		        </div>
	        </div>
	    </div>
	</div>
@endsection



@section('modalAjaxTitle', @$headers['title'])
@section('modalAjaxContent')
    <form id="formdata" name="formdata" method="POST" class="form-horizontal" action="" autocomplete="off">
    	<input type="hidden" name="_method" id="_method" readonly="readonly">
        <input type="hidden" name="_token" id="_token" readonly="readonly" value="{{ csrf_token() }}">
        <input type="hidden" name="_action" id="_action" readonly="readonly">
        <input type="hidden" name="id" id="id" readonly="readonly">

        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5 id="title_form">&nbsp;</h5>
                <div class="ibox-tools">
                	<a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                </div>
            </div>
            <div class="ibox-content ibox-form">

		        <div class="form-group">
		            <label for="name" class="control-label col-sm-4">{{ trans('messages.000107') }} <span class="required">(*)</span></label>
		            <div class="col-sm-8">
		            	<input type="text" id="name" name="name" value="" class="form-control">
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="description" class="control-label col-sm-4">{{ trans('messages.000108') }} <span class="required">(*)</span></label>
		            <div class="col-sm-8">
		            	<input type="text" id="description" name="description" value="" class="form-control">
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="status" class="control-label col-sm-4">{{ trans('messages.000109') }} <span class="required">(*)</span></label>
			        <div class="col-sm-8">
			            <select id="status" name="status" class="form-control">
			                <option value="">{{ trans('messages.000113') }}</option>
			                <option value="1">{{ trans('messages.000111') }}</option>
			                <option value="0">{{ trans('messages.000112') }}</option>
			            </select>
			        </div>
		        </div>

				<div class="col-sm-4 col-sm-offset-4">
				    <button type="submit" id="btnSend" class="btn btn-success">&nbsp;</button>
				    <button type="reset" id="btnReset" class="btn btn-warning">&nbsp;</button>
				</div>

				<div class="hr-line-dashed"></div>
				<div style="height: 80px;">&nbsp;</div>
	        </div>
        </div>
	</form>
@endsection
@section('modalAjaxButtons')

@endsection



@section('scripts')
	<script type="text/javascript">
		// https://www.tutsmake.com/laravel-6-create-ajax-crud-application-example/

		var nameForm = "formdata";
		var actionType = "";

		// --------------------------------------------------------------
		function processRecord(){
			$("#spinnerLoader").show();
            var formData = new FormData($('form#'+nameForm)[0]);
            $.ajax({
                url : $('form#'+nameForm).attr('action'),
                type: 'POST',//POST
                data: formData,
                cache: false,
                processData: false,
                contentType: false,
                async: true,
                enctype: 'multipart/form-data',
                dataType : 'json',
                success : function( response ) {
                    if( response.errors.length!==0 ) { 
                        var renderError ='<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><ul>';
                        $.each(response.errors, function(id, dato){
                            renderError+='<li>'+dato+'</li>';
                        });
                        renderError+='</ul></div>';
                        $("#messagesAjaxModal").html(renderError);
                        $('html, body').stop().animate({
                            scrollTop: $("#messagesAjaxModal").offset().top
                        }, 1500, 'easeInOutExpo');
                    }else{
	                    if( response.result.length!==0 ) {
	                        var renderError ='<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><ul>';
	                        $.each(response.result, function(id, dato){
	                            renderError+='<li>'+dato+'</li>';
	                        });
	                        renderError+='</ul></div>';
	                        $("#messagesAjax").html(renderError);
	                    }

	                    var row = response.row;
						var rowdata = '';
						var msj = "{{ trans('messages.000119') }}";
						rowdata+= '<tr id="row_'+row.id+'" class="bg-change">';
						rowdata+= '<td><b><i class="fa fa-user"></i> '+row.name+'</b></td>';
						rowdata+= '<td>'+row.description+'</td>';
						rowdata+= '<td><strong>';
			        	rowdata+= '<span style="color: '+row.color+';">'+row.textstatus+'</span>';
						rowdata+= '</strong></td>';
						rowdata+= '<td><a href="'+row.permision+'">'+msj+'</a></td>';
						rowdata+= '<td class="text-center">';
						rowdata+= '<a href="javascript:updateRecord(\''+row.encriptid+'\')" class="btn btn-outline btn-xs btn-success"><i class="fa fa-edit"></i></a>';
						rowdata+= '</td>';
						rowdata+= '<td class="text-center">';
						rowdata+= '<a href="javascript:deleteRecord(\''+row.encriptid+'\')" class="btn btn-outline btn-xs btn-danger"><i class="fa fa-trash-o"></i></a>';
						rowdata+= '</td>';
						rowdata+= '</tr>';
						if (actionType == "create") {
							$('#table_rows').prepend( rowdata );
						} else {
							$("#row_"+row.id).replaceWith(rowdata);
						}

	                    $("#spinnerLoader").hide();
                    	$('#crudModalAjax .close').trigger("click");
                    }
                },error : function( data, textStatus ) {
                	console.log(data);
                	console.log(textStatus);
                	if (textStatus=='error') {
                		showMessage("error", "{{ trans('messages.000023') }}:", "{{ trans('messages.000059') }}" );
                	}
                },complete : function( data, textStatus ) {
                	if (textStatus=='success') {
                		$("#spinnerLoader").hide();
                		//$('#crudModalAjax .close').trigger("click");
                	}
                }
            });
		}

		function addRecord() {
			actionType = "create";				
			$('form#'+nameForm).trigger("reset");
			$('form#'+nameForm).attr('action', "{{ route('roles.store') }}");
			$('#_method').val("POST");
			$('#_token').val("{{ csrf_token() }}");
			$('#_action').val( actionType );
			$('#id').val('');				
			$('#title_form').html("{{ trans('messages.000139') }}");
			$('#btnSend').html("{{ trans('messages.buttons.16') }}"); // 
			$('#btnReset').html("{{ trans('messages.buttons.17') }}"); //
			$('#crudModalAjax').modal('show');
		}

		function updateRecord( secretid ) {
			actionType = "update";
			if ( secretid ) {
				$('form#'+nameForm).trigger("reset");
				$('form#'+nameForm).attr('action', "{{ url('roles') }}/"+secretid );
				$('#_method').val("PUT");
				$('#_token').val("{{ csrf_token() }}");
				$('#_action').val( actionType );
				$('#id').val(secretid);				
				$('#title_form').html("{{ trans('messages.000121') }}");
				$('#btnSend').html("{{ trans('messages.buttons.06') }}"); // 
				$('#btnReset').html("{{ trans('messages.buttons.17') }}"); //
				$.get('roles/'+secretid +'/edit', function (data) {
					$('#name').val(data.name);
					$('#description').val(data.description);
					$('#status').val(data.status);
				});
				$('#crudModalAjax').modal('show');
			}
		}

		function deleteRecord( secretid ) {
			//var encodedString = btoa(string);
			//var decodedString = atob(encodedString);
			actionType = "delete";
			if ( secretid ) {
                swal({
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
						$.ajax({
							type: "DELETE",
							url: "{{ url('roles') }}"+'/'+secretid,
							success: function (response) {
								//console.log('Error:', response);
			                    if( response.result.length!==0 ) {
			                        var renderError ='<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><ul>';
			                        $.each(response.result, function(id, dato){
			                            renderError+='<li>'+dato+'</li>';
			                        });
			                        renderError+='</ul></div>';
			                        $("#messagesAjax").html(renderError);
			                        $("#row_"+atob(secretid)).remove();// elimina registro del listado.
			                    }
			                    if( response.errors.length!==0 ) { 
			                        var renderError ='<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><ul>';
			                        $.each(response.errors, function(id, dato){
			                            renderError+='<li>'+dato+'</li>';
			                        });
			                        renderError+='</ul></div>';
			                        $("#messagesAjax").html(renderError);
			                    }
							},
							error: function (data) {
								//console.log('Error:', data);
								showAlert( "{{ trans('messages.000024') }}", data );
							}
						});
                    }
                });
			}
		}

		// --------------------------------------------------------------
		$(document).ready(function () {
			
			// --------------------------------------------------------------
			$.ajaxSetup({headers: {'x-csrf-token': $('meta[name="csrf-token"]').attr('content')}});

			// --------------------------------------------------------------
			$('#btnAdd').click(function () {
				addRecord();
			});

			// --------------------------------------------------------------
            $("form#"+nameForm).submit(function(evt){  
                evt.preventDefault();
                var validate = true;
                if (!validate) {
	                // Algunos datos ingresados en este formulario son incorrectos, por favor verifique la información.
	                showAlert("{{ trans('messages.000024') }}", "{{ trans('messages.000118') }} ");
	                return false;
                }
				// Mensaje de confirmar.
                if ( $('#_action').val()=='update' ) {
	                swal({
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
	                    	processRecord();
	                    }
	                });
                }else if ( $('#_action').val()=='create' ) {
                	processRecord();
                }
            });

		});
	</script>
@endsection