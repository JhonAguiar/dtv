@extends('layouts.backend')

@section('styles')
    <style type="text/css">
		.bg-photo {background: #E8B10D;background: linear-gradient(to left, #BA4A00, #F1C40F );color: #ffffff;}
		.profile-image img {width: 124px;height: 124px;}
		.profile-info {margin-left: 148px;}
		ul {list-style: none; padding: 0;margin: 4px;}
    </style>
    <link href="{{url('inspinia/css/plugins/c3/c3.min.css')}}" rel="stylesheet">
@endsection

@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])

@section('content')
	<div class="wrapper wrapper-content animated fadeInRight">
        <!-- <div class="row">
            <div class="col-md-4">
                <div class="profile-image">
                    <img src="{{$avatar}}" width="100%" class="img-thumbnail img-responsive" alt="profile">
                </div>
                <div class="profile-info">
                    <div class="">
                        <h2 class="no-margins">{{ ucwords(@$user->fullname) }}</h2>
                        <h4>{{ @$user->email }}</h4>
                        <h5>{{ 'Rol: '.@$user->rol }}</h5>
		                <a href="{{ url('/users/photo') }}" title="{{ trans('messages.000005') }}">
		                	<i class="fa fa-picture-o" style="color: #243747"></i>&nbsp;
		                	<small>{{ trans('messages.000005') }}</small> 
		                </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
	            <div class="widget-text-box" style="background: #C15B03; color: #FFF;">
	                <small class="text-primary"><b>{!! trans('messages.000001').': '.$date !!}</b></small><br>
	                <small class="text-primary">{!! trans('messages.000008').': '.$ip !!}</small><br>
	                <small class="text-primary">{!! trans('messages.000006').': '.$device !!}</small><br>
	                <small class="text-primary">{!! trans('messages.000003').': '.$navigator !!}</small><br>
	            </div>
            </div>
            <div class="col-md-4">&nbsp;</div>
        </div> -->
    </div>


    @if( Session::get('country') and \Session::get('country')=='Col' )
	    <div class="ibox float-e-margins">
	        <div class="ibox-title">
	            <h5>{{trans('messages.000158')}}</h5>
	        </div>
	        <div class="ibox-content">
				<div class="row">
				    <div class="col-sm-4">
				        <div class="form-group">
				            <select name="type_query" id="type_query" class="form-control">
				                <option value="1013" selected="true">{{trans('messages.000159')}}</option>
				                <option value="1014">{{trans('messages.000160')}}</option>
								<option value="1015">{{trans('messages.000179')}}</option>
				            </select>
				        </div>
				    </div>		    
				    <div class="col-sm-4">
				        <div class="form-group">
				            <select name="date_query" id="date_query" class="form-control">
				                <option value="ultima_semana" selected="true">{{trans('messages.000161')}}</option>
				                <option value="ultimos_quince">{{trans('messages.000162')}}</option>
				                <option value="ultimo_mes">{{trans('messages.000163')}}</option>
				            </select>
				        </div>
				    </div>
				    <div class="col-sm-4">
				        <div class="form-group">
				            <select name="tecnology" id="tecnology" class="form-control">
				                <option value="LTE" selected="true">LTE</option>
				            </select>
				        </div>
				    </div>
				</div>
				<div class="hr-line-dashed"></div>
				<h3 id="tipe_query_text" class="p-sm"></h3>

				<div id="graficas" class="row">
				    <div class="col-lg-6">
				    	<div id="chart_pie"></div>
				    </div>
				    <div class="col-lg-6"> 
				    	<div id="datarow"></div>
				    </div>
				</div>
				<div class="hr-line-dashed"></div>
				<div class="row">
				    <div class="col-lg-12">
				    	<div id="chart_line"></div>
				    </div>
				</div>
				<div class="hr-line-dashed"></div>
				<br>

	        </div>
	    </div>
    @endif

@endsection


@section('scripts')
    <!-- C3.js | D3-based reusable chart library -->
    <script src="{{url('inspinia/js/plugins/d3/d3.min.js')}}"></script>
    <script src="{{url('inspinia/js/plugins/c3/c3.min.js')}}"></script>
    @if( Session::get('country') and \Session::get('country')=='Col' )
	    <script type="text/javascript">
	    	function generateGraphicColombia(){
	            $("#spinnerLoader").show();
	            $.post("{{ url('Colombia/graphics')}}",{
	                "_token" : "{{ csrf_token() }}",
					"type_query" : $('#type_query').val(),
					"date_query" : $('#date_query').val(),
					"tecnology" : $('#tecnology').val(),
	            },function(data){
	            	if( data.result.chartpie && data.result.chartpie.length!==0 ) {
						// ----------------------------------------
						var arrayPie=[];
						$.each(data.result.chartpie, function(id, dato){
							arrayPie.push([id+': '+dato, dato]);
						});             
			            var chart1 = c3.generate({
			                bindto: '#chart_pie',
			                data:{
			                    columns: arrayPie,
			                    type : 'pie',
			                    labels: true,
			                }
			            });
			            var text = $("#type_query option:selected").text()+', '+$("#date_query option:selected").text()+', '+$("#tecnology option:selected").text();
			            $("#tipe_query_text").html('Informe de '+text);
			            // ----------------------------------------
			            // https://c3js.org/samples/timeseries.html
						var chart2 = c3.generate({
						    bindto: '#chart_line',
						    data: {
						        x: 'x',
						        columns: eval( data.result.chartline )
						    },
						    axis: {
						        x: {
						            type: 'timeseries',
						            tick: {format: '%Y-%m-%d'}
						        }
						    }
						});
						var rowdata='<table class="table table-bordered table-striped">';
						$.each(data.result.rowdata, function(fech, group){
							rowdata+='<tr><td>'+fech+'</td>';
							rowdata+='<tr><td><ul>';
							contador = 0;
							$.each(group, function(id, data){
								rowdata+='<li><i class="fa fa-address-card-o"></i> '+id+': '+data+'</li>';
								contador++;
							}); 
							rowdata+='</ul></td></tr>';
						});
						rowdata+='</table>'; 
						$("#datarow").html(rowdata);
						var height=$("#chart_pie").height();
						$("#datarow").css({"overflow": "auto", "height": height});
	            	}
	            	if ( data.errors && data.result.errors!==0  ) {
                        var renderError ='';
                        $.each(data.errors, function(id, dato){
                            renderError+='<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>';
                            renderError+=dato+'</div>';
                        });
                        $("#tipe_query_text").html(renderError);
	            	}
	            }).fail(function(){
	                showMessage("error", "{{ trans('messages.000023') }}:", "{{ trans('messages.000059') }}" );
	            }).always(function(){
	                $("#spinnerLoader").hide("slow");
	            },"json");
	    	}
	    	
	        $(document).ready(function () {
	            generateGraphicColombia();
	            $( "#type_query, #tecnology, #date_query" ).change(function() {
	                generateGraphicColombia();
	            });
	        });
	    </script>
    @endif
@endsection}
