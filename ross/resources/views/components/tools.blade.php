                <div class="row wrapper border-bottom white-bg page-heading">
                    
                    <!-- Zona de título, migas de pan, regiones y botón de ayuda. -->
                    <div class="col-sm-6">
                        <h2>@yield('title')</h2>
                        <ol class="breadcrumb">
                            
                            @if ( isset($headers['country']) and !empty($headers['country']) )
                                <i class="fa fa-star-half-empty" title="{{ $headers['country'].'_'.$controller.'_'.$method }}"></i>&nbsp;
                            @else
                                <i class="fa fa-star-half-empty" title="{{ $controller.'_'.$method }}"></i>&nbsp;
                            @endif

                            @if ( isset($headers['country']) and !empty($headers['country']) )
                                <li><a href=""><strong>{{ @$headers['country'] }}</strong></a></li>
                            @endif  
                            <li><a href=""><strong>{{ @$controller }}</strong></a></li>
                            <li class="active"><a href=""><strong>{{ @$method }}</strong></a></li>

                        </ol>
                    </div>

                    <div class="col-sm-6">
                        <div class="title-action">
                            
                            <!-- Grupo de seleccionar region -->
                            <div class="btn-group">
                                @if( Session::get('country') )
                                    <?php 
                                        $country = \Session::get('country');
                                        $infoRegion = \App\Customs\Collections::getCollectionRegionsById($country); 
                                    ?>                                    
                                    <button data-toggle="dropdown" class="btn btn-outline btn-default btn-rounded dropdown-toggle" aria-expanded="true" style="padding: 1px 12px;">
                                        <img src="{{ url('img/flags/'.$country.'.png') }}" title="{{ trans('messages.000014').': '.$infoRegion->name }}" height="30">&nbsp;&nbsp;<span class="caret"></span>
                                    </button>
                                @else
                                    <button data-toggle="dropdown" class="btn btn-default btn-rounded dropdown-toggle" aria-expanded="true">
                                        <strong>{{ trans('messages.buttons.02') }}</strong>&nbsp;<span class="caret"></span>
                                    </button>
                                @endif
                                <ul class="dropdown-menu">
                                    @if ($temp_regions->count()>0)
                                        @foreach($temp_regions as $info)
                                            <?php $id=$info['id']; ?>
                                            <li><a href="{{ url('/country', [$id]) }}"><img src="{{ url('img/flags/'.$id.'.png') }}" height="30"> {{ $info['name'] }}</a></li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div> 

                            <!-- Grupo de seleccionar idioma -->
                            <div class="btn-group">
                                <button data-toggle="dropdown" class="btn btn-default btn-rounded dropdown-toggle" aria-expanded="true">
                                    @if( Session::has('locale') )
                                        <?php $var = trans("messages.".Session::get('locale')); ?>
                                        <strong>{{ $var }}</strong>&nbsp;<span class="caret"></span>
                                    @else
                                        <?php $var = trans('messages.es'); ?>
                                        <strong>{{ $var }}</strong>&nbsp;<span class="caret"></span>
                                    @endif
                                </button>                              
                                <ul class="dropdown-menu">
                                    @if (count($temp_languages)>0)
                                        @foreach($temp_languages as $key => $lang)
                                            <li><a href="{{ url('/language', [$key]) }}">{{ $lang }}</a></li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div> 

                            <!-- Videos de la interfaz -->
                            @if ( isset($headers['video']) and !empty($headers['video']) )
                                <a id="video_modal" href="{{ $headers['video'] }}" title="{{ trans('messages.000079') }}" class="btn btn-default btn-rounded">
                                    <i class="fa fa-play"></i>&nbsp;&nbsp;{!! trans('messages.buttons.08') !!}
                                </a> 
                            @endif          
                        </div>
                    </div>

                </div>