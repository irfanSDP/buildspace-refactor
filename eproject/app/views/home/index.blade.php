@extends('layout.main', array('hide_ribbon'=>true))

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
        <h1 class="page-title txt-color-blueDark"><i class="fa-fw fa fa-home"></i> Hi, {{{$user->name}}}! </h1>
    </div>
</div>

<div id="company-list" name="company-list">

</div>
@if($user->isTemporaryAccount())
<div class="well">
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="alert alert-warning fade in">
                <i class="fa-fw fa fa-info"></i>
                <strong>Info!</strong> This is a temporary login account. Please complete the registration process before it expires.
            </div>
        </article>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            This account expires at <strong>{{ $user->purge_date->format(\Config::get('dates.readable_timestamp')) }}</strong>
        </div>
    </div>
</div>
@endif

@if(!$user->isSuperAdmin())

<div class="row">
    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-3" id="tendering-todo-container">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-inbox"></i> </span>
                <h2>{{ trans('general.tenderingToDos') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding smart-form" id="wid-tendering-todo" style="height:360px;overflow-y:auto;">
                    <div class="text-middle text-center" style="padding-top:32px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ trans('general.loading') }}...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-3" id="post-contract-todo-container">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
            <header>
                <span class="widget-icon"> <i class="far fa-file"></i> </span>
                <h2>{{ trans('general.postContractToDos') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding smart-form" id="wid-post-contract-todo" style="height:360px;overflow-y:auto;">
                    <div class="text-middle text-center" style="padding-top:32px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ trans('general.loading') }}......</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-3" id="site-module-todo-container">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
            <header>
                <span class="widget-icon"> <i class="far fa-file"></i> </span>
                <h2>{{ trans('general.siteModuleToDos') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding smart-form" id="wid-site-module-todo" style="height:360px;overflow-y:auto;">
                    <div class="text-middle text-center" style="padding-top:32px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ trans('general.loading') }}......</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-3" id="vendor-management-todo-container">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-th-list"></i> </span>
                <h2>{{ trans('general.vendorManagementToDos') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding smart-form" id="wid-vendor-management-todo" style="height:360px;overflow-y:auto;">
                    <div class="text-middle text-center" style="padding-top:32px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ trans('general.loading') }}......</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-6 col-md-6 col-lg-3" id="consultant-management-todo-container">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-th-list"></i> </span>
                <h2>{{ trans('general.consultantManagementToDos') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding smart-form" id="wid-consultant-management-todo" style="height:360px;overflow-y:auto;">
                    <div class="text-middle text-center" style="padding-top:32px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ trans('general.loading') }}......</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-3" id="digital-star-todo-container">
            <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-inbox"></i> </span>
                    <h2>{{ trans('digitalStar/digitalStar.digitalStarToDos') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding smart-form" id="wid-digital-star-todo" style="height:360px;overflow-y:auto;">
                        <div class="text-middle text-center" style="padding-top:32px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">{{ trans('general.loading') }}......</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    @endif
</div>

@endif

<div class="row">
@if(count($overallStatusInfo) > 1)
<?php
$col = (count($overallStatusInfo) > 1) ? "4" : "6";
?>
    <article class="col-xs-12 col-sm-{{$col}} col-md-{{$col}} col-lg-{{$col}}">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false">
            <div>
                <div class="widget-body no-padding smart-form">
                    <div class="text-middle text-center" style="padding-top:12px;">
                        <h5>{{trans('projects.noOfProjectsByStatuses')}}</h5>
                        <div id="project-pie-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </article>
<?php
$chunks = array_chunk($overallStatusInfo, ceil(count($overallStatusInfo)/2));
$cnt = 0;
?>

    @foreach($chunks as $chunk)
    <article class="col-xs-12 col-sm-{{$col}} col-md-{{$col}} col-lg-{{$col}}">
        @foreach($chunk as $data)
        <div class="panel @if((int)$data['id'] > 0) panel-{{$data['color']['class_name']}} bg-color-{{$data['color']['class_name']}} @else  panel-darken @endif" style="{{ isset($data['color']['hex']) ? 'background-color:'.$data['color']['hex'].'!important' : '' }}">
            <div class="panel-body status">
                <ul class="inline-info">
                    <li class="inline-info-data">
                        <h5 @if((int)$data['id'] < 0) style="color:#505050;" @endif> <span @if((int)$data['id'] > 0) class="txt-color-white" @endif>{{$data['total']}} {{{trans('projects.projects')}}}</span> {{$data['name']}} </h5>
                    </li>
                </ul>
            </div>
        </div>
        <?php $cnt++?>
        @endforeach
    </article>
    @endforeach
@else

<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <div class="alert alert-info fade in">
        <i class="fa-fw fa fa-info"></i>
        <strong>Info!</strong> You don't have any project information yet.
    </div>
</article>

@endif
</div>

@if($user->hasCompanyRoles([\PCK\ContractGroups\Types\Role::CONTRACTOR]))

<style>
    .popup-dialog {
        display: none; /* Hidden by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        z-index: 1000;
    }

    .popup-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 700px;
        margin: 100px auto;
        position: relative;
        text-align: center;
    }
    
</style>

<!-- Popup Dialog -->
<div id="contractorModal" class="popup-dialog modal">
    <div class="popup-content">
        <article>
            <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" style="background:#FFFFE0!important;border-bottom:1px solid #1da1f2;">
                <div style="background-color:#FFFFE0!important;">
                    <div class="widget-body" style="height:360px;background-color:#FFFFE0!important;">
                    <img style="max-height: 80px; max-width: 120px;" src="{{ asset('img/bs_procureX_logo.png') }}" alt="BuildSpace"/>
                    <div style="font-size:13px!important;font-weight:700;font-family: Roboto Condensed,sans-serif;margin-top:30px;">
                        <p style="color:red">🚧 Still using Excel Sheet to price your bids?</p>
                        <p style="color:red">That’s slowing you down.</p>
                        <p><strong>With BuildSpace ProCureX you can:</strong></p>
                        <p>⚡ Price faster</p>
                        <p>🎯 Submit accurate bids</p>
                        <p>💰 Control your project budget when awarded</p>
                        <p style="color:green">👉 Stop wasting time. Tender smarter today.</p>
                        <p>🚀 Submit your enquiry <a href="https://docs.google.com/forms/d/e/1FAIpQLSddNFhqc5URapoEkxjBDJxFPo4xjFoT2FuaDS8B0BH6Xemtyg/viewform" target="_blank">here</a> or WhatsApp <a href="https://wa.me/601159716225" target="_blank">011-59716225</a></p>
                    </div>
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>

<div class="row">
    <article class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" style="background:#1da1f2!important;border-bottom:1px solid #1da1f2;">
            <div style="background-color:#1da1f2!important;">
                <div class="widget-body" style="height:360px;background-color:#1da1f2!important;overflow-y:auto;">
                <img style="max-height: 80px; max-width: 120px" src="{{ asset('img/buildspace-login-logo.png') }}" alt="BuildSpace"/>
                <div style="font-size:13px!important;font-weight:700;font-family: Roboto Condensed,sans-serif;margin-top:12px;">
                    <p class="text-white">Learn how to download and submit a tender. <a href="https://buildspace.my/tenderer-guide-pdf/" target="_blank" class="plain text-warning">(Click Here)</a></p>
                    <p class="text-white">Should you have any enquiries, fell free to submit a support ticket. <a href="https://buildspacesupport.freshdesk.com/support/tickets/new" target="_blank" class="plain text-warning">(Click Here)</a></p>
                    <p class="text-white">Visit <a href="https://buildspacesupport.freshdesk.com/support/solutions/articles/70000593152-general-tutorials-for-tenderer" target="_blank" class="plain text-warning">BuildSpace Tutorials.</a>
                </div>
                </div>
            </div>
        </div>
    </article>

    <article class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" style="background:#FFFFE0!important;border-bottom:1px solid #1da1f2;">
            <div style="background-color:#FFFFE0!important;">
                <div class="widget-body" style="height:360px;background-color:#FFFFE0!important;">
                <img style="max-height: 80px; max-width: 120px" src="{{ asset('img/bs_procureX_logo.png') }}" alt="BuildSpace"/>
                <div style="font-size:13px!important;font-weight:700;font-family: Roboto Condensed,sans-serif;margin-top:30px;">
                    <p>Want to subscribe our BuildSpace ProCureX at as low as RM500 per month with the following features?</p>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <ul class="text-left">
                                <li><strong>Library Manager</strong></li>
                                <li><strong>Project Builder & Tendering Modules</strong></li>
                                <li><strong>Post-Contract Management</strong></li>
                                <li><strong>Project Scheduling</strong></li>
                                <li><strong>Cost Manager</strong></li>
                            </ul>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <ul class="text-left">
                                <li><strong>Budget Management</strong></li>
                                <li><strong>Purchase Request & Approvals</strong></li>
                                <li><strong>RFQ & PO Management</strong></li>
                                <li><strong>Good Receipt Note (GRN)</strong></li>
                                <li><strong>E-Invoicing & LHDN Comliance</strong></li>
                            </ul>
                        </div>
                    </div>
                    <br><br><br>
                    <p>Interested? Don’t miss the chance to explore it further — <a href="https://buildspace.my/request-demo/" target="_blank">Request a free demo now!</a></p>
                </div>
                </div>
            </div>
        </div>
    </article>
</div>

@endif

@endsection

@section('js')

@if(count($overallStatusInfo) > 1)
<script src="{{ asset('js/plugin/apexcharts/apexcharts.min.js') }}"></script>
<?php
$series = [];
$colors = [];
$labes  = [];
foreach($overallStatusInfo as $idx => $statusInfo)
{
    if((int)$statusInfo['id'] > 0)
    {
        $series[] = $statusInfo['total'];
        $colors[] = $statusInfo['color']['hex'];
        $labels[] = $statusInfo['name'];
    }
}
?>
@endif

<script type="text/javascript">
    $(document).ready(function() {

        @if($user->hasCompanyRoles([\PCK\ContractGroups\Types\Role::CONTRACTOR]))   
            $('#contractorModal').fadeIn();
        
            // Close the popup
            $('#close-btn').click(function () {
                console.log('close clicked');
                $('#contractorModal').fadeOut();
            });

            // Close the popup when clicking outside the content
            $(window).click(function (e) {
                if ($(e.target).is('#contractorModal')) {
                    $('#contractorModal').fadeOut();
                }
            });
        @endif

        @if(count($overallStatusInfo) > 1)
        
        var chart = new ApexCharts(document.querySelector("#project-pie-chart"), {
            colors: {{json_encode($colors)}},
            series: {{json_encode($series)}},
            labels: {{json_encode($labels)}},
            chart: {
                type: 'donut',
                height: '200px'
            },
            legend: {
                show: true,
                fontSize: '10px'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: '100%'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        });
        chart.render();

        @endif

        @if(!$user->isSuperAdmin())
            var renderedContainers = [];
            var groupsWithData = 0;
    
            $.getJSON('{{ route('home.my.todo.list.ajax') }}')
                .done(function (data) {
                    renderToDoList(data['tendering'], 'wid-tendering-todo', 'tendering-todo-container', 1);
                    renderToDoList(data['post_contract'], 'wid-post-contract-todo', 'post-contract-todo-container', 1);
                    renderToDoList(data['site_module'], 'wid-site-module-todo', 'site-module-todo-container', 1);
                    renderToDoList(data['vendor_management'], 'wid-vendor-management-todo', 'vendor-management-todo-container', 2);
                    renderToDoList(data['consultant_management'], 'wid-consultant-management-todo', 'consultant-management-todo-container', 3);

                    if ($('#digital-star-todo-container').length > 0) {
                        renderToDoList(data['digital_star'], 'wid-digital-star-todo', 'digital-star-todo-container', 1);
                    }

                    if(groupsWithData > 0) {
                        resizeToDoLists();
                    }
                })
                .fail(function (jqxhr,settings,ex) {
                    let errorMsg = "{{ trans('errors.anErrorOccurred') }}";
                    $('#wid-post-contract-todo').html('<div class="alert alert-danger text-center">' + errorMsg + '</div>');

                    const widgetTargets = [
                        'wid-tendering-todo',
                        'wid-post-contract-todo',
                        'wid-site-module-todo',
                        'wid-vendor-management-todo',
                        'wid-consultant-management-todo',
                        'wid-digital-star-todo'
                    ];

                    const errorHTML = '<div class="alert alert-danger text-center">' + errorMsg + '</div>';

                    for (var i = 0; i < widgetTargets.length; i++) {
                        $('#' + widgetTargets[i]).html(errorHTML);
                    }
                });

            function renderToDoList(dataGroup, targetId, containerId, templateNo) {
                if (!dataGroup ||
                    (Array.isArray(dataGroup.records) && dataGroup.records.length === 0) ||
                    (typeof dataGroup === 'object' && Object.keys(dataGroup).length === 0)
                ) {
                    $('#' + containerId).hide(200);
                    return;
                }

                var str = '';

                switch (templateNo) {
                    case 1: // Project-style
                        for (var i in dataGroup) {
                            str += '<h5 class="todo-group-title bg-color-blue"><i class="fa fa-exclamation-triangle"></i> '
                                + DOMPurify.sanitize(dataGroup[i].name)
                                + ' <span class="badge bg-color-red inbox-badge" style="font-size:10px; margin-left:5px;">'
                                + dataGroup[i].records.length
                                + '</span></h5><ul class="todo">';

                            for (var k in dataGroup[i].records) {
                                var obj = dataGroup[i].records[k];
                                var projTitle = obj.parent_project_title
                                    ? DOMPurify.sanitize(obj.parent_project_title)
                                    : DOMPurify.sanitize(obj.project_title);
                                var subProjTitle = obj.parent_project_title
                                    ? '[' + DOMPurify.sanitize(obj.project_reference) + '] ' + DOMPurify.sanitize(obj.project_title)
                                    : '[' + DOMPurify.sanitize(obj.project_reference) + ']';
                                var daysPending = parseInt(obj.days_pending)
                                    ? '<span style="margin-top:4px;width:120px;"><span class="badge bg-color-red" style="font-size:10px;">'
                                    + DOMPurify.sanitize(obj.days_pending)
                                    + '</span> Day(s) pending</span>'
                                    : '';

                                str += '<li>'
                                    + '<span> <a href="' + DOMPurify.sanitize(obj.route) + '" class="btn btn-xs btn-primary"><i class="fa fa-sm fa-search"></i></a> </span>'
                                    + '<p>' + projTitle
                                    + '<span class="date" style="margin-top:2px;">' + subProjTitle + '</span>'
                                    + daysPending
                                    + '</p></li>';
                            }

                            str += '</ul>';
                        }
                        break;

                    case 2: // Vendor-style
                        for (var i in dataGroup) {
                            str += '<h5 class="todo-group-title bg-color-blue"><i class="fa fa-exclamation-triangle"></i> '
                                + DOMPurify.sanitize(dataGroup[i].name)
                                + ' <span class="badge bg-color-red inbox-badge" style="font-size:10px; margin-left:5px;">'
                                + dataGroup[i].records.length
                                + '</span></h5><ul class="todo">';

                            for (var k in dataGroup[i].records) {
                                var obj = dataGroup[i].records[k];
                                var vendorName = DOMPurify.sanitize(obj.vendor_name);
                                var daysPending = parseInt(obj.days_pending)
                                    ? '<span style="margin-top:4px;width:120px;"><span class="badge bg-color-red" style="font-size:10px;">'
                                    + DOMPurify.sanitize(obj.days_pending)
                                    + '</span> Day(s) pending</span>'
                                    : '';

                                str += '<li>'
                                    + '<span> <a href="' + DOMPurify.sanitize(obj.route) + '" class="btn btn-xs btn-primary"><i class="fa fa-sm fa-search"></i></a> </span>'
                                    + '<p>' + vendorName + daysPending + '</p>'
                                    + '</li>';
                            }

                            str += '</ul>';
                        }
                        break;

                    case 3: // Flat-style
                        str += '<h5 class="todo-group-title bg-color-blue"><i class="fa fa-exclamation-triangle"></i> '
                            + "{{ trans('general.pendingTasks') }}"
                            + ' <span class="badge bg-color-red inbox-badge" style="font-size:10px; margin-left:5px;">'
                            + DOMPurify.sanitize(dataGroup.total_pending)
                            + '</span></h5><ul class="todo">';

                        for (var i in dataGroup.records) {
                            var obj = dataGroup.records[i];
                            var daysPending = parseInt(obj.total_days_pending)
                                ? '<span style="margin-top:4px;width:120px;"><span class="badge bg-color-red" style="font-size:10px;">'
                                + DOMPurify.sanitize(obj.total_days_pending)
                                + '</span> Day(s) pending</span>'
                                : '';

                            str += '<li>'
                                + '<span> <a href="' + DOMPurify.sanitize(obj.route) + '" class="btn btn-xs btn-primary"><i class="fa fa-sm fa-search"></i></a> </span>'
                                + '<p>' + DOMPurify.sanitize(obj.title)
                                + '<span class="date" style="margin-top:2px;">' + DOMPurify.sanitize(obj.reference_no) + '</span>'
                                + daysPending + '</p>'
                                + '</li>';
                        }

                        str += '</ul>';
                        break;
                }

                $('#' + targetId).html(str);
                //$('#' + containerId).show();
                renderedContainers.push(containerId);
                groupsWithData++;
            }

            function resizeToDoLists() {
                const columnsInRow = 12; // this is fixed

                //var colXS, colSM, colMD, colLG = columnsInRow;
                var colXS = columnsInRow;
                var colSM, colMD, colLG;

                // small and medium occupy 6 columns at the very least
                if (groupsWithData > 2) {
                    colSM = (columnsInRow / 2);
                    colMD = (columnsInRow / 2);
                    colLG = (groupsWithData == 3) ? (columnsInRow / groupsWithData) : (columnsInRow / 2);
                } else {
                    colSM = (columnsInRow / groupsWithData);
                    colMD = (columnsInRow / groupsWithData);
                    colLG = (columnsInRow / groupsWithData);
                }

                var classString = `col-xs-${colXS} col-sm-${colSM} col-md-${colMD} col-lg-${colLG}`;

                for (var i = 0; i < renderedContainers.length; i++) {
                    $('#' + renderedContainers[i]).removeClass().addClass(classString);
                }
            }
        @endif
    });
</script>

@endsection