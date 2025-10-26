<script>
    function renameFolder(id){
        $.get( "{{ route('projectDocument.folderInfo', array($project->id)) }}/"+id )
         .done(function( data ) {
             $('#renameFolderModal').modal({
                 keyboard: false
             });
             var $form = $( "#renameFolderForm" );
             $form.find( "input[name='folder_name']" ).val(data.name);
             $form.find( "input[name='folder_name']" ).attr("placeholder", data.name);
             $form.find( "input[name='id']" ).val(id);
         });
        return false;
    }

    $( "#renameFolderForm" ).submit(function( event ) {

        app_progressBar.toggle();

        // Stop form from submitting normally
        event.preventDefault();

        // Get some values from elements on the page:
        var $form = $( this ),
            token = $form.find( "input[name='_token']" ).val(),
            folderName = $form.find( "input[name='folder_name']" ).val(),
            id = $form.find( "input[name='id']" ).val()
        url = $form.attr( "action" );

        // Send the data using post
        var posting = $.post( url, { name: folderName, id: id, _token: token } );

        // Put the results in a div
        posting.done(function( data ) {
            if(data.success){
                app_progressBar.maxOut();
                $('#renameFolderModal').modal('hide');
                document.location.reload();
            }
        });
    });

    $(document).on('shown.bs.modal', '#renameFolderModal', function(){
        $(this).find("input[name='folder_name']").focus();
    });

</script>