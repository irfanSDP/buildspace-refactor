<section id="widget-grid" class="">
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget" id="dashboard_master-widget-id-0" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-fw fa-chart-bar"></i> </span>
                    <h2>{{trans('projects.noOfProjectsByStatuses')}}</h2>
                </header>

                <!-- widget div-->
                <div>

                    <div class="jarviswidget-editbox"></div>
                    <!-- end widget edit box -->

                    <!-- widget content -->
                    <div class="widget-body no-padding">

                        <div class="row padding-10 no-space">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                <div id="project-pie-chart" class="chart"></div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">

                                <div class="row">
                                    <div class="col-xs-6 col-sm-6 col-md-12 col-lg-12">
                                        <div class="padding-10">
                                            <span class="text"> Design Stage <span class="pull-right"><span style="color:#006699"><strong>{{{ $designCount = $projectsData['design'] }}}</strong></span> Project(s)</span> </span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-12 col-lg-12">
                                        <div class="padding-10">
                                            <span class="text"> Calling Tender <span class="pull-right"><span style="color:#001a66"><strong>{{{ $callingTenderCount = $projectsData['callingTender'] }}}</strong></span> Project(s)</span> </span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-12 col-lg-12">
                                        <div class="padding-10">
                                            <span class="text"> Closed Tender <span class="pull-right"><span style="color:#4d0066"><strong>{{{ $closedTenderCount = $projectsData['closedTender'] }}}</strong></span> Project(s)</span> </span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-12 col-lg-12">
                                        <div class="padding-10">
                                            <span class="text"> Post Contract <span class="pull-right"><span style="color:#006600"><strong>{{{ $postContractCount = $projectsData['postContract'] }}}</strong></span> Project(s)</span> </span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-12 col-lg-12">
                                        <div class="padding-10">
                                            <span class="text"> Completed <span class="pull-right"><span style="color:#00664d"><strong>{{{ $completedCount = $projectsData['completed'] }}}</strong></span> Project(s)</span> </span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-12 col-lg-12">
                                        <div class="padding-10">
                                            <hr/>
                                            <strong><span class="text"> Total <span class="pull-right">{{{ $designCount + $callingTenderCount + $closedTenderCount + $postContractCount + $completedCount }}} Project(s)</span> </span></strong>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!-- end widget content -->

                </div>
                <!-- end widget div -->

            </div>

        </article>
    </div>
</section>