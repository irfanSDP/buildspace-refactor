<?php $hasRole = $user->isSuperAdmin() || $user->hasCompanyProjectRole($project, $project->getCallingTenderRole()); ?>
<?php $isEditor = $user->isEditor($project); ?>
<?php $allowTenderAccess = \PCK\Filters\TenderFilters::checkTenderAccessLevelPermissionAllowed($project, $currentUser);?>
<?php $allowContractorAccess = ($currentUser->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) && $currentUser->isGroupAdmin() && $project->showOpenTender());?>

<?php $showUpload = ( $hasRole && $isEditor ); ?>

@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('tenders.tenderDocuments') }}</li>
    </ol>
@endsection

@section('content')

    <div class="jarviswidget " id="wid-id-projectDocumentTabs"
         data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false"
         data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false"
         data-widget-sortable="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-folder"></i> </span>

            <h2>{{ trans('tenders.tenderDocuments') }}</h2>
        </header>
        <!-- widget div-->
        <div>

            <!-- widget edit box -->
            <div class="jarviswidget-editbox"></div>
            <!-- end widget edit box -->

            <!-- widget content -->
            <div class="widget-body">
                <ul id="documentManagementFolderTab" class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#tab-myFolder" data-toggle="tab"><i
                                    class="fa fa-fw fa-lg fa-folder"></i> {{ trans('documentManagementFolders.myFolders') }}
                        </a>
                    </li>
                </ul>

                <div id="documentManagementFolderTabContent1" class="tab-content padding-10">
                    <div class="tab-pane fade in active" id="tab-myFolder">
                        <div class="tree">
                            <ul>
                                <li>
                                    <span class="label label-primary"><i class="fa fa-lg fa-folder-open"></i> {{ trans('tenders.tenderDocuments') }}</span>

                                    @if ($showUpload)
                                        <a href="#" tabindex="0" data-toggle="popover" onclick="return false;"
                                           data-content="<div style='min-width:120px'>
                                           <a href='#' onclick='createNewFolder(null, {{{ \PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_FOLDER }}});'>{{ trans('documentManagementFolders.newFolder') }}</a>
                                           </div>">{{ trans('documentManagementFolders.options') }}</a>
                                    @endif
                                    
                                    <?php
                                    $result = '';
                                    ?>

                                    <?php $result = '<ul role="group">'?>

                                    @foreach($roots as $root)

                                        <?php
                                        $fileCount = $root->files->count();
                                        $badgeClass = 'bg-color-orange';
                                        $openInNewPage = false;
                                        $openOptionName =  trans('documentManagementFolders.open');
                                        if(PCK\TenderDocumentFolders\TenderDocumentFolder::find($root->id)->folder_type == \PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT)
                                        {
                                            $fileCount = 1;
                                            $badgeClass = 'bg-color-magenta';
                                            if( ! $allowTenderAccess )
                                            {
                                                $openInNewPage = true;
                                                $openOptionName = trans('general.print');
                                            }
                                        }
                                        ?>
                                        <?php $result .= '<li'; $isRoot = ( count($folderDescendants[$root->id]) ) ? true : false; ?>
                                        <?php $result .= ( $isRoot ) ? ' class="parent_li" role="treeitem"><span title="Collapse this branch"><i class="fa fa-lg fa-minus-circle"></i> ' . $root->name . '&nbsp; <span class="badge '.$badgeClass.'">' . $fileCount . ' Files</span></span>' : '><span> ' . $root->name . ' &nbsp; <span class="badge '.$badgeClass.'">' . $fileCount . ' Files</span></span>'; ?>

                                        <?php

                                        if ( !$showUpload )
                                        {
                                            $result .= ' &ndash; ' . link_to_route('projects.tenderDocument.myFolder', $openOptionName, array( $project->id, $root['id'] ), array('target' => $openInNewPage ? '_blank' : '_self'));
                                        }
                                        else
                                        {
                                            $result .= ' &ndash; <a href="#" tabindex="0" data-toggle="popover" onclick="return false;" data-content="<div style=\'min-width:120px\'><a href=\'' . route('projects.tenderDocument.myFolder', array( $project->id, $root->id )) . '\'>' . trans('documentManagementFolders.open') . '</a><br />';

                                            if ( !$root['system_generated_folder'] )
                                            {
                                                $result .= '<a href=\'#\' onclick=\'createNewFolder(' . $root->id . ', '.\PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_FOLDER.');\'>' . trans('documentManagementFolders.newFolder') . '</a>
                                                <br/><a href=\'#\' onclick=\'createNewFolder(' . $root->id . ', '.\PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT.');\'>' . trans('documentManagementFolders.newStructuredDocument') . '</a>
                                                <br /><a href=\'#\' onclick=\'renameFolder(' . $root->id . ');\'>' . trans('documentManagementFolders.rename') . '</a><br /><a href=\'#\' onclick=\'deleteFolder(' . $root->id . ');\'>' . trans('files.delete') . '</a><br />';
                                            }
                                            else
                                            {
                                                $shareNotificationLink = route('projects.tenderDocument.sendNotification', array($project->id, $root->id));

                                                $result .= "<a href='{$shareNotificationLink}'>" . trans('documentManagementFolders.sendNotification') . '</a><br />';
                                            }

                                            $result .= '</div>">' . trans('documentManagementFolders.options') . '</a>';
                                        }
                                        ?>

                                        @if($isRoot)

                                            <?php
                                            $currDepth = 0;
                                            $lastNodeIndex = count($folderDescendants[$root->id]) - 1;
                                            ?>

                                            @foreach($folderDescendants[$root->id] as $index => $descendant)

                                                @if ($descendant['depth'] > $currDepth || $index == 0)
                                                    <?php $result .= '<ul>'?>
                                                @endif

                                                @if ($descendant['depth'] < $currDepth)
                                                    <?php $result .= str_repeat('</ul></li>', $currDepth - $descendant['depth'])?>
                                                @endif

                                                <?php /*Always open a node*/ $t = ( $index == 0 ) ? 1 : 2?>

                                                <?php

                                                $fileCount = $descendant->files->count();
                                                $badgeClass = 'bg-color-darken';
                                                $openInNewPage = false;
                                                $openOptionName =  trans('documentManagementFolders.open');
                                                if(PCK\TenderDocumentFolders\TenderDocumentFolder::find($descendant['id'])->folder_type == \PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT)
                                                {
                                                    $fileCount = 1;
                                                    $badgeClass = 'bg-color-magenta';
                                                    if( ! $allowTenderAccess )
                                                    {
                                                        $openInNewPage = true;
                                                        $openOptionName = trans('general.print');
                                                    }
                                                }

                                                if ( !$showUpload ):
                                                    {
                                                        $result .= '<li><span class="label label-default"><i class="fa fa-lg fa-folder-open"></i>&nbsp;' . $descendant['name'] . ' &nbsp; <span class="badge '.$badgeClass.'">' . $fileCount . ' Files</span></span> &ndash; ' . link_to_route('projects.tenderDocument.myFolder', $openOptionName, array( $project->id, $descendant['id'] ), array('target' => $openInNewPage ? '_blank' : '_self'));
                                                    }
                                                else:
                                                    $result .= '<li><span class="label label-default"><i class="fa fa-lg fa-folder-open"></i>&nbsp;' . $descendant['name'] . '  &nbsp; <span class="badge '.$badgeClass.'">' . $fileCount . ' Files</span> </span> &ndash; <a href="#" tabindex="0" data-toggle="popover" onclick="return false;" data-content="<div style=\'min-width:120px\'><a href=\'' . route('projects.tenderDocument.myFolder', array( $project->id, $descendant['id'] )) . '\'>' . trans('documentManagementFolders.open') . '</a><br />';

                                                    if ( !$descendant['system_generated_folder'] )
                                                    {
                                                        $result .= '<a href=\'#\' onclick=\'createNewFolder(' . $descendant['id'] . ', '.\PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_FOLDER.');\'>' . trans('documentManagementFolders.newFolder') . '</a>
                                                        <br/><a href=\'#\' onclick=\'createNewFolder(' . $descendant['id'] . ', '.\PCK\TenderDocumentFolders\TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT.');\'>' . trans('documentManagementFolders.newStructuredDocument') . '</a>
                                                        <br /><a href=\'#\' onclick=\'renameFolder(' . $descendant['id'] . ');\'>' . trans('documentManagementFolders.rename') . '</a><br /><a href=\'#\' onclick=\'deleteFolder(' . $descendant['id'] . ');\'>' . trans('files.delete') . '</a><br />';
                                                    }
                                                    else
                                                    {
                                                        $shareNotificationLink = route('projects.tenderDocument.sendNotification', array($project->id, $descendant->id));

                                                        $result .= "<a href='{$shareNotificationLink}'>" . trans('documentManagementFolders.sendNotification') . '</a><br />';
                                                    }

                                                    $result .= '</div>">' . trans('documentManagementFolders.options') . '</a>';
                                                endif
                                                ?>

                                                @if ($index != $lastNodeIndex && $folderDescendants[$root->id][$index + 1]['depth'] <= $folderDescendants[$root->id][$index]['depth'])
                                                    <?php $result .= '</li>'?>
                                                @endif

                                                <?php $currDepth = $descendant['depth'] ?>

                                                @if ($index == $lastNodeIndex)
                                                    <?php $result .= str_repeat('</li></ul>', $currDepth)?>
                                                @endif
                                            @endforeach
                                        @endif


                                        <?php $result .= '</li>'?>
                                    @endforeach()
                                    <?php $result .= '</ul>'?>
                                    {{$result}}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
            <!-- end widget content -->

        </div>
        <!-- end widget div -->

    </div>

    <!-- Form of Tender Folder -->
    <div data-document="form-of-tender" hidden>
        <ul>
            <li>
                <span> {{ trans('tenderDocumentFolders.formOfTender') }}
                    &nbsp;<span class="badge bg-color-greenDark">{{{ $project->latestTender->current_tender_name }}}</span>
                </span>
                @if($allowTenderAccess)
                    &ndash;
                    <a href="{{ route('form_of_tender.edit', array($project->id, $project->latestTender->id)) }}">{{ trans('general.show') }}</a>
                @elseif($allowContractorAccess)
                    <?php
                    $pivot = $user->company->tenders()->where('tender_id', '=', $project->latestTender->id)->first()->pivot;
                    ?>
                    &ndash;
                    @if($pivot->isSubmitted())
                        <a href="{{ route('form_of_tender.contractorInput.print', array($project->id, $pivot->tender->id, $pivot->company->id)) }}" target="_blank">{{ trans('general.print') }}</a>
                    @else
                        <a href="{{ route('form_of_tender.print', array($project->id, $pivot->tender->id )) }}" target="_blank">{{ trans('general.print') }}</a>
                    @endif
                @endif
            </li>
        </ul>
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
                {{ Form::open(array('route' => array('projects.tenderDocument.newFolder', $project->id) , 'id' => 'newFolderForm')) }}
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
                {{ Form::open(array('route' => array('projects.tenderDocument.renameFolder', $project->id) , 'id' => 'renameFolderForm')) }}
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

    <div id="folderDeleteConfirm" class="modal fade">
        <div class="modal-dialog modal-dmf">
            <div class="modal-content">
                <div class="modal-body">
                    {{ trans('documentManagementFolders.confirmDelete') }}
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-primary" id="folderDeleteBtn">{{ trans('files.delete') }}</button>

                    <button type="button" data-dismiss="modal" class="btn btn-default">{{ trans('files.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        $(document).ready(function() {

            pageSetUp();

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

            $form.find( "input[name='parent_id']" ).val((parentId) ? parentId : null);
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

        function renameFolder(id){

            $.get( "{{ route('projects.tenderDocument.folderInfo', array($project->id)) }}/"+id )
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

        function deleteFolder(id)
        {
            var token = $('meta[name=_token]').attr("content");

            $('#folderDeleteConfirm').modal({ backdrop: 'static', keyboard: false })
                 .one('click', '#folderDeleteBtn', function() {
                     app_progressBar.toggle();
                     $.ajax({
                         url: "{{ route('projects.tenderDocument.deleteFolder', array($project->id)) }}",
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

        // Add Form of Tender Folder to the tree view.
        $('#tab-myFolder .tree ul[role=group]>li:nth-child(1)').after($('[data-document=form-of-tender] li').detach());

        $(document).on('shown.bs.modal', '#newFolderModal, #renameFolderModal', function(){
            $(this).find("input[name='folder_name']").focus();
        });

    </script>
@endsection