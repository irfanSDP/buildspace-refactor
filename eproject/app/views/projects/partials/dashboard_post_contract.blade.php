<section id="widget-grid" class="">
    <div class="row">
        @if(!empty($dashBoardData->records->contract_amt))
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <div class="jarviswidget" id="dashboard_post_contract-widget-contract-info" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-fw fa-chart-pie"></i> </span>
                    <h2>{{ trans('projects.contractInformation')}}</h2>
                </header>

                <!-- widget div-->
                <div>
                    <div class="jarviswidget-editbox"></div>
                    <div class="widget-body no-padding">
                        <div id="contract_info-pie_chart" style="max-width:380px;padding:0;"></div>
                    </div>
                </div>

            </div>

        </article>

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <div class="jarviswidget" id="dashboard_post_contract-widget-claim-info" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-fw fa-chart-pie"></i> </span>
                    <h2>{{ trans('projects.claimInformation')}}</h2>
                </header>

                <div>
                    <div class="jarviswidget-editbox"></div>
                    <div class="widget-body no-padding">
                        <div id="claim_info-pie_chart" style="max-width:380px;padding:0;"></div>
                    </div>
                </div>

            </div>

        </article>
        @endif

    @if(!empty($projectSchedules))
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

            <div class="jarviswidget" id="dashboard_post_contract-widget-project-duration-info" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-fw fa-chart-line"></i> </span>
                    <h2>{{trans('projects.projectScheduleCostInformation')}}</h2>
                </header>

                <!-- widget div-->
                <div>

                    <div class="jarviswidget-editbox"></div>

                    <div class="widget-body no-padding">

                        <div class="smart-form no-padding-bottom">

                            <div class="widget-body-toolbar bg-color-white smart-form">
                                <div class="pull-right">
                                    <label class="label" style="display:inline;">Project Schedules:</label>
                                    <select id="project_schedule-select" name="project_schedule_id" class="form-control" style="">
                                        @foreach($projectSchedules as $id => $title)
                                            <option value="{{{ $id }}}">{{{$title}}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="project_schedule-chart" class="chart-large has-legend-unique">
                                <div class="text-middle text-center" style="padding-top:32px;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>

                            @if($project->pam2006Detail)
                            <div class="padding-10 no-padding-bottom">
                                <table class="table ">
                                    <thead>
                                        <tr>
                                            <th class="text-middle text-center"> {{trans('projects.commencementDate')}}</th>
                                            <th class="text-middle text-center"> {{trans('projects.completionDate')}}</th>
                                            <th class="text-middle text-center"> {{trans('projects.extensionOfTime')}}</th>
                                            <th class="text-middle text-center"> {{trans('projects.expectedCompletionDate')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-middle text-center"><h5 class="buildspace_info-title">{{ $project->getProjectTimeZoneTime($project->pam2006Detail->commencement_date) }}</h5></td>
                                            <td class="text-middle text-center"><h5 class="buildspace_info-title">{{ $project->getProjectTimeZoneTime($project->pam2006Detail->completion_date) }}</h5></td>
                                            <td class="text-middle text-center"><h5 class="buildspace_info-title">{{$eotDays}} {{trans('projects.days')}}</h5></td>
                                            <td class="text-middle text-center"><h5 class="buildspace_info-title">{{$expectedCompletionDate}}</h5></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </article>
        @endif

    </div>
</section>