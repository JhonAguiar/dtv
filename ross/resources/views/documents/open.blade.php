@extends('layouts.backend')

@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])

<!--
    Ejemplos;
    http://example.com/doc.pdf#Chapter5
    http://example.com/doc.pdf#page=5
    http://example.com/doc.pdf#page=3&zoom=200,250,100
    http://example.com/doc.pdf#zoom=100
    http://example.com/doc.pdf#page=72&view=fitH,100

    page=pagenum: Especifica el número de páginas a ver. La primera página del documento es la número 1 y no la 0.
    zoom=scale: Define los factores de zoom y scroll mediante valores enteros o floats. Por ejemplo, un scale de 100 indica un zoom del 100%.
    view=Fit: Define la vista de la página mostrada
    scrollbar=1|0: Activar o desactivar el scroll
    toolbar=1|0: Activar o desactivar la barra de herramientas
    statusbar=1|0: Activar o desactivar la barra de estado
    navpanes=1|0: Activar o desactivar los paneles o pestañas

    <embed src="files/Brochure.pdf#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="100%" height="600px" />
-->
    
@section('content')
    <div class="row animated fadeInRightBig">
        <div class="col-md-12">
            <div class="embed-responsive embed-responsive-16by9" width="100%" height="100%">
                <embed src="{{ url($file_open.'#zoom=scale') }}" type="application/pdf" class="embed-responsive-item" width="100%" height="100%" ></embed>
            </div>
            <hr>
        </div>
    </div>
    <div class="hr-line-dashed"></div>
@endsection


@section('scripts')
    <script type="text/javascript"></script>
@endsection