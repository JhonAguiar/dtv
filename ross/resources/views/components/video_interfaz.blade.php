@if ( isset($headers['video']) and !empty($headers['video']) )
<div id="AppModalVideo" class="modal inmodal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">{{ trans('buttons.01') }}</span>
                </button>
                <h4 class="modal-title">{{ trans('messages.000079') }}</h4>
            </div>
            <div class="modal-body">
                <div class="video-container">
                    <video id="videoPlayer" poster="{{ url('img/post-video.png') }}" width="100%" preload controls>
                        <source src="{{ $headers['video'] }}" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"'/>
                        <p>Su browser no soporta este tag de video.</p>
                    </video>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success close-modal" data-dismiss="modal">
                    {{ trans('messages.buttons.01') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif 