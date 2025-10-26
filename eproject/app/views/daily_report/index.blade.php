@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('dailyreport.daily-report') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="glyphicon glyphicon-wrench"></i>&nbsp;&nbsp;{{{ trans('dailyreport.daily-report') }}}
        </h1>
    </div>
    
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
    	<a href="{{ route('daily-report.create', $project->id)}}">
	        <button id="createInstruction" class="btn btn-primary btn-md pull-right header-btn">
	            <i class="fa fa-plus"></i> {{{ trans('dailyreport.add-form') }}}
	        </button>
        </a>
    </div>

</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('dailyreport.daily-report-status') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dt_basic">
                            <thead>
                                <tr>
                                    <th style="width:40px;" class="text-middle">{{{ trans('dailyreport.no') }}}</th>
                                    <th class="text-center text-middle">{{{ trans('dailyreport.description') }}}</th>
                                    <th class="text-center text-middle">{{{ trans('dailyreport.date') }}}</th>
                                    <th class="text-center text-middle">{{{ trans('dailyreport.submitby') }}}</th>
                                    <th class="text-center text-middle">{{{ trans('dailyreport.status') }}}</th>
                                    <th class="text-center text-middle">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?> 
                                @foreach ($records as $record)
                                    <tr>
                                        <td class="text-center text-middle">{{{++$count}}}</td>
                                        <td class="text-center text-middle">{{ Str::limit($record->instruction, 50) }}</td>
                                        <td class="text-center text-middle">{{\Carbon\Carbon::parse($record->instruction_date)->format('Y-m-d')}}</td>
                                        <td class="text-center text-middle">{{{$record->submittedUser->name}}}</td>
                                        <td class="text-center text-middle">
                                            @if($record->status == PCK\DailyReport\DailyReport::STATUS_PENDING_FOR_APPROVAL)
                                            <a href="{{{ route('daily-report.show', 
                                                    array($project->id,$record->id)) }}}">
                                                <strong>{{{$record->status_text}}}</strong>
                                            </a>
                                            &nbsp;
                                            @else
                                                {{{$record->status_text}}}
                                            @endif
                                        </td>
                                        <td>
                                        @if($record->status == PCK\DailyReport\DailyReport::STATUS_APPROVED 
                                            || $record->status == PCK\DailyReport\DailyReport::STATUS_REJECT)
                                            <a href="{{{ route('daily-report.show', array($project->id,$record->id)) }}}" class="btn btn-xs btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            &nbsp;
                                        @else
                                            @if(PCK\SiteManagement\SiteManagementUserPermission::isSubmitter(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT, $currentUser, $project))        
                                                @if($record->status != PCK\DailyReport\DailyReport::STATUS_PENDING_FOR_APPROVAL)
                                                    <a href="{{{ route('daily-report.edit', array($project->id,$record->id))}}}" class="btn btn-xs btn-success">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    &nbsp;
                                                    <a href="{{{ route('daily-report.delete', array($project->id,$record->id))}}}"
                                                        class="btn btn-xs btn-danger"
                                                        data-method="delete"
                                                        data-csrf_token="{{{ csrf_token() }}}">
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



@endsection

@section('js')
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
    
        $('#btnViewLogs').on('click', function(e) {
                        e.preventDefault();
                        $('#dailyReportVerifierLogModal').modal('show');
                    });

    </script>
@endsection