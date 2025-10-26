@if (Session::has('flash_notification.message'))
    @include('layout.partials.flash_message_view', array('notificationLevel' => Session::get('flash_notification.level'), 'message' => Session::get('flash_notification.message')))
@endif