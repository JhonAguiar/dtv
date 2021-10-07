@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        <p><strong><i class="fa fa-bell-slash"></i> {!! @trans('messages.000115').': ' !!}</strong></p>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{!! @$error !!}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Mensaje exitoso -->
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        <strong>{!! @trans('messages.000115').': ' !!}</strong>{!! Session::get('success') !!}
    </div>
@endif

<!-- Mensaje -->
@if(Session::has('info'))
    <div class="alert alert-info alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        <strong>{!! @trans('messages.000115').': ' !!}</strong>{!! Session::get('info') !!}
    </div>
@endif

<!-- Advertencia -->
@if(Session::has('warning'))
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        <strong>{!! @trans('messages.000115').': ' !!}</strong>{!! Session::get('warning') !!}
    </div>
@endif

<!-- Error -->
@if(Session::has('danger'))
    <div class="alert alert-danger alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        <strong>{!! @trans('messages.000115').': ' !!}</strong>{!! Session::get('danger') !!}
    </div>
@endif