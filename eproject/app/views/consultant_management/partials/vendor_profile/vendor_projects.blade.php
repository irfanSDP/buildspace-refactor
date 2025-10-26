<div>
    <ul id="vendor-awarded-projects-tab" class="nav nav-pills">
        <li class="nav-item active">
            <a class="nav-link" href="#vendor-awarded-projects-content" data-toggle="tab"><i class="fa fa-award"></i> {{ trans('vendorProfile.awardedProjects') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#vendor-completed-projects-content" data-toggle="tab"><i class="far fa-check-circle"></i> {{ trans('vendorProfile.completedProjects') }}</a>
        </li>
    </ul>
</div>
<div id="vendor-projects-tab-content" class="tab-content" style="padding-top:1rem!important;">
    <div class="tab-pane fade in active" id="vendor-awarded-projects-content">
        <div id="awarded-projects-table"></div>
    </div>
    <div class="tab-pane fade" id="vendor-completed-projects-content">
        <div id="completed-projects-table"></div>
    </div>
</div>