@if (Session::has('flash_notification.message'))
    <?php
        $notifyMsgType = Session::get('flash_notification.level');
        $message = Session::get('flash_notification.message');
    ?>

    @if(in_array($notifyMsgType, ['error', 'success', 'info', 'warning']))
        <script type="text/javascript">
            $(document).ready(function() {
                notifyMsg("{{ $notifyMsgType }}", "{{ $message }}", '', true);
            });
        </script>
    @endif
@endif