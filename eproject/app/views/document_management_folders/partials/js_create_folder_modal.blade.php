<script>
    function createNewFolder(parentId){
        $('#newFolderModal').modal({
            keyboard: false
        });
        var $form = $( "#newFolderForm" );

        $form.find( "input[name='parent_id']" ).val(parentId);
        return false;
    }

    $( "#newFolderForm" ).submit(function( event ) {

        app_progressBar.toggle();

        // Stop form from submitting normally
        event.preventDefault();

        // Get some values from elements on the page:
        var $form = $( this ),
            token = $form.find( "input[name='_token']" ).val(),
            folderName = $form.find( "input[name='folder_name']" ).val(),
            parentId = $form.find( "input[name='parent_id']" ).val(),
            url = $form.attr( "action" );

        // Send the data using post
        var posting = $.post( url, { name: folderName, parent_id: parentId, _token: token } );

        // Put the results in a div
        posting.done(function( data ) {
            if(data.success){
                app_progressBar.maxOut();
                $('#newFolderModal').modal('hide');
                document.location.reload();
            }else{
                /*for (var key in data.errors) {
                 if (data.errors.hasOwnProperty(key)) {
                 }
                 }*/
            }
        });
    });

    $(document).on('shown.bs.modal', '#newFolderModal', function(){
        $(this).find("input[name='folder_name']").focus();
    });

</script>