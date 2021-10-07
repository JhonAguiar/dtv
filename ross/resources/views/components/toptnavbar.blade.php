                <div class="row border-bottom">
                    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                        <div class="navbar-header">
                            <a class="navbar-minimalize minimalize-styl-2 btn btn-success " href="#">
                                <i class="fa fa-bars"></i>
                            </a>
                            <div class="navbar-form-custom" style="margin-top: 15px;">
                                <a href="{{ url('/') }}">
                                    <img src="{{ url('img/logo-blue.png') }}" height="30" class="d-inline-block align-top" alt="">
                                </a>
                            </div>
                        </div>
                        <ul class="nav navbar-top-links navbar-right">
                            <li>
                                <span class="m-r-sm welcome-message">
                                    <!--{{ config('app.name', '') }}-->
                                    {{ config('appross.sigla', '').' - '.trans('messages.appname') }}
                                </span>
                            </li>

                            <!--
                            <li class="dropdown">
                                <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                                    <i class="fa fa-bell"></i>  <span class="label label-primary">2</span>
                                </a>
                                <ul class="dropdown-menu dropdown-alerts">
                                    <li>
                                        <a href="mailbox.html">
                                            <div>
                                                <i class="fa fa-envelope fa-fw"></i> You have 16 messages
                                                <span class="pull-right text-muted small">4 minutes ago</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="profile.html">
                                            <div>
                                                <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                                <span class="pull-right text-muted small">12 minutes ago</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <div class="text-center link-block">
                                            <a href="notifications.html">
                                                <strong>See All Alerts</strong>
                                                <i class="fa fa-angle-right"></i>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            -->

                            @auth
                                <!-- Inicar sesion -->
                                <li>
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i>{{ trans('messages.000081') }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                                </li>
                            @else
                                <!-- Cerrar sesion -->
                                <li><a href="{{ route('login') }}">{{ trans('messages.000011') }}</a></li>
                            @endauth
                        </ul>
                    </nav>
                </div>
                    