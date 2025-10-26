<?php 
use PCK\GeneralSettings\GeneralSetting;
?>
<!-- HEADER -->
<header id="header" class="logo-header">
    <div id="logo-group">
        <!-- PLACE YOUR LOGO HERE -->
        <a href="{{ route('home.index') }}" id="logo" class="d-flex">
            @if(file_exists(public_path('img/company-logo.png')))
                <img src="{{ asset('img/company-logo.png') }}" alt="{{{ \PCK\MyCompanyProfiles\MyCompanyProfile::all()->first()->name }}}">
            @else
                <img src="{{ asset('img/buildspace-login-logo.png') }}" alt="BuildSpace eProject">
            @endif
        </a>
    </div>

    @if($isLicenseValid)
    <div id="hide-menu" class="d-flex">
        <a href="javascript:void(0);" class="header-icon" data-action="toggleMenu" data-placement="left" title="{{ trans('navigation/mainnav.collapseMenu') }}">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    @endif

    <!-- pulled right: nav area -->
    <div class="pull-right d-flex">
    @if($isLicenseValid)
        @if(GeneralSetting::count()>0 && GeneralSetting::first()->view_tenders==true)
            <ul class="header-dropdown-list" style="margin: auto !important;">
                <li>
                    <a class="btn btn-primary btn-sm pull-right header-btn m-auto" href="{{{ route('open_tenders.main_project') }}}">
                        View Tenders
                    </a>
                </li>
            </ul>
        @endif

        <ul class="header-dropdown-list">
            <li>
                <a href="#" id="header-notification" class="header-icon" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-cube"></i>
                </a>

                <div class="dropdown-menu dropdown-notification pull-right">
                    <div class="dropdown-header bg-trans-gradient d-flex rounded-top">
                        <div class="col-xs-12">
                            <h5 class="txt-color-white text-center">
                                {{ trans('navigation/mainnav.shortcuts') }}
                                <br/>
                                <small class="txt-color-white">{{ trans('navigation/mainnav.appsAndModules') }}</small>
                            </h5>
                        </div>
                    </div>
                    <div class="nav-menu-header-dropdown custom-scroll">
                        @include('layout.partials.app_shortcut_menu_list')
                    </div>
                </div>
            </li>
        </ul>

        <ul class="header-dropdown-list">
            <li>
                <a href="#" id="my-processes-show" class="header-icon" aria-expanded="false">
                    <i class="far fa-list-alt"></i>
                    <span hidden><b data-id="count" class="badge bg-color-red bounceIn animated"></b></span>
                </a>
            </li>
        </ul>

        <ul class="header-dropdown-list">
            <li>
                <a href="#" id="header-notification" class="header-icon" data-toggle="dropdown" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    @if ( $unreadNotificationsCount )
                        <b class="badge bg-color-red bounceIn animated">{{{ $unreadNotificationsCount }}}</b>
                    @endif
                </a>

                <div class="dropdown-menu dropdown-notification pull-right">
                    <div class="dropdown-header bg-trans-gradient d-flex rounded-top">
                        <div class="col-xs-12">
                            <h5 class="txt-color-white text-center">
                                {{trans('notifications.notifications')}}
                                <br/>
                                <small class="txt-color-white">{{trans('notifications.headerPopupTitle', ['count' => $unreadNotificationsCount])}}</small>
                            </h5>
                        </div>
                    </div>
                    <div class="nav-menu-header-dropdown custom-scroll">
                        <div class="">
                            @if(!empty($latest10Notifications))
                                <ul class="notification">
                                    @foreach($latest10Notifications as $notification)
                                    <li class="@if($notification['read']) read @else unread @endif">
                                        <a href="{{{$notification['url']}}}" class="d-flex">
                                        <i class="fa fa-envelope fa-lg text-muted"></i>&nbsp;&nbsp;
                                        <h4>
                                            {{{sprintf($notification['extra'], $notification['from']['name'])}}}
                                            <br />
                                            <small>{{{ Carbon\Carbon::parse($notification['created_at'])->format('d M Y') }}} {{{ Carbon\Carbon::parse($notification['created_at'])->format('h:i a') }}}</small>
                                        </h4>
                                    </a>
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="alert alert-warning fade in text-center">
                                    <i class="fa-fw fa fa-envelope-open"></i>
                                    {{trans('notifications.youHaveNoNotification')}}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="panel-footer text-align-right">
                        <a href="{{ route('notifications.index') }}">{{trans('notifications.viewAllNotifications')}}</a>
                    </div>
                </div>
            </li>
        </ul>
    @endif

        <ul class="header-dropdown-list">
            <li>
                <a href="#" class="header-icon profile-image" data-toggle="dropdown" aria-expanded="false">
                    <i class="far fa-user-circle"></i>
                </a>
                <ul class="dropdown-menu pull-right" style="width:280px;">
                    <li>
                        <div class="dropdown-header bg-trans-gradient d-flex rounded-top">
                            <div class="col-sm-4 profile-pic">
                                <i class="far fa-5x fa-fw fa-user-circle"></i>
                            </div>
                            <div class="col-sm-8">
                                <h5 class="profile-title txt-color-white text-truncate">
                                    {{{Confide::user()->name}}}
                                    @if(Confide::user()->company)
                                    <br>
                                    <small class="txt-color-white">{{{Confide::user()->company->name}}}</small>
                                    @endif
                                </h5>
                                <p class="txt-color-white text-truncate text-muted">
                                    <i class="far fa-envelope"></i>&nbsp;<small>{{{Confide::user()->email}}}</small>
                                </p>
                            </div>
                        </div>

                        <div class="dropdown-item text-right pull-right">
                            <a href="{{ route('user.updateMyProfile') }}" class="btn btn-success btn-sm me-2" title="{{ trans('navigation/mainnav.myProfile') }}">
                            <i class="fa fa-user"></i></span> {{trans('navigation/mainnav.myProfile')}}</a>

                            <a href="{{ route('users.logout') }}" id="userLogoutBtn" class="btn btn-danger btn-sm" data-placement="left" title="{{ trans('auth.signOut') }}"
                            data-action="userLogout"
                            data-logout-msg="{{ trans('auth.signOutMessage') }}">
                            <i class="fa fa-sign-out-alt"></i></span> {{trans('auth.signOut')}}</a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>

    </div>
    <!-- end pulled right: nav area -->
</header>
<!-- END HEADER -->
