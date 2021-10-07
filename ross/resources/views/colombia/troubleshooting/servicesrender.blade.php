<section>
	@if (count($datahtml)>0)
		<h2>{!! trans('messages.000050').' '.$count !!}</h2>
		<div id="accordion" class="panel-group">
		<?php $rowid=0; ?>
	    @foreach($datahtml as $imsi => $data)
		    <?php 
            $cell='panel-primary';
            if ($data['errors']>0) { $cell='panel-danger'; }
            $in = $rowid==0 ? 'in' : '';
            $bool = $rowid==0 ? 'true' : 'false';
		    ?>
            <div class="panel {{$cell}}">
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne_{{$rowid}}" aria-expanded="false" class="collapsed">
                            {!! trans('messages.000168') !!} <b>{{$imsi}}</b>
                        </a>
                    </h5>
                </div>
                <div id="collapseOne_{{$rowid}}" class="panel-collapse collapse {{$in}}" aria-expanded="{{$bool}}">
                    <div class="panel-body">
			            @foreach ($data as $group => $info) 
			                @if ( $group!='errors' and !empty($info) ) 
			                	<h2><strong>{{trans('messages.000167')}}: Provisioning {{ucwords(str_replace("_", " ", $group)) }}</strong></h2>
			                	<ul>
			                	@foreach ($info as $key => $value)
			                		<ol>{!!$key.': '.$value!!}</ol>
			                	@endforeach
			                	</ul>
			                @endif
			            @endforeach 
                    </div>
                </div>
            </div>
            <?php $rowid++; ?>
	    @endforeach
	@else
		<h2>{!! trans('messages.000166') !!}.</h2>
	@endif
</section>