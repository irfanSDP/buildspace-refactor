@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('tenderDocumentFolders.tenderDocumentFolders') }}</li>
        <li>{{ link_to_route('tender_documents.template.directory', trans('general.templates')) }}</li>
        <li>{{ trans('general.template') }} {{{ $root->serial_number }}}</li>
    </ol>
@endsection

@section('css')
    @parent
    <style>
    .tenderDocumentFolderLabel {
        background-color: white;
        color:black;
        border: 1px solid black;
    }

    .tenderDocumentSubFolderLabel {
        background-color: #999999;
        color:white;
        border: 1px solid #999999;
    }
    </style>
@endsection

@section('content')

    <div class="jarviswidget " id="wid-id-projectDocumentTabs"
         data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false"
         data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false"
         data-widget-sortable="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-folder"></i> </span>

            <h2>{{ trans('tenderDocumentFolders.tenderDocumentFoldersTemplate') }} {{{ $root->serial_number }}}</h2>

        </header>
        <!-- widget div-->
        <div>

            <!-- widget edit box -->
            <div class="jarviswidget-editbox"></div>
            <!-- end widget edit box -->

            <!-- widget content -->
            <div class="widget-body">
                <div class="well">
                    {{ Form::open(array('route' => array('tender_documents.template.assign.workCategory', $root->id), 'method' => 'POST', 'class' => 'smart-form')) }}
                    <div class="row">
                        <div class="col col-md-10">
                            <label class="fill">
                                {{ trans('tenderDocumentFolders.templateFor') }}:
                                <select name="work_category_id[]" class="form-control select2" multiple>
                                    @foreach($workCategories as $workCategory)
                                        <option value="{{{ $workCategory->id }}}" {{{ $root->workCategories->contains($workCategory->id) ? 'selected' : '' }}}>{{{ $workCategory->name }}}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="col col-md-2">
                            <button class="btn btn-sm btn-success"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                        </div>
                    </div>


                    {{ Form::close() }}
                </div>
                <hr/>
                <?php
                $elementWidth = 300;
                ?>
                <div class="dd no-float"  id="root-folder">
                    <ol class="dd-list">
                        <li class="dd-item" style="width:{{{ $elementWidth }}}px">
                            <?php
                            $toggleExpandIndicator = '<span class="label label-primary folder-state-label"><i class="fa fa-lg fa-folder-open folder-state"></i></span>';
                            ?>
                            <div class="dd3-content bg-grey-e rounded-less">{{ $toggleExpandIndicator }}&nbsp;<span class="label label-primary">{{{ $rootName }}}</span>
                                <div style="float:right"><a href="#" tabindex="0" data-toggle="popover" onclick="return false;" class="color-bootstrap-primary"
                                        data-content="<div style='min-width:120px'>
                                    <a href='#' onclick='createNewFolder(null, {{{ \PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_FOLDER }}});return false;'>
                                    {{ trans('documentManagementFolders.newFolder') }}</a></div>">{{ trans('documentManagementFolders.options') }}</a>
                                </div>
                            </div>
                        </li>
                    </ol>
                </div>
                <!-- BQ Files -->
                <div>
                    <div class="dd no-float">
                        <ol class="dd-list">
                            <li class="dd-item" style="width: 580px">
                                <?php
                                $toggleExpandIndicator = '<span class="label tenderDocumentFolderLabel primary folder-state-label"><i class="fa fa-lg fa-folder folder-state"></i></span>';
                                ?>
                                <div class="dd3-content bg-grey-d rounded-ne rounded-se">{{ $toggleExpandIndicator }}&nbsp;
                                    <span class="label tenderDocumentFolderLabel">{{ $bqFolderDefaultName }}</span>
                                    <div style="float:right">
                                        <a href="#" tabindex="0" data-toggle="popover" onclick="return false;" class="color-bootstrap-primary"
                                                                data-content="<div style='min-width:120px'>{{ trans('documentManagementFolders.newFolder') }}</div>">
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- Form of Tender -->
                <div>
                    <div class="dd no-float">
                        <ol class="dd-list">
                            <li class="dd-item" style="width: 580px">
                                <?php
                                $toggleExpandIndicator = '<span class="label tenderDocumentFolderLabel primary folder-state-label"><i class="fa fa-lg fa-folder folder-state"></i></span>';
                                ?>
                                <div class="dd3-content bg-grey-d rounded-ne rounded-se">{{ $toggleExpandIndicator }}&nbsp;
                                    <span class="label tenderDocumentFolderLabel">{{{ \PCK\TenderDocumentFolders\TenderDocumentFolder::DEFAULT_FORM_OF_TENDER_FOLDER_NAME }}}</span>
                                    <div style="float:right">
                                        <a href="#" tabindex="0" data-toggle="popover" onclick="return false;" class="color-bootstrap-primary"
                                           data-content="<div style='min-width:120px'>{{ trans('documentManagementFolders.newFolder') }}</div>">
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>
                <div id="folders">
                    <?php
                    function populate($items, $depth){
                        $elementWidth = 580;
                        $html = '';
                        if(isset($items) && (count($items) > 0)){

                            $html .= '<ol class="dd-list">';

                            $colorClasses = array(
                                    1 => 'bg-grey-9',
                                    2 => 'bg-grey-a',
                                    3 => 'bg-grey-b',
                                    4 => 'bg-grey-c',
                                    5 => 'bg-grey-d',
                                    6 => 'bg-grey-e',
                                    7 => 'bg-white'
                            );

                            foreach($items as $item){

                                $isMainFolder = ( $depth == 1 ) ? true : false;

                                $fileCount = $item['data']['fileCount'];
                                $fileCountIndicatorColorClass = ( $isMainFolder ) ? 'bg-bootstrap-warning' : 'bg-grey-4';

                                if(PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder::find($item['id'])->folder_type == \PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT)
                                {
                                    $fileCount = 1;
                                    $fileCountIndicatorColorClass = 'bg-color-magenta';
                                }

                                $listItemColour = $colorClasses[ ( ( $depth - 1 ) % count($colorClasses) ) + 1 ];
                                $html .= '<li class="dd-item '.$listItemColour.'" data-id="'.$item['id'].'" style="width:'.$elementWidth.'px; border-radius: 25px;">';

                                // Handle
                                $html .= '<div class="dd-handle dd3-handle">&nbsp;</div>';

                                $subFolderClass = ( $isMainFolder ) ? 'bg-grey-d' : '';

                                $html .= '<div class="dd3-content toggle-expand ' . $subFolderClass . '" style="border-radius: 0 25px 25px 0;">';

                                $options = '<div class="options-menu" style="float:right">';

                                $options .= '<a href="#" tabindex="0" data-toggle="popover" onclick="return false;"
                                         data-content="<div style=\'min-width:200px\'>';

                                $options .= '<a href=\'' . route('tender_documents.template.show', array( $item['id'] )) . '\'>' . trans('documentManagementFolders.open') . '</a><br />';
                                $options .= '<a href=\'#\' onclick=\'createNewFolder(' . $item['id'] . ', '.\PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_FOLDER.');return false;\'>' . trans('documentManagementFolders.newFolder') . '</a><br />
                                        <a href=\'#\' onclick=\'createNewFolder(' . $item['id'] . ', '.\PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT.');return false;\'>' . trans('documentManagementFolders.newStructuredDocument') . '</a><br />
                                        <a href=\'#\' onclick=\'renameFolder(' . $item['id'] . ');return false;\'>' . trans('documentManagementFolders.rename') . '</a><br />
                                         <a href=\'#\' onclick=\'deleteFolder(' . $item['id'] . ');return false;\'>' . trans('files.delete') . '</a><br />
                                         </div>">' . trans('documentManagementFolders.options') . '</a>';

                                $options .= "</div>";

                                $folderName = $item['data']['folderName'];
                                $fullFolderName = $folderName;
                                $strLimit = 40;
                                if(strlen($folderName) > $strLimit )
                                {
                                    $folderName = substr($folderName, 0, ( $strLimit - 3 )) . '...';
                                }

                                $folderLabelClass = ( $isMainFolder ) ? 'tenderDocumentFolderLabel' : 'tenderDocumentSubFolderLabel';

                                $toggleExpandIndicator = '<span class="label ' . $folderLabelClass . ' folder-state-label" data-id="' . $item['id'] . '"><i class="fa fa-lg fa-folder-open folder-state" data-id="' . $item['id'] . '"></i></span>';

                                $folderNameLabel = '<span class="label ' . $folderLabelClass . '" data-toggle="tooltip" data-placement="top" title="' . $fullFolderName . '">' . $folderName . '</span>';

                                $fileCountIndicator = '<span class="badge '.$fileCountIndicatorColorClass.'">' . $fileCount . ' Files</span>';

                                $html .= '<div class="dd-content">' . $toggleExpandIndicator . '&nbsp;' . $folderNameLabel . '&nbsp; ' . $fileCountIndicator . ' ' . $options . '</div>';

                                $html .= '</div>';

                                if( isset( $item['children'] ) )
                                {
                                    $html .= populate($item['children'], ( $depth + 1 ));
                                }

                                $html .= '</li>';
                            }

                            $html .= '</ol>';
                        }
                        return $html;
                    }
                    ?>

                    <div class="dd no-float" id="nestable-json">
                        {{ populate($descendants, 1) }}
                        <!-- Additional space to make dragging to the bottom easier (Start) -->
                        <br/>
                        <br/>
                        <br/>
                        <!-- Additional space to make dragging to the bottom easier (End) -->
                    </div>
                    <!-- Sub folders end -->
                </div>
            </div>
            <!-- end widget content -->

        </div>
        <!-- end widget div -->

    </div>

    <!-- New Folder Modal -->
    <div class="modal fade" id="newFolderModal">
        <div class="modal-dialog modal-dmf">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="newFolderLabel">{{ trans('documentManagementFolders.newFolder') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                </div>
                {{ Form::open(array('route' => array('tender_documents.template.create', $root->id) , 'id' => 'newFolderForm')) }}
                <div class="modal-body">
                    @include('tender_document_folders.partials.folderForm')
                </div>
                <div class="modal-footer">
                    {{ Form::submit(trans('documentManagementFolders.save'), array('class' => 'btn btn-primary')) }}

                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {{ trans('files.cancel') }}
                    </button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!-- Rename Folder Modal -->
    <div class="modal fade" id="renameFolderModal">
        <div class="modal-dialog modal-dmf">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="renameFolderLabel">{{ trans('documentManagementFolders.renameFolder') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                </div>
                {{ Form::open(array('route' => array('tender_documents.template.rename') , 'id' => 'renameFolderForm')) }}
                <div class="modal-body">
                    @include('tender_document_folders.partials.folderForm')
                </div>
                <div class="modal-footer">
                    {{ Form::submit(trans('documentManagementFolders.save'), array('class' => 'btn btn-primary')) }}

                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('files.cancel') }}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!-- Delete Folder Modal -->
    <div id="folderDeleteConfirm" class="modal fade">
        <div class="modal-dialog modal-dmf">
            <div class="modal-content">
                <div class="modal-body">
                    {{ trans('documentManagementFolders.confirmDelete') }}
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-primary"
                            id="folderDeleteBtn">{{ trans('files.delete') }}</button>

                    <button type="button" data-dismiss="modal"
                            class="btn btn-default">{{ trans('files.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
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
            if(listItemElement.hasClass('dd-collapsed'))
            {
                // Open folder
                listItemElement.removeClass('dd-collapsed');
                folderIcon.removeClass('fa-folder');
                folderIcon.addClass('fa-folder-open');
            }
            else
            {
                // Close folder
                listItemElement.addClass('dd-collapsed');
                folderIcon.removeClass('fa-folder-open');
                folderIcon.addClass('fa-folder');
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
            maxDepth: 5,
            callback: function(){
                $.ajax({
                    url: '{{ route('tender_documents.template.reposition', array($root->id)) }}',
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
                    }
                });
            }
        });

        $(document).ready(function() {
            // PAGE RELATED SCRIPTS

            $('.tree > ul').attr('role', 'tree').find('ul').attr('role', 'group');
            $('.tree').find('li:has(ul)').addClass('parent_li').attr('role', 'treeitem').find(' > span').attr('title', 'Collapse this branch').on('click', function(e) {
                var children = $(this).parent('li.parent_li').find(' > ul > li');
                if (children.is(':visible')) {
                    children.hide('fast');
                    $(this).attr('title', 'Expand this branch').find(' > i').removeClass().addClass('fa fa-lg fa-plus-circle');
                } else {
                    children.show('fast');
                    $(this).attr('title', 'Collapse this branch').find(' > i').removeClass().addClass('fa fa-lg fa-minus-circle');
                }
                e.stopPropagation();
            });

            $('[data-toggle="popover"]').popover({html : true,trigger: 'focus','placement': 'right'});
        });

        function createNewFolder(parentId, folderType){
            $('#newFolderModal').modal({
                keyboard: false
            });
            var $form = $( "#newFolderForm" );

            $form.find( "input[name='parent_id']" ).val(parentId);
            $form.find( "input[name='folder_type']" ).val(folderType);
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
                    folderType = $form.find( "input[name='folder_type']" ).val(),
                    url = $form.attr( "action" );

            // Send the data using post
            var posting = $.post( url, { name: folderName, parent_id: parentId, folder_type: folderType, _token: token } );

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

        function renameFolder(id) {
            $.ajax({
                url: "{{ route('tender_documents.template.getFolderInfo') }}",
                type: 'POST',
                data: {id: id, _token: '{{{ csrf_token() }}}'},
                success: function (data) {
                    $('#renameFolderModal').modal({
                        keyboard: false
                    });
                    var $form = $("#renameFolderForm");
                    $form.find("input[name='folder_name']").val(data.name);
                    $form.find("input[name='folder_name']").attr("placeholder", data.name);
                    $form.find("input[name='id']").val(id);
                }
            });
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

        function deleteFolder(id)
        {
            var token = $('meta[name=_token]').attr("content");

            $('#folderDeleteConfirm').modal({ backdrop: 'static', keyboard: false })
                    .one('click', '#folderDeleteBtn', function() {
                        app_progressBar.toggle();
                        $.ajax({
                            url: "{{ route('tender_documents.template.delete') }}",
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

        $(document).on('shown.bs.modal', '#newFolderModal, #renameFolderModal', function(){
            $(this).find("input[name='folder_name']").focus();
        });

    </script>
@endsection