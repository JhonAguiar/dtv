//-----------------------------------------------------------------
// MENSAJES DE TIPO ALERT.
function showAlert( title='', text='' ){
    swal({
        title: title,
        text: text,
        confirmButtonColor: "#293846",
    });
}

//-----------------------------------------------------------------
// MENSAJES DE TIPO VENTANA MODAL.
function showModal( title='', content='', footer='' ){
    $('#AppModal .modal-title').html( title );
    $('#AppModal .modal-body').html( content );
    $('#elementModal .modal-footer').html( footer );
    $('#AppModal').modal({
        backdrop: 'static',
        keyboard: true
    });
}

//-----------------------------------------------------------------
// MOSTRAR LOS MENSAJES DE RESPUESTA.
function showMessage(statusAlerta, titulo, mensaje){
    var i = -1;
    var toastCount = 0;
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-bottom-full-width",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "14000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    return toastr[statusAlerta](mensaje, titulo);
}



//-----------------------------------------------------------------
// Esta función es para activar el evento on.
(function($){
    $.each(['show', 'hide'], function(i, ev){
        var el = $.fn[ev];
        $.fn[ev] = function(){
            this.trigger(ev);
            return el.apply(this, arguments);
        };
    });
})(jQuery);
var tiempo = {hora: 0, minuto: 0, segundo: 0 };
var tiempo_corriendo = null;
$("#spinnerLoader").on('show', function() {
    tiempo_corriendo = setInterval(function(){
        // Segundos
        tiempo.segundo++;
        if(tiempo.segundo >= 60){
            tiempo.segundo = 0;
            tiempo.minuto++;
        }      
        // Minutos
        if(tiempo.minuto >= 60){
            tiempo.minuto = 0;
            tiempo.hora++;
        }
        $("#hour").text(tiempo.hora < 10 ? '0' + tiempo.hora : tiempo.hora);
        $("#minute").text(tiempo.minuto < 10 ? '0' + tiempo.minuto : tiempo.minuto);
        $("#second").text(tiempo.segundo < 10 ? '0' + tiempo.segundo : tiempo.segundo);
    }, 1000);
});
$("#spinnerLoader").on('hide', function() {
    clearInterval(tiempo_corriendo);
    tiempo_corriendo = null;
    tiempo = {hora: 0, minuto: 0, segundo: 0 };
    $("#hour").text(tiempo.hora < 10 ? '0' + tiempo.hora : tiempo.hora);
    $("#minute").text(tiempo.minuto < 10 ? '0' + tiempo.minuto : tiempo.minuto);
    $("#second").text(tiempo.segundo < 10 ? '0' + tiempo.segundo : tiempo.segundo);
});


//-----------------------------------------------------------------
// GENERAR BOTON DE VOLVER ARRIBA. 
$(function(){
    // Genera el boton y cuando se hace click regresa al inicio de página.          
    $("body").append("<a href='#' id='volverarriba'>{{ trans('messages.000080') }}</a>");
    $(window).scroll(function(){
        if($(this).scrollTop() > 70){ 
            $('#volverarriba').fadeIn();
        }else{
            $("#volverarriba").fadeOut();
        }
    });
    $(document).on("click","#volverarriba",function(e){
        e.preventDefault();
        $("html, body").stop().animate({ scrollTop: 0 }, 2000);
        //$("html, body").stop().animate({ scrollTop: 0 }, "slow");
        return false; 
    });
});

//-----------------------------------------------------------------
$(function(){
    $( "#video_modal" ).click(function( event ) {
        event.preventDefault();
        $('#AppModalVideo').modal('show',{
            backdrop: 'static',
            keyboard: true
        });
    });
    $( ".close, .close-modal" ).click(function( event ) {
        $("iframe").each(function() { 
            var src= $(this).attr('src');
            $(this).attr('src',src);  
        });
        $("video").each(function(){
            $(this).get(0).pause();
        });
    });
});

