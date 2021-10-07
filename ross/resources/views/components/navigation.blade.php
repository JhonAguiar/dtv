<?php 
    $menuHtml = \App\Models\Role::getMenuHtml();
    $role = \App\Models\Role::where('id', Auth::user()->role_id)->first();
    #echo "<pre>";print_r($menuHtml); die;
?>
    <nav class="navbar-default navbar-static-side" role="navigation">  
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element text-center"> 
                        <a href="{{ url('/') }}" title="{{ config('appross.sigla', '').' - '.trans('messages.appname') }}">
                            <img src="{{ url('img/unnamed.jpg') }}" class="img-circle" style="max-width: 100px;" alt="{{ config('appross.sigla', '') }}" />
                        </a>
                        <a class="dropdown-toggle" href="#">
                            <span class="clear"> 
                                <span class="block m-t-xs">
                                    <strong class="font-bold">{{ ucwords(@Auth::user()->fullname) }}</strong>
                                </span>
                                <span class="text-muted text-xs block">{{@Auth::user()->email}}</span>
                                <span class="text-muted text-xs block">{{$role->name}}</span>
                            </span> 
                        </a>
                    </div>
                    <div class="logo-element">{{ config('appross.sigla', 'DTV') }}</div>
                </li>

                <!-- Menu options -->
                <li><a href="{{ url('/home') }}"><i class="fa fa-home fa-2x"></i> <span class="nav-label">{{ trans('messages.000002') }}</span></a></li>
                @if(!empty($menuHtml))
                    @foreach($menuHtml as $key => $module)
                        <?php 
                            $module_name = trans('messages.'.$module['key_language']); 
                        ?>
                        <li>
                            <a href="#">
                                {!!'<i class="'.@$module['icon'].'"></i>&nbsp;'!!}
                                {!!'<span class="nav-label">'.@$module_name.'</span>'!!}
                                {!!'<span class="fa arrow"></span>'!!}                            
                            </a>
                            <ul class="nav nav-second-level collapse">
                                @if(!empty($module['items']))
                                    @foreach($module['items'] as $k => $item)
                                        <?php 
                                            $item_name = trans('messages.'.$item->key_language); 
                                        ?>
                                        <li>
                                            <a href="{{url($item->url_access)}}">
                                                {!! '<i class="fa fa-angle-right"></i>&nbsp;'.$item_name !!}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                           </ul>
                        </li>
                    @endforeach
                @endif


                <!--
                <li>
                    <a href="#"><i class="fa fa-list"></i> <span class="nav-label">{{ trans('messages.menu.001') }}</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="{{ url('menus') }}">{{ trans('messages.menu.014') }}</a></li>
                        <li><a href="{{ url('roles') }}">{{ trans('messages.menu.015') }}</a></li>
                        <li><a href="{{ url('users') }}">{{ trans('messages.menu.009') }}</a></li>
                    </ul>
                </li>
                @if( Session::get('country') )
                    @if( Session::get('country')=='Col' )
                        <li>
                            <a href="#"><i class="fa fa-list"></i> <span class="nav-label">{{ trans('messages.menu.002') }}</span> <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li><a href="{{ url('Colombia/provisioning/create') }}">{{ trans('messages.menu.004') }}</a></li>
                                <li><a href="{{ url('Colombia/provisioning/suspend-unsuspend') }}">{{ trans('messages.menu.005') }}</a></li>
                                <li><a href="{{ url('Colombia/provisioning/edit') }}">{{ trans('messages.menu.006') }}</a></li>
                                <li><a href="{{ url('Colombia/provisioning/delete') }}">{{ trans('messages.menu.007') }}</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-list"></i> <span class="nav-label">{{ trans('messages.menu.003') }}</span> <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li><a href="{{ url('Colombia/troubleshooting/services') }}">{{ trans('messages.menu.008') }}</a></li>
                            </ul>
                        </li>                        
                    @endif
                    @if( Session::get('country')=='Arg' )
                        <li>
                            <a href="#"><i class="fa fa-list"></i> <span class="nav-label">{{ trans('messages.menu.002') }}</span> <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li><a href="{{ url('Argentina/provisioning/create') }}">{{ trans('messages.menu.004') }}</a></li>
                                <li><a href="{{ url('Argentina/provisioning/edit') }}">{{ trans('messages.menu.006') }}</a></li>
                                <li><a href="{{ url('Argentina/provisioning/delete') }}">{{ trans('messages.menu.007') }}</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-list"></i> <span class="nav-label">{{ trans('messages.menu.003') }}</span> <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li><a href="{{ url('Argentina/troubleshooting/services') }}">{{ trans('messages.menu.008') }}</a></li>
                            </ul>
                        </li>                        
                    @endif                    
                @endif
                <li>
                    <a href="#"><i class="fa fa-list"></i> <span class="nav-label">{{ trans('messages.menu.011') }}</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="{{ url('systems') }}">{{ trans('messages.menu.012') }}</a></li>
                        <li><a href="{{ url('documentlist') }}">{{ trans('messages.menu.013') }}</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#"><i class="fa fa-list"></i> <span class="nav-label">FOTA</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Firmware</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fa fa-list"></i> <span class="nav-label">CPEs</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Device Pairing</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Model Range</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Homologaciones</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Perfiles</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fa fa-list"></i> <span class="nav-label">Topology</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Load eNB / gNB</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Load Topology</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Load Celds</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fa fa-list"></i> <span class="nav-label">Solicitudes Judiciales</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Histórico Cisco</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Histórico Huawei</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Histórico Ericsson BO</a></li>
                        <li><a href="{{ url('/aaaaaaaaaaaaa') }}">Histórico Ericsson ATL</a></li>
                    </ul>
                </li>
            -->
            </ul>
        </div>
    </nav>