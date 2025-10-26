@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ trans('requestForVariation.requestForVariation') }}</li>
	</ol>

	@include('projects.partials.project_status')
@endsection

@section('css')
<style>
.tabulator .tabulator-header .tabulator-col {
    text-align:center;
}

.bottom-spacing {
    margin-bottom: 10px;
}
</style>
@endsection
<?php use \PCK\RequestForVariation\RequestForVariation as RFV; ?>
@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-table" aria-hidden="true"></i> {{ trans('requestForVariation.requestForVariationForm') }}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
            <div class="btn-group pull-right header-btn">
                @include('request_for_variation.rfv.partials.index_actions_menu', array('classes' => 'pull-right'))
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('requestForVariation.requestForVariation') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div id="totalSum-table"></div>
                        <span style="padding:2px;">
                            <div style="padding-top:4px;padding-bottom:30px;">
                                <div class="col-sm-12 bottom-spacing">
                                    <select id="txtSelectedRfvs" name="selectedRfvs[]" class="select2" style="width:100%" multiple></select>
                                </div>
                            </div>
                        </span>
                        <div id="rfvTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @include('request_for_variation.rfv.partials.ai_number_modal')
@endsection

@section('js')
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script>
    $(document).ready(function() {
        'use strict';

        var rfvTableUrl = "{{ route('requestForVariation.list', [$project->id]) }}";
        var rfvAmountInfoUrl = "{{ route('requestForVariation.amount.info.get', [$project->id]) }}";
        var userPermissionGroups = JSON.parse('{{ $userPermissionGroups }}');
        var requestForVariationCategories = JSON.parse('{{ $requestForVariationCategories }}');

        var tabulatorTbl = new Tabulator("#totalSum-table", {
            height: 100,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('requestForVariation.overallCostEstimateForRFV') }}", field: 'rfvOverallTotalAmountByUser', align:"center", resizable:false, headerSort:false, formatter:customMoneyFormatter, formatterParams:{symbol:"{{{$project->getModifiedCurrencyCodeAttribute($project->modified_currency_code)}}} "}},
                {title:"{{ trans('requestForVariation.proposedCostEstimateForRFV') }}", field: 'rfvProposedCostEstimate', align:"center", resizable:false, headerSort:false, formatter:customMoneyFormatter, formatterParams:{symbol:"{{{$project->getModifiedCurrencyCodeAttribute($project->modified_currency_code)}}} "}},
                {title:"{{ trans('requestForVariation.approvedRFVAmount') }}", field: 'accumulativeApprovedRfvAmountByUser', align:"center", resizable:false, headerSort:false, formatter:customMoneyFormatter, formatterParams:{symbol:"{{{$project->getModifiedCurrencyCodeAttribute($project->modified_currency_code)}}} "}},
            ],
            ajaxURL: rfvAmountInfoUrl,
            ajaxConfig: 'GET',
            ajaxParams: { projectId: "{{{ $project->id }}}", groupId: null },
        });

        var columns = [
            { title: "id", field: 'id', visible:false },
            { title: "permission_group_id", field: 'permission_group_id', visible:false },
            { width: 30, formatter: "rowSelection", titleFormatter: "rowSelection", align: 'center', headerSort: false },
            { title: "{{ trans('requestForVariation.group') }}", field: 'permission_group_name', width: 180, align: 'center', headerSort: false, headerFilter: 'select', headerFilterParams: userPermissionGroups },
            { title: "{{ trans('requestForVariation.rfvNumber') }}", field: 'rfvNumber', width: 120, 'align': 'center', cssClass:"text-center", headerSort:true },
            { title: "{{ trans('requestForVariation.aiNumber') }}", field: 'aiNumber', width: 160, align: 'center', headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter by AI Number', formatter: rfvAiNumberFormatter },
            { title: "{{ trans('requestForVariation.description') }}", field: 'description', minWidth:420, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter by description', formatter: rfvDescriptionFormatter },
            { title: "{{ trans('requestForVariation.categoryOfRfv') }}", field: 'rfvCategory', width: 260, headerSort:false, headerFilter: 'select', headerFilterParams: requestForVariationCategories, headerFilterPlaceholder: 'filter by category' },
            { title: "{{ trans('requestForVariation.nettOmissionAddition') }}", field: 'nettOmissionAddition', width: 180, 'align': 'center', cssClass:"text-center", headerSort:false },
            { title: "{{  trans('requestForVariation.createdBy') }}", field: 'createdBy', width: 220, 'align': 'center', cssClass:"text-center", headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter by creator' },
            { title: "{{  trans('requestForVariation.status') }}", field: 'statusText', width: 160, 'align': 'center', cssClass:"text-center", headerSort:false },
        ];

        var rfvTable = new Tabulator('#rfvTable', {
            height:350,
            columns: columns,
            layout:"fitColumns",
            ajaxURL: rfvTableUrl,
            ajaxConfig: "GET",
            ajaxParams: { projectId: "{{{ $project->id }}}" },
            placeholder:"No Data Available",
            selectableCheck:function(row){
                return !row.getData().is_deleted;
            },
            rowSelectionChanged: function(data, rows) {
                // Deselect unselectable rows. rowSelection formatter doesn't handle them properly.
                rows.forEach(function(row){
                    if(row.getData().is_deleted) {
                        rfvTable.deselectRow(row.getData().id);
                    }
                });
                var rfvIds = [];
                var selectedRfvs = rfvTable.getSelectedData();

                $('#txtSelectedRfvs').html('');

                selectedRfvs.forEach(function(item, index) {
                    var option = new Option('#' + item.rfvNumber, item.id, true, true);
                    $('#txtSelectedRfvs').append(option).trigger('change');

                    rfvIds.push(item.id);
                });

                tabulatorTbl.setData(rfvAmountInfoUrl, { projectId: "{{{ $project->id }}}", rfvIds: rfvIds });
            },
        });

        $('#txtSelectedRfvs').on('select2:unselect', function (e) {
            rfvTable.deselectRow(e.params.data.id);
        });

        $('#txtSelectedRfvs').on('select2:opening', function (e) {
            e.preventDefault();
        });

        $(document).on('click', '#btnShowContractAndContingencySum', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('requestForVariation.cncsum.show', [$project->id]) }}";
        });

        $(document).on('click', 'button[data-action=update_ai_number]', function(e) {
            $('#ai_number-rfv_id').val($(this).data('rfv_id'));
            $('#ai_number_modal').modal('show');
            $('#ai-number-input').select();
        });

        $(document).on('click', 'input[data-action=submit-ai-number]', function(e) {
            var rfvId = $('#ai_number-rfv_id').val();

            if(!isNaN(parseInt(rfvId))){
                var url = "{{ route('requestForVariation.ainumber.save', [$project->id, 'rfvId']) }}".replace('rfvId', rfvId);
                var aiNumber = $('#ai-number-input').val();
                var validationSuccessful = validateAiNumber(aiNumber);

                if(!validationSuccessful) return;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        ai_number: aiNumber,
                    },
                    success: function (data) {
                        if (data['success']) {
                            window.location.reload();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    },
                });
            }
        });

        $(document).on('click', '#btnDownloadVariationOrderReport', function(e) {
            e.preventDefault();

            var selectedRfvs = $('#txtSelectedRfvs').select2('data');

            if(selectedRfvs.length == 0) return;

            var selectedRfv = [];

            selectedRfvs.forEach(function(item, index) {
                selectedRfv.push(item.id);
            });

            var voReportUrl = "{{ route('variationOrder.report.download', [$project->id]) }}";
            voReportUrl += '?rfvIds=' + selectedRfv.join();

            window.open(voReportUrl, '_blank');
        });
    });

    function validateAiNumber(value) {
        if(value == '') {
            return false;
        }

        return true;
    }

    var rfvAiNumberFormatter = function(cell, formatterParams, onRendered) {
        var rfvApproved = cell.getRow().getData().status == "{{{ \PCK\RequestForVariation\RequestForVariation::STATUS_APPROVED }}}";
        var rfvAiNumberUpdated = (cell.getRow().getData().aiNumber !== null);

        if(rfvApproved) {
            if(rfvAiNumberUpdated) {
                return cell.getRow().getData().aiNumber;
            } else {
                var updateAiNumberButton = document.createElement('button');
                updateAiNumberButton.id = 'btnUpdateRfvAiNumber_' + cell.getRow().getData().id;
                updateAiNumberButton.dataset.rfv_id = cell.getRow().getData().id;
                updateAiNumberButton.dataset.action = 'update_ai_number';
                updateAiNumberButton.dataset.csrf_token = cell.getRow().getData().csrf_token;
                updateAiNumberButton.innerHTML = '<i class="fa fa-edit"></i>';

                return updateAiNumberButton;
            }
        }

        return null;
    }

    var rfvDescriptionFormatter = function(cell, formatterParams, onRendered) {
        if(cell.getData().is_deleted) return cell.getData().description;
        var rfvShowLink = document.createElement('a');
        rfvShowLink.id = 'btnShowRfvLink_' + cell.getRow().getData().id;
        rfvShowLink.href = cell.getRow().getData().route_show;
        rfvShowLink.innerHTML = cell.getRow().getData().description;
        rfvShowLink.style['text-decoration'] = 'underline';
        rfvShowLink.style['user-select'] = 'none';

        return rfvShowLink;
    };

    var customMoneyFormatter = function(cell, formatterParams, onRendered) {
        var floatVal = parseFloat(cell.getValue()),number,integer,decimal,rgx;
        var decimalSym = formatterParams.decimal || ".";
        var thousandSym = formatterParams.thousand || ",";
        var symbol = formatterParams.symbol || "";
        var after = !!formatterParams.symbolAfter;
        var precision = typeof formatterParams.precision !== "undefined" ? formatterParams.precision : 2;
        if (isNaN(floatVal) || floatVal == 0) {
            return this.emptyToSpace();
        }
        number = precision !== false ? floatVal.toFixed(precision) : floatVal;
        number = String(number).split(".");
        integer = number[0];
        decimal = number.length > 1 ? decimalSym + number[1] : "";
        rgx = /(\d+)(\d{3})/;
        var absInt = Math.abs(integer);
        while (rgx.test(absInt)) {
            absInt = absInt.toString().replace(rgx, "$1" + thousandSym + "$2");
        }
        var ret;
        if(parseInt(integer) > 0){
            cell.getElement().style.color = "";
            ret = absInt + decimal;
        }else{
            cell.getElement().style.color = "red";
            ret = "("+absInt + decimal+")";
        }
        return after ? ret + symbol : symbol + ret;
    };
    </script>
@endsection
