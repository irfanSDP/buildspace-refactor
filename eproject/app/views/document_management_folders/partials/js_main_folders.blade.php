<script src="{{ asset('js/plugin/nestable-master/jquery.nestable.js') }}"></script>
<script>
    $(document).on('click', '.options-menu', function(){
        // To disable toggling the expand/collapse,
        // we just toggle it again.
        // ** This is a HACK **
        // Better to just register clicks from non options-menu and toggle, but currently not able to distinguish the clicks.
        toggleExpand($(this).closest('li'));
    });

    $(document).on('click', '.toggle-expand', function(){
        toggleExpand($(this).parent('li'));
    });

    function toggleExpand(listItemElement) {
        var folderIcon = $('.folder-state[data-id='+listItemElement.attr('data-id')+']');
        var folderStateLabel = $('.folder-state-label[data-id='+listItemElement.attr('data-id')+']');
        if(listItemElement.hasClass('dd-collapsed'))
        {
            // Open folder
            listItemElement.removeClass('dd-collapsed');
            folderIcon.removeClass('fa-folder');
            folderIcon.addClass('fa-folder-open');
            folderStateLabel.removeClass('label-success');
            folderStateLabel.addClass('label-warning');
        }
        else
        {
            // Close folder
            listItemElement.addClass('dd-collapsed');
            folderIcon.removeClass('fa-folder-open');
            folderIcon.addClass('fa-folder');
            folderStateLabel.removeClass('label-warning');
            folderStateLabel.addClass('label-success');
        }
    }

    function disableDragging()
    {
        $('.dd-handle').each(function(){
            $(this).removeClass('dd-handle');
        });
    }

    $('#root-folder').nestable({});

    $('#nestable-json').nestable({
        expandBtnHTML: '',
        collapseBtnHTML: '',
        maxDepth: 5
    });

    @if($isEditor)
        $('[data-toggle="popover"]').popover({ html: true, trigger: 'focus', 'placement': 'right' });

        $('#nestable-json').on('change', function(){
            $.ajax({
                url: '{{ route('projectDocument.reposition', array($project->id, $root->id)) }}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    folders: $('#nestable-json').nestable('serialise')
                },
                success: function(data){
                    // success
                },
                error: function(jqXHR,textStatus, errorThrown ){
                    // error
                    console.error(errorThrown);
                }
            });
        });
    @endif
</script>