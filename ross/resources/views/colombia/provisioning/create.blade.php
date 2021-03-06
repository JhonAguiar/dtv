@extends('layouts.backend')

@section('styles')
    <style type="text/css"></style>
@endsection

@section('title', @$headers['title'])
@section('controller', @$headers['controlle'])
@section('method', @$headers['method'])

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{{ trans('messages.000061') }}</h5>
                <div class="ibox-tools">
                    <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                </div>
            </div>
            <div class="ibox-content ibox-form">

                <form name="create" id="create" action="{{ url('Colombia/provisioning') }}" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" readonly="readonly">
                    <p class="text-justify">{{ trans('messages.000078') }}</p>
                    <div class="hr-line-dashed"></div>
                    
                    <div class="row">
                        <!-- tipo de carga -->
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="load_type" class="control-label">{{ trans('messages.000009') }}</label>
                                <select id="load_type" name="load_type" class="form-control">
                                    <option value="individual" selected="selected">{{ trans('messages.000027') }}</option>
                                    <option value="multiple">{{ trans('messages.000026') }}</option>
                                </select>
                            </div>
                        </div>
                        <!-- IMSI -->
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div id="unitaria">
                                    <label for="subscriber_identity" class="control-label">{{ trans('messages.000027') }}</label>
                                    <input type="text" id="subscriber_identity" name="subscriber_identity" class="form-control positive" minlength="15" maxlength="15" placeholder="732176000123456">
                                </div>
                                <div id="multiple" style="display: none;">
                                    <label for="subscriber_identity_file" class="control-label">{{ trans('messages.000026') }}</label>
                                    <input type="file" id="subscriber_identity_file" name="subscriber_identity_file" class="form-control">
                                </div>
                            </div>
                        </div>
                        <!-- Perfiles -->
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="profile" class="control-label">{{ trans('messages.000022') }}</label>
                                <select id="profile" name="profile" class="form-control">
                                    <option value="" selected="">{{ trans('messages.000016') }}</option>
                                    @if (count($profiles)>0)
                                        @foreach($profiles as $profile)
                                            <option value="{{ $profile }}">{{ $profile }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <!-- Boton -->
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="quantity">&nbsp;</label>
                                <button type="submit" id="btnEnviar" class="btn btn-success btn-block">{{ trans('messages.buttons.09') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <div class="hr-line-dashed"></div>
                
                <div class="row">
                    <div id="data_return" class="col-sm-12">
                        &nbsp;
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection



@section('scripts')
    <script src="{{ url('js/jquery.numeric.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() { 
            
            // ------------------------------------------------------
            // Validaci??n de campos num??ricos.
            $('.numeric').numeric();

            $('.positive').numeric(
                {negative: false},
                function () {
                    alert('No negative values');
                    this.value = '';
                    this.focus();
                }
            );
            
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
            $("form#create").submit(function(evt){  
                evt.preventDefault();

                var validate=true;
                var labels='';
                
                if ( $("#load_type").val()=='individual' && $("#subscriber_identity").val().length<15 ) {
                    validate=false;
                    labels+="{{ trans('messages.000027').' ' }}";
                }

                if ( $("#load_type").val()=='multiple' && $("#subscriber_identity_file").val().length==0 ) {
                    validate=false;
                    labels+="{{ trans('messages.000026').' ' }}";
                }

                if ( $("#profile").val()=='') {
                    validate=false;
                    labels+="{{ trans('messages.000022').' ' }}";
                }

                if (!validate) {
                    showAlert("{{ trans('messages.000024') }}", "{{ trans('messages.000069') }} "+labels);
                    return false;
                }

                // Mensaje de confirmar.
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
                        // ------------------------------------------------------
                        $("#spinnerLoader").show();
                        $("#data_return").html('&nbsp;');
                        var formData = new FormData($('form#create')[0]);
                        $.ajax({
                            url : $('form#create').attr('action'),// la URL para la petici??n
                            type: 'POST',// especifica si ser?? una petici??n POST o GET
                            data: formData,
                            cache: false,
                            processData: false,
                            contentType: false,
                            async: true,
                            enctype: 'multipart/form-data',
                            dataType : 'json',// el tipo de informaci??n que se espera de respuesta
                            success : function( response ) {
                                // --------------------------------------------------
                                // c??digo a ejecutar si la petici??n es satisfactoria;
                                var renderHtml ='';
                                $.each(response.result, function(id, dato){
                                    var style='alert-info';
                                    if (dato.code!=0) {
                                        style='alert-danger';
                                    }
                                    renderHtml+='<div class="alert '+style+' alert-dismissable">';
                                    renderHtml+='<button aria-hidden="true" data-dismiss="alert" class="close" type="button">??</button>';
                                    renderHtml+='<img src="{{ url("img/icons/dtv-blue.png") }}" height="15">&nbsp;&nbsp;<strong>IMSI: '+dato.imsi+'</strong> '+dato.info;
                                    renderHtml+='</div>';
                                });
                                $("#data_return").html( renderHtml );
                                $('html, body').stop().animate({
                                    scrollTop: $("#data_return").offset().top
                                }, 1500, 'easeInOutExpo');
                                //$("#data_return").append( renderHtml );
                                //console.log(response);

                                // --------------------------------------------------
                                if( response.errors.length!==0 ) { 
                                    var renderError ='';
                                    $.each(response.errors, function(id, dato){
                                        renderError+='<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">??</button>';
                                        renderError+=dato+'</div>';
                                    });
                                    showModal("{{ trans('messages.000020') }}", renderError, "&nbsp;");
                                }
                                // --------------------------------------------------
                            },error : function( data, status ) {
                                // c??digo a ejecutar si la petici??n falla;
                                showMessage("error", "{{ trans('messages.000023') }}:", "{{ trans('messages.000059') }}" );
                            },complete : function( data, status ) {
                                // c??digo a ejecutar sin importar si la petici??n fall?? o no
                                $("#spinnerLoader").hide();
                            }
                        });
                        // ------------------------------------------------------
                    }
                });

            });

        });
    </script>
@endsection