@extends('layout.main')

<?php
$currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;
$pendingReviews = $user->getConsultantManagementPendingReviews($consultantManagementContract);
?>

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{{ $consultantManagementContract->short_title }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-table"></i> {{{ trans('general.developmentPlanning') }}}
        </h1>
    </div>
</div>
@if(!empty($pendingReviews['roc']) || !empty($pendingReviews['loc']) || !empty($pendingReviews['calling_rfp']) || !empty($pendingReviews['open_rfp']) || !empty($pendingReviews['rfp_resubmission']) || !empty($pendingReviews['approval_document']) || !empty($pendingReviews['loa']))
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <span class="widget-icon"> <i class="fa fa-exclamation-triangle text-danger"></i> </span>
                <h2>To-Do List</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <ul id="consultant-management-todo-tabs" class="nav nav-tabs">
                        <?php $moduleCount = 0;?>
                        @foreach(array_keys($pendingReviews) as $moduleName)
                            <?php
                            if(empty($pendingReviews[$moduleName]))
                            {
                                continue;
                            }
                            ?>
                        <?php
                        switch($moduleName)
                        {
                            case 'roc':
                                $tabTitle = 'Rec. of Consultant';
                                $tabIcon = 'fa-file-signature';
                                break;
                            case 'loc':
                                $tabTitle = trans('general.listOfConsultant');
                                $tabIcon = 'fa-th-list';
                                break;
                            case 'calling_rfp':
                                $tabTitle = trans('general.callingRFP');
                                $tabIcon = 'fa-trophy';
                                break;
                            case 'open_rfp':
                                $tabTitle = 'RFP Opening';
                                $tabIcon = 'fa-star';
                                break;
                            case 'rfp_resubmission':
                                $tabTitle = 'RFP Resubmission';
                                $tabIcon = 'fa-star';
                                break;
                            case 'approval_document':
                                $tabTitle = trans('general.approvalDocument');
                                $tabIcon = 'fa-file-contract';
                                break;
                            case 'loa':
                                $tabTitle = 'LOA';
                                $tabIcon = 'fa-file-code';
                                break;
                            default:
                                $tabTitle = "-";
                                $tabIcon = '';
                        }
                        ?>
                        <li class=" @if($moduleCount == 0) active @endif">
                            <a href="#consultant-management-todo-{{$moduleName}}" data-toggle="tab"><i class="fa fa-fw fa-lg {{$tabIcon}}"></i> {{{ $tabTitle }}}</a>
                        </li>
                        <?php $moduleCount++ ?>
                        @endforeach
                    </ul>
                    <div id="consultant-management-todo-content" class="tab-content">
                    <?php $moduleCount = 0;?>
                    @foreach(array_keys($pendingReviews) as $moduleName)
                        <?php
                            if(empty($pendingReviews[$moduleName]))
                            {
                                continue;
                            }
                        ?>
                        <div class="tab-pane fade in @if($moduleCount==0) active @endif" id="consultant-management-todo-{{$moduleName}}">
                            <div id="todo-table-{{$moduleName}}"></div>
                        </div>
                    <?php $moduleCount++?>
                    @endforeach
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <ul id="consultant-management-contract-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#consultant-management-contract-tab-main-info" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> {{{ trans('projects.mainInformation') }}}</a>
                        </li>
                        <li>
                            <a href="#consultant-management-contract-tab-consultant-categories" data-toggle="tab"><i class="fa fa-fw fa-lg fa-users"></i> {{{ trans('general.consultantCategories') }}}</a>
                        </li>
                    </ul>

                    <div id="consultant-management-contract-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active " id="consultant-management-contract-tab-main-info">
                            @include('consultant_management.contracts.partials.main_info')
                            <div class="row">
                                <div class="col col-lg-12">
                                    <div class="pull-right">
                                    @if($consultantManagementContract->editableByUser($user))
                                    {{ HTML::decode(link_to_route('consultant.management.contracts.contract.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$consultantManagementContract->id], ['class' => 'btn btn-primary'])) }}
                                    @endif
                                    {{ link_to_route('consultant.management.contracts.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade in " id="consultant-management-contract-tab-consultant-categories">
                            @if($isContractEditor)
                            <div class="pull-right" style="padding-bottom:12px;">
                            {{ HTML::decode(link_to_route('consultant.management.vendor.category.rfp.create', '<i class="fa fa-plus"></i> '.trans('general.addConsultantRFP'), [$consultantManagementContract->id], ['class' => 'btn btn-info'])) }}
                            </div>
                            @endif
                            <table class="table table-bordered table-hover" style="text-align: center;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">{{{ trans('contractGroupCategories.vendorCategories') }}}</th>
                                        <th style="text-align: center;width:180px;">{{{ trans('general.costType') }}}</th>
                                        @if(!$user->isConsultantManagementConsultantUser())
                                        <th style="text-align: center;width:180px;">Account Code</th>
                                        @endif
                                        <th style="text-align: center;width:180px;">{{{ trans('general.status') }}}</th>
                                        @if($isContractEditor)
                                        <th style="text-align: center;width:98px;">{{{ trans('general.actions') }}}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!$consultantManagementContract->consultantManagementVendorCategories()->count())
                                    <tr>
                                        <td colspan="@if($isContractEditor) 5 @else 4 @endif">
                                            <div class="alert alert-warning text-center">
                                                <i class="fa-fw fa fa-info"></i>
                                                <strong>Info!</strong> There is no Consultant Category for this Development Plan.
                                            </div>
                                        </td>
                                    </tr>
                                    @else
                                    @foreach($consultantManagementContract->consultantManagementVendorCategories()->orderBy('id', 'asc')->get() as $consultantManagementVendorCategory)
                                    <?php $consultantManagementVendorCategoryStatusTxt = $consultantManagementVendorCategory->getStatusText(); ?>
                                    <tr>
                                        <td style="text-align: left;">
                                            {{{ $consultantManagementVendorCategory->vendorCategory->name}}}
                                        </td>
                                        <td>
                                            {{{ $consultantManagementVendorCategory->getCostTypeText() }}}
                                        </td>
                                        @if(!$user->isConsultantManagementConsultantUser())
                                        <td>
                                            @if(($accountCodes = $consultantManagementVendorCategory->getBsAccountCodes())->count() === 0)
                                                {{ link_to_route('consultant.management.vendor.category.rfp.account.codes.index', 'Assign Account Code', [$consultantManagementVendorCategory->id], ['class' => 'btn btn-xs btn-primary']) }}
                                            @else
                                                {{ link_to_route('consultant.management.vendor.category.rfp.account.codes.index', $accountCodes->first()->code, [$consultantManagementVendorCategory->id], ['class' => 'btn btn-xs btn-success']) }}
                                            @endif

                                            @if($accountCodes->count() > 1)
                                                {{ link_to_route('consultant.management.vendor.category.rfp.account.codes.index', "+".($accountCodes->count()-1), [$consultantManagementVendorCategory->id], ['class' => 'btn btn-xs btn-success']) }}
                                            @endif
                                        </td>
                                        @endif
                                        <td>
                                            @if($consultantManagementVendorCategoryStatusTxt == trans('general.awarded'))
                                            <b class="badge bg-color-green inbox-badge">{{ $consultantManagementVendorCategoryStatusTxt }}</b>
                                            @elseif($consultantManagementVendorCategoryStatusTxt == trans('verifiers.approved'))
                                            <b class="badge bg-color-purple inbox-badge">{{ $consultantManagementVendorCategoryStatusTxt }}</b>
                                            @elseif($consultantManagementVendorCategoryStatusTxt == trans('general.callingRFP'))
                                            <b class="badge bg-color-yellow inbox-badge">{{$consultantManagementVendorCategoryStatusTxt }}</b>
                                            @else
                                            {{{ $consultantManagementVendorCategoryStatusTxt }}}
                                            @endif
                                        </td>
                                        @if($isContractEditor)
                                        <td>
                                            @if($consultantManagementVendorCategory->editable())
                                            {{ HTML::decode(link_to_route('consultant.management.vendor.category.rfp.edit', '<i class="fa fa-edit"></i>', [$consultantManagementContract->id, $consultantManagementVendorCategory->id], ['class' => 'btn btn-xs btn-primary'])) }}
                                            @endif
                                            @if($consultantManagementVendorCategory->deletable())
                                            {{ HTML::decode(link_to_route('consultant.management.vendor.category.rfp.delete', '<i class="fa fa-trash"></i>', [$consultantManagementContract->id, $consultantManagementVendorCategory->id], ['data-id'=>$consultantManagementVendorCategory->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-xs btn-danger'])) }}
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-file-contract"></i> {{{ trans('general.phases') }}}
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
        <div class="pull-right">
        @if($consultantManagementContract->editableByUser($user))
        {{ HTML::decode(link_to_route('consultant.management.contracts.phase.create', '<i class="fa fa-plus"></i> '.trans('forms.add').' '.trans('general.phase'), [$consultantManagementContract->id], ['class' => 'btn btn-info'])) }}
        @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body @if($consultantManagementContract->consultantManagementSubsidiaries->count()) no-padding @endif">
                    @if(!$consultantManagementContract->consultantManagementSubsidiaries->count())
                    <div class="row">
                        <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="alert alert-warning text-center">
                                <i class="fa-fw fa fa-info"></i>
                                <strong>Info!</strong> There is no Phase for this Development Plan.
                            </div>
                        </section>
                    </div>
                    @else
                    <ul id="consultant-management-subsidiaries-tabs" class="nav nav-tabs">
                    @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                        <li @if($key==0) class="active" @endif>
                            <a href="#consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}" data-toggle="tab" title="{{{ $consultantManagementSubsidiary->subsidiary->name}}}">{{{ $consultantManagementSubsidiary->subsidiary->short_name}}}</a>
                        </li>
                    @endforeach
                    </ul>
                    <div id="consultant-management-subsidiaries-tab-content" class="tab-content padding-10">
                    @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                        <div class="tab-pane fade in @if($key==0) active @endif" id="consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}">
                            @include('consultant_management.contracts.partials.phase_info')
                            @if($consultantManagementContract->editableByUser($user))
                            <div class="row">
                                <div class="col col-lg-12">
                                    <div class="pull-right">
                                    {{ HTML::decode(link_to_route('consultant.management.contracts.phase.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$consultantManagementSubsidiary->id], ['class' => 'btn btn-primary'])) }}
                                    {{ HTML::decode(link_to_route('consultant.management.contracts.phase.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$consultantManagementSubsidiary->id], ['data-id'=>$consultantManagementSubsidiary->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('consultant_management.partials.general_attachment')

@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
@include('consultant_management.partials.general_attachment_javascript')

@if(!empty($pendingReviews['roc']) || !empty($pendingReviews['loc']) || !empty($pendingReviews['calling_rfp']) || !empty($pendingReviews['open_rfp']) || !empty($pendingReviews['rfp_resubmission']) || !empty($pendingReviews['approval_document']) || !empty($pendingReviews['loa']))
<script type="text/javascript">
$(document).ready(function () {
    <?php
    $moduleTableInfo = [
        'roc' => [
            'url' => route('consultant.management.todo.list.roc', [$consultantManagementContract->id])
        ],
        'loc' => [
            'url' => route('consultant.management.todo.list.loc', [$consultantManagementContract->id])
        ],
        'calling_rfp' => [
            'url' => route('consultant.management.todo.list.calling_rfp', [$consultantManagementContract->id])
        ],
        'open_rfp' => [
            'url' => route('consultant.management.todo.list.open_rfp', [$consultantManagementContract->id])
        ],
        'rfp_resubmission' => [
            'url' => route('consultant.management.todo.list.rfp_resubmission', [$consultantManagementContract->id])
        ],
        'approval_document' => [
            'url' => route('consultant.management.todo.list.approval_document', [$consultantManagementContract->id])
        ],
        'loa' => [
            'url' => route('consultant.management.todo.list.loa', [$consultantManagementContract->id])
        ]
    ];
    ?>
    var columns = [];
    @foreach(array_keys($pendingReviews) as $moduleName)
        <?php
        if(empty($pendingReviews[$moduleName]))
        {
            continue;
        }
        ?>
        @if($moduleName == 'approval_document')
        columns = [
            {title:"{{ trans('general.no') }}", field:"id", formatter:'rownum', width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"Consultant", field:"company_name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"Consultant RFP", field:"rfp_title", width: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('general.proposedFee') }} ({{{$currencyCode}}})", field:"proposed_fee_sum", width: 200, hozAlign:"right", cssClass:"text-right text-middle", headerSort:false, formatter:"money"},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return (parseInt(rowData.days_pending) > 0) ? '<span class="text-danger">'+rowData.days_pending+'</span>' : rowData.days_pending;
                    }
                }]
            }},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:show']+'" class="btn btn-xs btn-primary"><i class="fa fa-sm fa-search"></i> {{{ trans('general.view') }}}</a>';
                    }
                }]
            }}
        ];
        @else

        columns = [
            {title:"{{ trans('general.no') }}", field:"id", formatter:'rownum', width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"Consultant RFP", field:"rfp_title", minWidth: 300, hozAlign:"left", headerSort:false},
            
            @if($moduleName != 'roc')
            {title:"{{ trans('formBuilder.revision') }}", field:"revision", width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            @endif
            
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return (parseInt(rowData.days_pending) > 0) ? '<span class="text-danger">'+rowData.days_pending+'</span>' : rowData.days_pending;
                    }
                }]
            }},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:show']+'" class="btn btn-xs btn-primary"><i class="fa fa-sm fa-search"></i> {{{ trans('general.view') }}}</a>';
                    }
                }]
            }}
        ];
        
        @endif

        var {{$moduleName}}Table = new Tabulator('#todo-table-{{$moduleName}}', {
            height:220,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ $moduleTableInfo[$moduleName]['url'] }}",
            ajaxConfig: "GET",
            layout:"fitColumns",
            columns: columns
        });

    @endforeach
});
</script>
@endif

@endsection