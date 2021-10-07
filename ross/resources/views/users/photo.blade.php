@extends('layouts.backend')

@section('styles')
    <link href="{{ url('inspinia/css/plugins/cropper/cropper.min.css') }}" rel="stylesheet">
    <style type="text/css">
        .preview-16-9 {width: 280px; height: 157px; margin-top: 10px;}
        .preview-1-1 {width: 250px; height: 250px; margin-top: 10px;}
        .boxmin{background: #FFF; border: solid 1px #ccc;}
        .boxmin *{background: #FFF; color: #333 !important;}
        #upload-img label{cursor: pointer;}
    </style>
@endsection

@section('title', @$headers['title'])
@section('controller', @$headers['controller'])
@section('method', @$headers['method'])

@section('content')
    @if( $device=='Ordenador')
		<div class="row">
		    <div class="col-lg-12">
		        <div class="ibox float-e-margins">
		            <div class="ibox-title">
		                <h5>{{ trans('messages.000063') }}</h5>
		                <div class="ibox-tools">
		                    <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
		                </div>
		            </div>
		            <div class="ibox-content ibox-form">

			            <form id="upload-img" action="{{ url('users/photo_upload') }}" enctype="multipart/form-data" method="post" /> 
			                <input type="hidden" id="data_width" name="data_width" />
			                <input type="hidden" id="data_height" name="data_height" />
			                <input type="hidden" id="data_x" name="data_x" />
			                <input type="hidden" id="data_y" name="data_y" />
			                <input type="file" id="inputImage" name="imagen_crop" accept="image/*" class="hide" />   
			                <input type="hidden" name="_token" value="{{ csrf_token() }}" readonly="readonly" />
		                    <p class="text-justify">{{ trans('messages.000072') }}</p>
		                    <div class="hr-line-dashed"></div>
	                        <div class="row" style="padding-top: 20px; ">
	                            <div class="col-md-7">
	                                <div class="image-crop">
	                                    <img id="img-up" src="{{ $avatar }}" class="img-responsive" width="100%" />
	                                </div>
	                            </div>
	                            <div class="col-md-1">&nbsp;</div>
	                            <div class="col-md-4">
	                                <center><div class="img-preview preview-1-1"></div></center><br>
	                                <div class="text-center">
	                                    <label for="inputImage" class="btn btn-primary btn-block">{{ trans('messages.000010') }}</label>
	                                    <button id="zoomIn" class="btn btn-success btn-block" type="button">Zoom [+]</button>
	                                    <button id="zoomOut" class="btn btn-success btn-block" type="button">Zoom [-]</button>
	                                    <button type="submit" class="btn btn-primary btn-lg btn-block">{{ trans('messages.000005') }}</button>
	                                    <hr id="width-croop">
	                                </div>
	                            </div>
	                            <span class="clear"></span>
	                        </div>
		                </form>

		                <div class="hr-line-dashed"></div>

		            </div>
		        </div>
		    </div>
		</div>
    @else
        <center><h1>{{ trans('messages.000035') }}</h1></center>
    @endif
@endsection


@section('scripts')
    <!-- Image cropper -->
    <script src="{{ url('inspinia/js/plugins/cropper/cropper.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            
            var a=$("#width-croop").width();
            //var b=((parseInt(a)*9)/16);
            var b=((parseInt(a)*1)/1);
            b=Math.round(b);
            $(".preview-1-1").css("width", a+"px");
            $(".preview-1-1").css("height", b+"px");

            var $image = $(".image-crop > img");
            $($image).cropper({
                //aspectRatio: 16/9,     
                aspectRatio: 1/1, 
                preview: ".img-preview",
                done: function(data) {
                    $("#data_x").val(data.x);
                    $("#data_y").val(data.y);
                    $("#data_width").val(data.width);
                    $("#data_height").val(data.height);
                }
            });

            var $inputImage = $("#inputImage");
            if (window.FileReader) {
                $inputImage.change(function() {
                    var fileReader = new FileReader(), files=this.files, file;
                    if (!files.length) {
                        return;
                    }
                    file = files[0];
                    if (/^image\/\w+$/.test(file.type)) {
                        fileReader.readAsDataURL(file);
                        fileReader.onload = function () {
                            //$inputImage.val("");
                            $image.cropper("reset", true).cropper("replace", this.result);
                        };
                    } else {
                        showMessage("Please choose an image file.");
                    }
                });
            }else{
                $inputImage.addClass("hide");
            }

            $("#zoomIn").click(function() {
                $image.cropper("zoom", 0.1);
            });

            $("#zoomOut").click(function() {
                $image.cropper("zoom", -0.1);
            });

            $("#rotateLeft").click(function() {
                $image.cropper("rotate", 45);
            });

            $("#rotateRight").click(function() {
                $image.cropper("rotate", -45);
            });

            $("#setDrag").click(function() {
                $image.cropper("setDragMode", "crop");
            });

            $("#download").click(function() {
                window.open($image.cropper("getDataURL"));
            });

            $("#upload-img").submit(function( event ) {
                if ( $( "#inputImage" ).val()!='' ){
                    return;
                }
                showAlert("{{ trans('messages.000024') }}", "{{ trans('messages.000049') }} ");
                event.preventDefault();
            });

        });
    </script>
@endsection