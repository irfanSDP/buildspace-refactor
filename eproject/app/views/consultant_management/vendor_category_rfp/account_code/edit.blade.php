@extends('layout.main')

<?php
$accountCodes = $vendorCategoryRfp->getBsAccountCodes();
?>

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $contract->short_title, [$contract->id]) }}</li>
        <li>Assign Account Code</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-table"></i> Assign Account Code
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>Contract:</dt>
                                <dd><div class="well">{{ nl2br($contract->title) }}</div></dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-3">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{{ trans('vendorManagement.vendorCategory') }}}:</dt>
                                <dd>{{{ $vendorCategoryRfp->vendorCategory->name }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col col-lg-9">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{{ trans('general.costType') }}}:</dt>
                                <dd>{{{ $vendorCategoryRfp->getCostTypeText() }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <hr class="simple">

                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <h1 class="page-title txt-color-blueDark">Account Code Details</h1>
                        </section>
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>Account Group:</dt>
                                <dd>@if(!$accountCodes->isEmpty()) {{{ $accountCodes->first()->accountGroup->name }}} @else - @endif</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </section>
                    </div>

                    <hr class="simple">

                    <div class="row pb-4">
                        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <a href="{{ route('account.group.index') }}" class="btn btn-info">
                                <i class="fa fa-cogs"></i>&nbsp;Account Code Settings
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <ul id="account_group-tabs" class="nav flex-column nav-pills pb-4">
                            @foreach($accountGroups as $idx => $accountGroup)
                                <li class="nav-item @if($idx == 0) active @endif" data-account_group_id="{{ $accountGroup->id }}">
                                    <a class="nav-link" href="#account_group-{{ $accountGroup->id }}" data-toggle="tab">{{{ $accountGroup->name }}}</a>
                                </li>
                            @endforeach
                            </ul>
                        </div>
                        <div class="col col-xs-12 col-sm-10 col-md-10 col-lg-10">
                            <div class="tab-content">
                            @foreach($accountGroups as $idx => $accountGroup)
                                <div class="tab-pane fade in @if($idx == 0) active @endif" id="account_group-{{ $accountGroup->id }}">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div id="account_codes-{{ $accountGroup->id }}-table"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="pull-right">
                            {{ link_to_route('consultant.management.contracts.contract.show', trans('forms.back'), [$contract->id], ['class' => 'btn btn-default']) }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {
    @foreach($accountGroups as $accountGroup)
    new Tabulator('#account_codes-{{ $accountGroup->id }}-table', {
        height:420,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.vendor.category.rfp.account.codes.ajax.list', [$vendorCategoryRfp->id, $accountGroup->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('finance.accountCodes') }}", field:"code", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter:"textarea"},
            {title:"{{ trans('accountCodes.taxCode') }}", field:"tax_code", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.type') }}", field:"type_txt", width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.actions') }}", field:"assigned", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        var title = (rowData.assigned) ? 'Unassign' : 'Assign';
                        var btn = (rowData.assigned) ? 'btn-danger' : 'btn-primary';
                        return '<button title="'+title+'" class="btn btn-xs '+btn+'" data-id="'+rowData.id+'"" onClick="assignCode('+rowData.id+', '+rowData.group_id+')">'+title+'</button>';
                    }
                }]
            }, editable: false, editor:"select", headerFilter:true, headerFilterParams:[
                "{{ trans('general.all') }}", "Assigned", "Unassigned"
            ]}
        ]
    });
    @endforeach

    @if(!$accountCodes->isEmpty())
        $('[href="#account_group-{{ $accountCodes->first()->account_group_id }}"]').tab('show');
    @endif
});

function assignCode(codeId, grpId){
    const formData  = new FormData();
    formData.append('id', parseInt(codeId));
    formData.append('_token', "{{{ csrf_token() }}}");
    fetch("{{ route('consultant.management.vendor.category.rfp.account.codes.store', [$vendorCategoryRfp->id])}}", {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Csrf-Token': '{{{ csrf_token() }}}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    }).then((response) => {
        if (!response.ok) {
            return;
        }
        return response.json(); 
    })
    .then((data) =>{
        location.reload();
    }).catch((error) => {
        console.log(error);
    });
}
</script>
@endsection