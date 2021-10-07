    <div id="crudModalAjax" class="modal inmodal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">@yield('modalAjaxTitle')</h4>
                </div>
                <div class="modal-body">
                    <div id="messagesAjaxModal"></div>
                    @yield('modalAjaxContent')
                </div>
                <div class="modal-footer">
                    @yield('modalAjaxButtons')
                    <button type="button" class="btn btn-success close-modal" data-dismiss="modal">
                        {{ trans('messages.buttons.01') }}
                    </button>
                </div>
            </div>
        </div>
    </div>