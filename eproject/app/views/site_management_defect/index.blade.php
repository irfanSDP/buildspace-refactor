@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('siteManagementDefect.defect') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="glyphicon glyphicon-wrench"></i>&nbsp;&nbsp;{{{ trans('siteManagementDefect.site-management-defect') }}}
        </h1>
    </div>
    @if($project->isMainProject())
        @if( ! PCK\SiteManagement\SiteManagementUserPermission::isQsUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) &&
            (PCK\SiteManagement\SiteManagementUserPermission::isSiteUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) ||
             PCK\SiteManagement\SiteManagementUserPermission::isPmUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) ||
             PCK\SiteManagement\SiteManagementUserPermission::isClientUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project)))
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            	<a href="{{ route('site-management-defect.create',$project->id )}}">
        	        <button id="createDefect" class="btn btn-primary btn-md pull-right header-btn">
        	            <i class="fa fa-plus"></i> {{{ trans('siteManagementDefect.add-defect') }}}
        	        </button>
                </a>
            </div>
        @endif
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('siteManagementDefect.defect-listing') }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table " id="dt_basic">
                            <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Company Name" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Status" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Category" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Defect" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Location" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Remark" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Submitted User" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter PIC" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter MCAR" />
                                    </th>
                                </tr>
                                <tr>
                                    <th>{{{ trans('siteManagementDefect.no') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.date-submitted') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.company') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.status') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.category') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.defect') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.location') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.remark') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.submitted-by') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.pic') }}}</th>
                                    <th>{{{ trans('siteManagementDefect.mcar') }}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($records as $record)

                                    <?php
                                        $status = PCK\SiteManagement\SiteManagementDefect::getStatusText($record->status_id);
                                        $mcarStatus   = PCK\SiteManagement\SiteManagementMCAR::getMCARText($record->mcar_status);
                                    ?>

                                    <tr>
                                        <td>
                                            {{{++$count}}}
                                        </td>
                                        <td>
                                           	{{{$project->getProjectTimeZoneTime($record->created_at)}}}
                                        </td>
                                        @if($record->contractor_id == NULL)
                                            <td>
                                                {{{ trans('siteManagementDefect.not-selected') }}}
                                            </td>
                                        @else
                                            <td>
                                                {{{$record->company->name}}}
                                            </td>
                                        @endif
                                        @if($record->status_id == PCK\SiteManagement\SiteManagementDefect::STATUS_REJECT)
                                            <td>
                                                <a href="{{{ route('site-management-defect.getResponse', 
                                                             array($project->id,$record->id)) }}}">
                                                    {{{$status}}}
                                                </a> 
                                                <span class="badge bg-color-red inbox-badge">
                                                    {{{$record->count_reject}}}
                                                </span>
                                            </td>
                                        @else
                                            <td>
                                                <a href="{{{ route('site-management-defect.getResponse', 
                                                             array($project->id,$record->id)) }}}">
                                                    {{{$status}}}
                                                </a> 
                                            </td>
                                        @endif
                                        <td>
                                            {{{$record->defectCategory->name}}}
                                        </td>
                                        @if($record->defect_id == NULL)
                                            <td>
                                                {{{ trans('siteManagementDefect.not-selected') }}}
                                            </td>
                                        @else
                                            <td>
                                                {{{$record->defect->name}}}
                                            </td>
                                        @endif
                                        <td>
                                            {{{$record->projectStructureLocationCode ? $record->projectStructureLocationCode->description : NULL}}}
                                        </td>
                                        <td>
                                            {{{$record->remark}}}
                                        </td>
                                        <td>
                                            {{{$record->submittedUser->name}}}
                                        </td>
                                        @if($record->pic_user_id == NULL)
                                            <td>
                                                <a href="{{{ route('site-management-defect.assignPIC', 
                                                         array($project->id,$record->id)) }}}">
                                                         {{{ trans('siteManagementDefect.not-assigned') }}}
                                                </a> 
                                            </td>
                                        @else
                                            <td>
                                                <a href="{{{ route('site-management-defect.assignPIC', 
                                                         array($project->id,$record->id)) }}}">
                                                        {{{$record->user->name}}}
                                                </a>
                                            </td>
                                        @endif
                                        @if($record->mcar_status == PCK\SiteManagement\SiteManagementMCAR::MCAR_SUBMIT_FORM)
                                        <td>
                                            <a href="{{{ route('site-management-defect.createMCAR', 
                                                         array($project->id,$record->id)) }}}">
                                                {{{$mcarStatus}}}
                                            </a> 
                                        </td>
                                        @elseif($record->mcar_status == PCK\SiteManagement\SiteManagementMCAR::MCAR_PENDING_REPLY)
                                        <td>
                                            <a href="{{{ route('site-management-defect.replyMCAR', 
                                                         array($project->id,$record->id)) }}}">
                                                {{{$mcarStatus}}}
                                            </a> 
                                        </td>
                                        @elseif($record->mcar_status == PCK\SiteManagement\SiteManagementMCAR::MCAR_PENDING_VERIFY)
                                        <td>
                                            <a href="{{{ route('site-management-defect.replyMCAR', 
                                                         array($project->id,$record->id)) }}}">
                                                {{{$mcarStatus}}}
                                            </a> 
                                        </td>
                                        @elseif($record->mcar_status == PCK\SiteManagement\SiteManagementMCAR::MCAR_VERIFIED)
                                        <td>
                                            <a href="{{{ route('site-management-defect.replyMCAR', 
                                                         array($project->id,$record->id)) }}}">
                                                {{{$mcarStatus}}}
                                            </a> 
                                        </td>
                                        @else
                                        <td>
                                            {{{$mcarStatus}}}
                                        </td>
                                        @endif
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