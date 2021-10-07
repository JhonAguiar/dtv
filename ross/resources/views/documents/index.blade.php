@extends('layouts.backend')

@section('styles')
    <style type="text/css"></style>
@endsection

@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])

@section('content')
	<div class="row animated fadeInRightBig">
	    <div class="col-md-12">
	        <div class="ibox float-e-margins">
	            <div class="ibox-title">
	                <h5>{{ trans('messages.000031') }}</h5>
	                <div class="ibox-tools">
	                    <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
	                </div>
	            </div>
	            <div class="ibox-content">
					<ul class="dd-list">
					    <li class="dd-item">
					        <div class="dd-handle">
					            <a href="{{ url('/document', ['DOC V1.0 Tabulado de errores.pdf']) }}">
					            	<span class="label label-info"><i class="fa fa-file-text"></i></span> Tabulado de errores provisioning
					            </a>
					        </div>
					    </li>
					    <li class="dd-item">
					        <div class="dd-handle">
					            <a href="{{ url('/document', ['CapacitacionEquipoBase-PortalCautivo.pdf']) }}">
					            	<span class="label label-info"><i class="fa fa-file-text"></i></span> Capacitación Portal Cautivo
					            </a>
					        </div>
					    </li>
					    <li class="dd-item">
					        <div class="dd-handle">
					            <a href="{{ url('/document', ['Net Check MANUAL TECNICO.pdf']) }}">
					            	<span class="label label-info"><i class="fa fa-file-text"></i></span> Net Check MANUAL TECNICO
					            </a>
					        </div>
					    </li>
					    <li class="dd-item">
					        <div class="dd-handle">
					            <a href="{{ url('/document', ['NET_Check_ManualUsuario.pdf']) }}">
					            	<span class="label label-info"><i class="fa fa-file-text"></i></span> NETCheck Manual de Usuario
					            </a>
					        </div>
					    </li>
					    <li class="dd-item">
					        <div class="dd-handle">
					            <a href="{{ url('/document', ['Provisioning MANUAL TECNICO.pdf']) }}">
					            	<span class="label label-info"><i class="fa fa-file-text"></i></span> Provisioning MANUAL TECNICO
					            </a>
					        </div>
					    </li>
					    <li class="dd-item">
					        <div class="dd-handle">
					            <a href="{{ url('/document', ['Robot Internet MANUAL TECNICO.pdf']) }}">
					            	<span class="label label-info"><i class="fa fa-file-text"></i></span> Robot Internet MANUAL TECNICO
					            </a>
					        </div>
					    </li>
					</ul>
	            </div>
	        </div>

	    </div>
	</div>
	<div class="hr-line-dashed"></div>
@endsection


@section('scripts')
    <script type="text/javascript"></script>
@endsection