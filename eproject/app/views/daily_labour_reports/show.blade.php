@extends('layout.main')

@section('breadcrumb')

<ol class="breadcrumb">
    <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
    <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
    <li>{{ link_to_route('daily-labour-report.index', 'Daily Labour Reports', array($project->id)) }}</li>
    <li>{{{ trans('dailyLabourReports.daily_labour_reports') }}}</li>
</ol>

@endsection

@section('content')

<div class="modal fade" id="enlargeImageModal" tabindex="-1" role="dialog" aria-labelledby="enlargeImageModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <img src="" class="enlargeImageModalSource" style="width: 100%;">
            </div>
        </div>
    </div>
</div>

<?php
    $locations = PCK\Buildspace\ProjectStructureLocationCode::where("root_id",$dailyLabourReport->projectStructureLocationCode->root_id)->where("level", "<=",$dailyLabourReport->projectStructureLocationCode->level)->where('lft', '<=',$dailyLabourReport->projectStructureLocationCode->lft )->where('rgt', '>=',$dailyLabourReport->projectStructureLocationCode->rgt )->orderBy("level", "asc")->get();
?>

<article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
<div class="jarviswidget jarviswidget-sortable">
    <header role="heading">
        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

        <h2>{{{ trans('dailyLabourReports.daily_labour_reports') }}}</h2>
    </header>

    <!-- widget div-->
    <div role="content">
        <!-- widget content -->
        <div class="widget-body no-padding">
            <div class="smart-form">
                <fieldset>
                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.weather') }}}&#58;</label>
                        <label class="input">
                            {{{$dailyLabourReport->weather->name}}}
                        </label>
                    </section>

                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.location') }}}&#58;</label>
                        <label class="input">
                            @foreach($locations as $location)
                                {{{$location->description}}}<br>
                             @endforeach
                        </label>
                    </section>

                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.trade') }}}&#58;</label>
                        <label class="input">
                            {{{$dailyLabourReport->preDefinedLocationCode->name}}}
                        </label>
                    </section>

                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.contractor') }}}&#58;</label>
                        <label class="input">
                            @if($dailyLabourReport->contractor_id == NULL)
                             {{{ trans('siteManagementDefect.not-selected') }}} 
                             @else
                            {{{$dailyLabourReport->contractorCompany->name}}}
                            @endif
                        </label>
                    </section>

                    <section>
                            @include('daily_labour_reports.labour_info_show')
                    </section>

                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.remark') }}}&#58;</label>
                        <label class="input">
                            {{{$dailyLabourReport->remark}}}
                        </label>
                    </section>

                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.work_description') }}}&#58;</label>
                        <label class="input">
                            {{{$dailyLabourReport->work_description}}}
                        </label>
                    </section>

                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.submitted_by') }}}</label>
                        <label class="input">
                            {{{$dailyLabourReport->submittedUser->name}}}
                        </label>
                    </section>

                    <section>
                        <label class="label">{{{ trans('dailyLabourReports.photo') }}}&#58;</label>
                        @include('site_management.uploaded_files')
                    </section>
                </fieldset>
            </div>
        </div>
        <!-- end widget content -->
    </div>
    <!-- end widget div -->
</div>
</article>

<hr class="horizontal_line">

@endsection

@section('js')

<script>

$(document).ready(function() {
    $('img#uploaded-item').on('click', function() {
        $('.enlargeImageModalSource').attr('src', $(this).attr('src'));
        $('#enlargeImageModal').modal('show');
    });

});

</script>

@endsection