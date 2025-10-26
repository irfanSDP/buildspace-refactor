<script>

    <?php
        $noOfGroup = 0;
        foreach($contractGroups as $contractGroup)
        {
            if( $myContractGroup->id != $contractGroup->id ) $noOfGroup++;
        }
    ?>

    function shareFolder(id)
    {
        var totalGroup = '{{{ $noOfGroup }}}';

        $(".checkbox-select_group").prop("checked", false);

        var token = $('meta[name=_token]').attr("content");

        $.get( "{{ route('projectDocument.sharedFolderInfo', array($project->id)) }}/"+id ).done(function( data ) {
            var checkedList = [];

            $(".checkall").prop("checked", (data.length == totalGroup) ? true : false );

            $.each(data, function(key, value){
                $("#"+value+"-checkbox_group_share_folder").prop("checked", true);
            });

            $('#folderShareModal').modal({
                keyboard: false
            }).one('click', '#folderShareBtn', function(e) {
                app_progressBar.toggle();
                e.preventDefault();

                $('.checkbox-select_group:checked').each(function() {
                    var values = $(this).val();

                    checkedList.push(values);
                });

                $.when.apply($, checkedList).done(function() {
                    $.ajax({
                        url: "{{ route('projectDocument.shareFolder', array($project->id)) }}",
                        type: 'post',
                        data: {"checked[]": checkedList, _token :token, folderId: id},
                        success:function(resp){
                            if(resp.success){
                                app_progressBar.maxOut();
                                document.location.reload();
                            }
                        }
                    });
                });
            });
        });

        return false;
    }
</script>