<?php
$isEditor = $user->isEditor($project);
$editable = ( ! $user->isSuperAdmin() );
?>

@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('documentManagementFolders.projectDocuments') }}</li>
        <li>{{{ $root->name }}}</li>
    </ol>
@endsection

@section('content')

    <h2 class="row-seperator-header"><i class="fa fa-folder"></i> {{{$root->name}}} </h2>

    <article class="col-sm-12 col-md-12 col-lg-12">

        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget well" id="wid-id-projectDocumentTabs" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
            <!-- widget div-->
            <div>

                <!-- widget edit box -->
                <div class="jarviswidget-editbox"></div>
                <!-- end widget edit box -->

                <!-- widget content -->
                <div class="widget-body">
                    <ul id="documentManagementFolderTab" class="nav nav-tabs bordered">
                        <li class="active">
                            <a href="#tab-myFolder" data-toggle="tab">
                                <i class="fa fa-fw fa-lg fa-folder"></i> {{ trans('documentManagementFolders.myFolders') }}
                            </a>
                        </li>
                        <li>
                            <a href="#tab-sharedFolder" data-toggle="tab">
                                <i class="fa fa-fw fa-lg fa-share-alt-square"></i> {{ trans('documentManagementFolders.sharedFolders') }}
                            </a>
                        </li>
                    </ul>

                    <div id="documentManagementFolderTabContent1" class="tab-content padding-10">
                        @include('document_management_folders.partials.main_folders')

                        @include('document_management_folders.partials.shared_folders')
                    </div>
                </div>
                <!-- end widget content -->

            </div>
            <!-- end widget div -->
        </div>
    </article>

    @if($isEditor)
        @include('document_management_folders.partials.create_folder_modal')
        @include('document_management_folders.partials.delete_folder_modal')
        @include('document_management_folders.partials.rename_folder_modal')
        @include('document_management_folders.partials.share_folder_modal')
    @endif
@endsection

@section('js')
    @include('document_management_folders.partials.js_main_folders')
    @include('document_management_folders.partials.js_shared_folders')
    @if($isEditor)
        @include('document_management_folders.partials.js_create_folder_modal')
        @include('document_management_folders.partials.js_delete_folder_modal')
        @include('document_management_folders.partials.js_rename_folder_modal')
        @include('document_management_folders.partials.js_share_folder_modal')
        @include('document_management_folders.partials.js_send_notifications_modal')
    @endif
@endsection