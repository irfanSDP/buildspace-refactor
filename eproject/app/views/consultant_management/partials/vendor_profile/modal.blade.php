<div class="modal fade" id="vendorProfileModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width:100%;max-width:98%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-users"></i> {{{trans('vendorProfile.vendorProfile')}}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body no-padding">
                <ul class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#company-details" data-toggle="tab">{{ trans('vendorProfile.companyDetails') }}</a>
                    </li>
                    <li>
                        <a href="#vendor-work-category" data-toggle="tab">{{ trans('vendorManagement.vendorWorkCategories') }}</a>
                    </li>
                    <li>
                        <a href="#vendor-performance-evaluation" data-toggle="tab">{{ trans('vendorProfile.vendorPerformanceEvaluation') }}</a>
                    </li>
                    <li>
                        <a href="#vendor-projects" data-toggle="tab">{{ trans('projects.projects') }}</a>
                    </li>
                </ul>
                <div class="tab-content padding-10">
                    <div class="tab-pane fade in active" id="company-details">
                        <div>
                            <ul id="vendor-company-details-tab" class="nav nav-pills">
                                <li class="nav-item active">
                                    <a class="nav-link" href="#vendor-company-info" data-toggle="tab"><i class="fa fa-info-circle"></i> {{ trans('companies.details') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-personnel" data-toggle="tab"><i class="fa fa-users"></i> {{ trans('vendorManagement.companyPersonnel') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-project-track-record" data-toggle="tab"><i class="fa fa-file-contract"></i> {{ trans('vendorManagement.projectTrackRecord') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#vendor-company-prequalification" data-toggle="tab"><i class="fas fa-list-alt"></i> {{ trans('vendorManagement.preQualification') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div id="vendor-company-details-tab-content" class="tab-content" style="padding-top:1rem!important;">
                            <div class="tab-pane fade in active" id="vendor-company-info" style="height:300px;overflow:auto;">
                                <div class="well">
                                    @include('consultant_management.partials.vendor_profile.company_details')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="vendor-company-personnel">
                                <div class="well">
                                    @include('consultant_management.partials.vendor_profile.company_personnel')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="vendor-company-project-track-record">
                                <div class="well">
                                    @include('consultant_management.partials.vendor_profile.project_track_record')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="vendor-company-prequalification">
                                <div class="well">
                                    @include('consultant_management.partials.vendor_profile.vendor_prequalification')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="vendor-work-category">
                        <div id="vendor_work_categories-table"></div>
                    </div>
                    <div class="tab-pane fade" id="vendor-performance-evaluation">
                        @include('consultant_management.partials.vendor_profile.vendor_performance_evaluations')
                    </div>
                    <div class="tab-pane fade" id="vendor-projects">
                        @include('consultant_management.partials.vendor_profile.vendor_projects')
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>