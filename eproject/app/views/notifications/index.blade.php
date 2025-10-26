@extends('layout.main', array('hide_ribbon'=>true))

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-lightbulb-o"></i> {{trans('notifications.notifications')}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">

            <div class="text-right">
                {{ $notifications->links() }}
            </div>

            <div class="well">

            @if ( !empty($notifications['data']) )
                @foreach ( $notifications['data'] as $date => $notificationItems )
                <table class="table table-striped table-forum">
                    <thead>
                        <tr>
                            <th colspan="2">{{{ PCK\Base\NotificationDateHelper::generateDateFormat($date) }}}</th>
                            <th class="text-center hidden-xs hidden-sm" style="width: 200px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ( $notificationItems as $notification )
                        <tr>
                            <td class="text-center" style="width: 40px;"><i class="fa fa-envelope fa-2x text-muted"></i></td>
                            <td>
                                <h4 class="{{{ ! $notification['read'] ? 'unread' : 'read' }}}">
                                    {{ HTML::link($notification['url'], sprintf($notification['extra'], $notification['from']['name'])) }}
                                </h4>
                            </td>
                            <td class="text-center hidden-xs hidden-sm">
                                <a href="javascript:void(0);">{{ $notification['from']['name'] }}</a>
                                <br>
                                <small><i>{{{ Carbon\Carbon::parse($notification['created_at'])->format('h:i a') }}}</i></small>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endforeach
            @else
                <div class="alert alert-warning fade in text-center">
                    <i class="fa-fw fa fa-envelope-open"></i>
                    {{trans('notifications.youHaveNoNotification')}}
                </div>
            @endif
            </div>

            <div class="text-right">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection