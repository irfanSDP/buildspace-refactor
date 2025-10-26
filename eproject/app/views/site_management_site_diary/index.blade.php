@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{trans('siteManagementSiteDiary.site_diary')}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="glyphicon glyphicon-book"></i>&nbsp;&nbsp;{{trans('siteManagementSiteDiary.site_diary')}}
        </h1>
    </div>
    @if(PCK\SiteManagement\SiteManagementUserPermission::isSubmitter(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY, $user, $project))
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('site-management-site-diary.general-form.create',$project->id )}}">
            <button id="createForm" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i>&nbsp;{{trans('siteManagementSiteDiary.create_general_form')}}
            </button>
        </a>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{trans('siteManagementSiteDiary.site_diary_records')}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table " id="dt_basic">
                            <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Date" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Physical Progress" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Plan Progress" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Submitted User" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Submitted Status" />
                                    </th>
                                </tr>
                                <tr>
                                    <th>Number</th>
                                    <th>Date</th>
                                    <th>Physical Progress (%)</th>
                                    <th>Plan Progress (%)</th>
                                    <th>Submitted User</th>
                                    <th>Approval Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($records as $record)
                                    <tr>
                                        <td>
                                            {{{++$count}}}
                                        </td>
                                        <td>
                                           	{{{$record->general_date}}}
                                        </td>
                                        <td>
                                           	{{{$record->general_physical_progress}}}
                                        </td>
                                        <td>
                                           	{{{$record->general_plan_progress}}}
                                        </td>
                                        <td>
                                           	{{{$record->submittedUser->name}}}
                                        </td>

                                        <td>
                                            @if($record->status == PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse::STATUS_PENDING_FOR_APPROVAL)
                                            <a href="{{{ route('site-management-site-diary.general-form.show', 
                                                    array($project->id,$record->id)) }}}">
                                                <strong>{{{$record->status_text}}}</strong>
                                            </a>
                                            &nbsp;
                                            @else
                                                {{{$record->status_text}}}
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->status == PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse::STATUS_APPROVED 
                                            || $record->status == PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse::STATUS_REJECT)
                                            <a href="{{{ route('site-management-site-diary.general-form.show', 
                                                    array($project->id,$record->id)) }}}" class="btn btn-xs btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            &nbsp;
                                            @else
                                                @if(PCK\SiteManagement\SiteManagementUserPermission::isSubmitter(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY, $currentUser, $project))      
                                                    @if($record->status != PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse::STATUS_PENDING_FOR_APPROVAL)
                                                        <a href="{{{ route('site-management-site-diary.general-form.edit', 
                                                                        array($project->id,$record->id, 'general')) }}}" class="btn btn-xs btn-success">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        &nbsp;
                                                        <a href="{{{ route('site-management-site-diary.general-form.delete', 
                                                            array($project->id,$record->id)) }}}" class="btn btn-xs btn-danger" data-method="delete" data-csrf_token="{{ csrf_token() }}">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var otable = $('#dt_basic').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : false
            });

            $("#dt_basic thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            } );
        });
    </script>
    
@endsection