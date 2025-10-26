<script>
    function sendNotifications(id)
    {
        eproject.generateRoutes(function(routes){
            $.post(routes['projectDocument.sendNotifications'], {_token: _csrf_token})
            .done(function(data){
                if(data.success){
                    SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('notifications.notificationSent') }}");
                }
                else{
                    SmallErrorBox.refreshAndRetry();
                }
            })
            .fail(function(data){
                SmallErrorBox.refreshAndRetry();
            });
        }, {"projectDocument.sendNotifications": [{{$project->id}}, id]});

        return false;
    }
</script>