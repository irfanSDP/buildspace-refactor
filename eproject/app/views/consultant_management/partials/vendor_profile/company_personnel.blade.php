<div class="row">
    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
        <ul class="nav flex-column nav-pills" style="padding-bottom:4px;">
            <li class="nav-item active">
                <a class="nav-link" href="#company-personnel-director" data-toggle="tab">{{{ trans('vendorManagement.directors') }}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#company-personnel-shareholder" data-toggle="tab">{{{ trans('vendorManagement.shareholders') }}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#company-personnel-owner" data-toggle="tab">{{{ trans('vendorManagement.headOfCompany') }}}</a>
            </li>
        </ul>
    </div>
    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
        <div class="tab-content">
            <div class="tab-pane fade in active" id="company-personnel-director">
                <div id="company-personnel-directors-table"></div>
            </div>
            <div class="tab-pane fade" id="company-personnel-shareholder">
                <div id="company-personnel-shareholders-table"></div>
            </div>
            <div class="tab-pane fade" id="company-personnel-owner">
                <div id="company-personnel-head-of-company-table"></div>
            </div>
        </div>
    </div>
</div>