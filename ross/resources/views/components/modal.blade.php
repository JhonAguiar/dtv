<div id="AppModal" class="modal inmodal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title"><!-- Titular --></h4>
            </div>
            <div class="modal-body">
                <!-- Cuerpo del mensaje -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success close-modal" data-dismiss="modal">
                    {{ trans('messages.buttons.01') }}
                </button>
            </div>
        </div>
    </div>
</div>
<a id="modalClick" href="#" class="close-modal" data-toggle="modal" data-target="#AppModal"></a>