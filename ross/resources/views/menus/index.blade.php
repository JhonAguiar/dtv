@extends('layouts.backend')


@section('styles')
    <style type="text/css">
    	.bg-change{background: #D6DCDC;}
    	.required{color: red; font-weight: bold;}
    	.bg-parent{background: #DDE3E9;}
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
	                <h5>{{ trans('messages.000131') }}</h5>
	                <div class="ibox-tools">
	                	<button id="btnAdd" type="button" class="btn btn-outline btn-xs btn-info">
	                		<i class="fa fa-plus"></i> {{ trans('messages.buttons.16') }}
	                	</button>
	                    <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
	                </div>
	            </div>
	            @if (count($menus)>0)
	            	<div class="ibox-content ibox-form">
						<div class="table-responsive">
							<table width="100%" class="table table-bordered table-hover">
							    <thead>
								    <tr>
								        <th class="text-center" width="5%">#</th>
								        <th>{{ trans('messages.000173') }}</th>							        
								        <th>{{ trans('messages.000133') }}</th>
								        <th>{{ trans('messages.000140') }}</th>
								        <th>{{ trans('messages.000141') }}</th>							        
								        <th>{{ trans('messages.000135') }}</th>
								        <th>{{ trans('messages.000109') }}</th>
								        <th width="6%" colspan="2">{{ trans('messages.000110') }}</th>
								    </tr>
							    </thead>
							    <tbody id="table_rows">
									@foreach($menus as $info)
										<?php 
											$class="font-normal";
											if ($info->parent_id==0) {
												$class="font-bold text-info";
											}
										?>
									    <tr id="row_{{ $info->id }}" class="@if($info->parentid==0) bg-parent @endif">
									        <td class="text-center {{$class}}"><b><i class="{{ $info->icon }}"></i></b></td>
									        <td>{{ $info->country }}</td>
									        <td class="{{$class}}">{{ $info->name }}</td>
									        <td class="{{$class}}">{!! ( !empty($info->parent) ? $info->parent : '<b>N/A</b>' ) !!}</td>
									        <td class="{{$class}}">{{ $info->description }}</td>
									        <td>{{ $info->controller }}</td>
									        <td class="">
									        	<strong>
										        	@if($info->status==1)
										        		<span style="color: green;">{{ trans('messages.000111') }}</span>
										        	@else
										        		<span style="color: red;">{{ trans('messages.000112') }}</span>
										        	@endif
										        </strong>
										    </td>

										    <td class="text-center {{$class}}">
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
							    </tbody>
							</table>
						</div>
						{{ $menus->links() }}
		        	</div>
		        @endif
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
		            <label for="parent_id" class="control-label col-sm-4">{{ trans('messages.000138') }}</label>
			        <div class="col-sm-8">
			            <select id="parent_id" name="parent_id" class="form-control">
			                <option value="">{{ trans('messages.000113') }}</option>
							@if (count($parents)>0)
								@foreach($parents as $parent)
									<option value="{{ $parent->id }}">{{ $parent->name }}</option>
								@endforeach
							@endif
			            </select>
			        </div>
		        </div>

		        <div class="form-group">
		            <label for="name" class="control-label col-sm-4">{{ trans('messages.000133') }} <span class="required">(*)</span></label>
		            <div class="col-sm-8">
		            	<input type="text" id="name" name="name" class="form-control" minlength="3" maxlength="35" required="true">
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="description" class="control-label col-sm-4">{{ trans('messages.000141') }} <span class="required">(*)</span></label>
		            <div class="col-sm-8">
		            	<input type="text" id="description" name="description" class="form-control" minlength="10" maxlength="50" required="true">
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="url_access" class="control-label col-sm-4">{{ trans('messages.000134') }}</label>
		            <div class="col-sm-8">
		            	<input type="text" id="url_access" name="url_access" maxlength="60" class="form-control">
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="controller" class="control-label col-sm-4">{{ trans('messages.000135') }}</label>
		            <div class="col-sm-8">
		            	<input type="text" id="controller" name="controller" maxlength="60" class="form-control">
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="country" class="control-label col-sm-4">{{ trans('messages.000173'). old('status')}} <span class="required">(*)</span></label>
			        <div class="col-sm-8">
			            <select id="country" name="country" class="form-control">
			                <option value="All">All</option>
			                <option value="Arg">Arg</option>
			                <option value="Col">Col</option>
			            </select>
			        </div>
		        </div>

		        <div class="form-group">
		            <label for="status" class="control-label col-sm-4">{{ trans('messages.000109'). old('status')}} <span class="required">(*)</span></label>
			        <div class="col-sm-8">
			            <select id="status" name="status" class="form-control">
			                <option value="">{{ trans('messages.000113') }}</option>
			                <option value="1">{{ trans('messages.000111') }}</option>
			                <option value="0">{{ trans('messages.000112') }}</option>
			            </select>
			        </div>
		        </div>

		        <div class="form-group">
		            <label for="key_language" class="control-label col-sm-4">{{ trans('messages.000142') }}</label>
		            <div class="col-sm-8">
		            	<input type="text" id="key_language" name="key_language" value="menu.000" class="form-control">
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="visible" class="control-label col-sm-4">{{ trans('messages.000149') }} <span class="required">(*)</span></label>
		            <div class="col-sm-8">
	                    <select id="visible" name="visible" class="form-control">
	                        <option value="S">Visible</option>
	                        <option value="N">Oculto</option>
	                    </select>
		            </div>
		        </div>

		        <div class="form-group">
		            <label for="visible" class="control-label col-sm-4">&nbsp;</label>
		            <div class="col-sm-4">
						<button type="submit" id="btnSend" class="btn btn-block btn-success">&nbsp;</button>
		            </div>
		            <div class="col-sm-4">
						<button type="reset" id="btnReset" class="btn btn-block btn-warning">&nbsp;</button>
		            </div>		            
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
		var optionSelection ="<option value=''>{{trans('messages.000113')}}</option>";

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
				            scrollTop: jQuery("#messagesAjaxModal").offset().top
				        }, 2000);
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
						var name_parent="<b>N/A</b>";
						if (row.parent) {
							name_parent=row.parent;
						}
						if(row.parent_id==0){
							rowdata+= '<tr id="row_'+row.id+'" class="bg-parent">';
						}else{
							rowdata+= '<tr id="row_'+row.id+'" class="">';
						}
						rowdata+= '<td class="text-center"><b><i class="'+row.icon+'"></i></b></td>';
						rowdata+= '<td>'+row.country+'</td>';
						rowdata+= '<td>'+row.name+'</td>';
						rowdata+= '<td>'+name_parent+'</td>';						
						rowdata+= '<td>'+row.description+'</td>';
						rowdata+= '<td><strong><span style="color: '+row.color+';">'+row.textstatus+'</span></strong></td>';
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
			$('form#'+nameForm).attr('action', "{{ route('menus.store') }}");
			$('#_method').val("POST");
			$('#_token').val("{{ csrf_token() }}");
			$('#_action').val( actionType );
			$('#id').val('');				
			$('#title_form').html("{{ trans('messages.000139') }}");
			$('#btnSend').html("{{ trans('messages.buttons.16') }}"); // 
			$('#btnReset').html("{{ trans('messages.buttons.17') }}"); //
			$.get('menus/parent', function (data) {
                var options = optionSelection;
                if( data.length!==0 ) {
	                $.each(data, function(id, dato){
	                	options +='<option value="'+dato.id+'">'+dato.name+'</option>';
	                });
					$('#parent_id').html(options);
				}
			});
			$('#crudModalAjax').modal('show');
		}

		function updateRecord( secretid ) {
			actionType = "update";
			if ( secretid ) {
				$('form#'+nameForm).trigger("reset");
				$('form#'+nameForm).attr('action', "{{ url('menus') }}/"+secretid );
				$('#_method').val("PUT");
				$('#_token').val("{{ csrf_token() }}");
				$('#_action').val( actionType );
				$('#id').val(secretid);				
				$('#title_form').html("{{ trans('messages.000121') }}");
				$('#btnSend').html("{{ trans('messages.buttons.06') }}"); // 
				$('#btnReset').html("{{ trans('messages.buttons.17') }}"); //
				
				$.get('menus/parent', function (data) {
	                var options = optionSelection;
	                if( data.length!==0 ) {
		                $.each(data, function(id, dato){
		                	options +='<option value="'+dato.id+'">'+dato.name+'</option>';
		                });
						$('#parent_id').html(options);
					}
				}).always(function() {
					$.get('menus/'+secretid +'/edit', function (data) {
						$('#parent_id').val(data.parent_id);
						$('#name').val(data.name);
						$('#description').val(data.description);
						$('#url_access').val(data.url_access);
						$('#controller').val(data.controller);
						$('#visible').val(data.visible);
						$('#status').val(data.status);
						$('#key_language').val(data.key_language);
						$('#country').val(data.country);
					});
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
							url: "{{ url('menus') }}"+'/'+secretid,
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
								console.log('Error:', data);
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

            $("#url_access, #controller").keyup(function() {
                $(this).val($(this).val().trim());
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