<script>
    function deleteFolder(id)
    {
        var token = $('meta[name=_token]').attr("content");

        $('#folderDeleteConfirm').modal({ backdrop: 'static', keyboard: false })
             .one('click', '#folderDeleteBtn', function() {
                 app_progressBar.toggle();
                 $.ajax({
                     url: "{{ route('projectDocument.deleteFolder', array($project->id)) }}",
                     type: 'post',
                     data: {id: id, _token :token},
                     success:function(resp){
                         if(resp.success){
                             app_progressBar.maxOut();
                             $('#folderDeleteConfirm').modal('hide');
                             document.location.reload();
                         }
                     }
                 });
             });

        return false;
    }
</script>