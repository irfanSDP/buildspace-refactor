<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <ul class="nav nav-pills" style="padding-bottom:4px;">
            <li class="nav-item active">
                <a class="nav-link project-track-record-tab" href="#completed-project-track-record-content" data-toggle="tab" id="completed-project-track-record-tab"><i class="fa fa-award"></i> {{{ trans('vendorManagement.completedProjects') }}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link project-track-record-tab" href="#current-project-track-record-content" data-toggle="tab" id="current-project-track-record-tab"><i class="far fa-check-circle"></i> {{{ trans('vendorManagement.currentProjects') }}}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade in active" id="completed-project-track-record-content">
                <div id="completed-project-track-record-table"></div>
            </div>
            <div class="tab-pane fade" id="current-project-track-record-content">
                <div id="current-project-track-record-table"></div>
            </div>
        </div>
    </div>
</div>