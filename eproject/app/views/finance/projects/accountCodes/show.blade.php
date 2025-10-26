@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('finance.claim-certificate', trans('navigation/mainnav.financeModule'), []) }}</li>
        <li>{{ link_to_route('finance.account.code.settings.index', trans('finance.accountCodeSettings'), []) }}</li>
        <li>{{{ $project->title }}}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <article class="col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget" id="wid-project-codes-settings">
                <header>
                    <span class="widget-icon"><i class="fa fa-edit"></i></span>
                    <h2 class="hidden-mobile">{{ trans('finance.accountCodeSettings') }} ({{ trans('projects.project') }} : {{{ $project->title }}})</h2>
                </header>
                <div class="no-padding">
                    <ul id="toDoListTab" class="nav nav-tabs">
                        <li class="active">
                            <a href="#projectCodeSettingsTabContent" data-toggle="tab" id="projectCodeSettingsTarget"><i class="fa fa-list-ul"></i> {{ trans('accountCodes.projectcodeSettings') }}</a>
                        </li>
                        <li>
                            <a href="#itemCodeSettingsTabContent" data-toggle="tab" id="itemCodeSettingsTarget"><i class="fa fa-list-ul"></i> {{ trans('accountCodes.itemCodeSettings') }}</a>
                        </li>
                        <li>
                            <a href="#contractorInfoTabContent" data-toggle="tab" id="contractorInfoTarget"><i class="fa fa-user"></i> {{ trans('companies.company') }}</a>
                        </li>
                    </ul>
                    <div id="accountcodeSettingsTabContentPane" class="tab-content padding-10" style="height: 100%;">
                        <div class="tab-pane fade in active" id="projectCodeSettingsTabContent">
                            <div class="widget-body">
                                <fieldset>
                                    <section>
                                        @if(!$isLocked)
                                            <div class="row" style="margin-bottom: 10px;">
                                                <div class="col col-sm-6">
                                                    <select id="apportionmentTypeFilter" class="select2" style="width:100%" data-action="filter">
                                                        @foreach($apportionmentTypes as $apportionmentType)
                                                            <?php $selectedAttribute = ($apportionmentType->id == $accountCodeSetting->apportionment_type_id) ? 'selected' : null; ?>
                                                            <option value="{{{ $apportionmentType->id }}}" {{{ $selectedAttribute }}}>{{{ $apportionmentType->name }}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col col-sm-6">
                                                    <button id="btnAssignSubsidiaries" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-target="#subsidiariesHierarchyModal" data-backdrop="static" data-keyboard="false"><i class="fa fa-users"></i> {{ trans('accountCodes.assignSubsidiaries') }}</button>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col col-sm-12">
                                                <div id="projectCodeSettingsTable"></div>
                                            </div>
                                        </div>
                                    </section>
                                </fieldset>
                                @if(!$isLocked)
                                <form id="accountCodeSettingForm" action="{{ route('account.code.settings.approval.submit', [ $project->id ]) }}" method="POST" data-event="submit" class="smart-form">
                                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                    @include('verifiers.select_verifiers', [
                                        'verifiers' => $verifiers,
                                    ])
                                    <button type="submit" id="btnSubmit" class="btn btn-primary btn-sm pull-right" style="margin-left:4px;"><i class="fa fa-save"></i> {{ trans('forms.submit') }}</button>
                                </form>
                                @endif
                                @if($isCurrentVerifier)
                                <div class="pull-right">
                                    @include('verifiers.approvalForm', [
                                        'object'	=> $accountCodeSetting,
                                    ])
                                </div>
                                @endif
                                <button id="btnViewLogs" type="button" class="btn btn-sm btn-success pull-right" style="margin-right:4px;">{{ trans('accountCodes.viewLogs') }}</button>
                            </div>
                        </div>
                        <div class="tab-pane fade in" id="itemCodeSettingsTabContent">
                            <div class="widget-body">
                                <fieldset>
                                    <section>
                                        <div class="row">
                                            <div class="col col-sm-12">
                                                <div class="well" id="item-code-settings-amount-information">
                                                    <div class="row">
                                                        <div class="col col-sm-3">
                                                            <dl class="dl-horizontal no-margin">
                                                                <dt>{{{ trans('projects.contractSum') }}}:</dt>
                                                                <dd><strong>{{ number_format($contractSum, 2, '.', ',') }}</strong></dd>
                                                            </dl>
                                                        </div>
                                                        <div class="col col-sm-3">
                                                            <dl class="dl-horizontal no-margin">
                                                                <dt>{{{ trans('accountCodes.assignedAmount') }}}:</dt>
                                                                <dd class="@{{ labelClass }}"><strong>@{{ assignedAmount }}</strong></dd>
                                                            </dl>
                                                        </div>
                                                        <div class="col col-sm-3">
                                                            <dl class="dl-horizontal no-margin">
                                                                <dt>{{{ trans('accountCodes.balance') }}}:</dt>
                                                                <dd class="@{{ labelClass }}"><strong>@{{ balance }}</strong></dd>
                                                            </dl>
                                                        </div>
                                                        <div class="col col-sm-3">
                                                            <div class="text-right @{{ labelClass }}">
                                                                <strong>
                                                                    @{{ saveStatusLabel }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if(!$isLocked)
                                            <div class="row mt-6 mb-6">
                                                <div class="col col-sm-10">
                                                </div>
                                                <div class="col col-sm-2">
                                                    <button id="btnSelectItemCodes" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-target="#itemCodeSelectionModal" data-backdrop="static" data-keyboard="false"><i class="fa fa-users"></i> {{ trans('accountCodes.itemCodes') }}</button>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col col-sm-12">
                                                <div id="selectedItemCodesTable"></div>
                                            </div>
                                        </div>
                                    </section>
                                </fieldset>
                            </div>
                        </div>
                        <div class="tab-pane fade in" id="contractorInfoTabContent">
                            <div class="widget-body">
                                <div class="smart-form">
                                    <fieldset>
                                        <div class="row">
                                            <section class="col col-sm-12">
                                                <label class="label">
                                                    <h4>{{ trans('tenders.awardedContractor') }} / {{ trans('tenders.supplier') }} : {{{ $finalSelectedContractor }}}</h4>
                                                </label>
                                            </section>
                                        </div>
                                        <div class="row">
                                            <section class="col col-sm-6">
                                                <label class="label">{{ trans('accountCodes.supplierCode') }}</label>
                                                <label class="input">
                                                    <?php $disabledClass = $isLocked ? 'disabled' : null; ?>
                                                    <input id="supplierCode" type="text" name="name" value="{{{ $supplierCode }}}" id="name" placeholder="{{ trans('accountCodes.supplierCode') }}" {{{ $disabledClass }}}>
                                                </label>
                                            </section>
                                            @if(!$isLocked)
                                            <section class="col col-sm-1" style="padding:0;">
                                                <label class="label">&nbsp;</label>
                                                <label class="input">
                                                    <button id="btnUpdateSupplierCode" type="button" class="btn btn-sm btn-primary"><i class="fa fa-save fa-lg"></i></button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        <div class="row">
                                            <section class="col col-sm-6">
                                                <label class="label">{{ trans('accountCodes.beneficiaryBankAccountNumber') }}</label>
                                                <label class="input">
                                                    <?php $disabledClass = $isLocked ? 'disabled' : null; ?>
                                                    <input type="text" name="beneficiary_bank_account_number" value="{{{ $beneficiaryBankAccountNumber }}}" placeholder="{{ trans('accountCodes.beneficiaryBankAccountNumber') }}" {{{ $disabledClass }}}>
                                                </label>
                                            </section>
                                            @if(!$isLocked)
                                            <section class="col col-sm-1" style="padding:0;">
                                                <label class="label">&nbsp;</label>
                                                <label class="input">
                                                    <button id="btnUpdateBeneficiaryBankAccountNumber" type="button" class="btn btn-sm btn-primary"><i class="fa fa-save fa-lg"></i></button>
                                                </label>
                                            </section>
                                            @endif
                                        </div>
                                        @if($vendorManagementModuleEnabled)
                                            <div class="row">
                                                <section class="col col-sm-6">
                                                    <label class="label">{{ trans('vendorManagement.vendorCategory') }}</label>
                                                    <label class="input">
                                                        @if(!$isLocked)
                                                            {{ Form::select('vendor_category_id', $vendorCategories, Input::old('vendor_category_id') ?? $accountCodeSetting->vendor_category_id, ['class' => 'select2 fill-horizontal']) }}
                                                        @else
                                                            <input type="text" value="{{ $accountCodeSetting->vendorCategory ? $accountCodeSetting->vendorCategory->name : '' }}" disabled>
                                                        @endif
                                                    </label>
                                                </section>
                                                @if(!$isLocked)
                                                <section class="col col-sm-1" style="padding:0;">
                                                    <label class="label">&nbsp;</label>
                                                    <label class="input">
                                                        <button id="btnUpdateVendorCategory" type="button" class="btn btn-sm btn-primary"><i class="fa fa-save fa-lg"></i></button>
                                                    </label>
                                                </section>
                                                @endif
                                            </div>
                                        @endif
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br/>
                </div>
            </div>
        </article>
    </div>
    @include('finance.projects.accountCodes.partials.subsidiariesHierarchyModal')
    @include('finance.projects.accountCodes.partials.itemCodeSelectionModal')
    @include('finance.projects.accountCodes.partials.verifier_remarks_modal')
    @include('finance.projects.accountCodes.partials.verifier_log_modal')
    @include('finance.projects.accountCodes.partials.errorMessagesModal')
    @include('templates.verifiers_required_modal')
@endsection

@section('js')
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        var errorMessages = [];
        $(document).ready(function() {
            var projectCodeSettingsTable = null;
            var projectCodeSettingsTableURL = "{{ route('project.code.settings.get', [$project->id]) }}";

            var subsidiaryHierarchySelectionTable = null;
            var subsidiaryHierarchySelectaionTableURL = "{{ route('subsidiary.hierarchy.get', [$project->id]) }}";
            var currentlySelectedHierarchyLevel = null;

            var selectedItemCodesTable = null;
            var selectedItemCodesTableURL = "{{ route('account.code.settings.saved.item.codes.get', [$project->id]) }}";

            var itemCodeSelectionTable = null;
            var itemCodeSelectionTableURL = "{{ route('account.codes.list.get') }}";

            var subsidiaryCodeEditCheck = function(cell) {
                return cell.getRow().getData().canEditSubsidiaryCode;
            };

            var weightageEditCheck = function(cell) {
                return cell.getRow().getData().canEditApportionment;
            };

            var weightageEditor = function(cell, onRendered, success, cancel, editorParams) {
                var cellValue = cell.getValue();

                var input = document.createElement("input");
                input.style.resize = 'none';
                input.style.padding = "5px";
                input.style.width = "100%";
                input.style.overflow = 'hidden';
                input.style.boxSizing = "border-box";
                input.value = cellValue;

                onRendered(function(){
                    input.focus();
                    input.style.css = "100%";
                });

                function autoGrow(){
                    var scrollHeight = input.scrollHeight;

                    input.style.height =  scrollHeight + 'px';

                    cell.getRow().normalizeHeight();
                }

                function onChange(){
                    if(input.value != cellValue){
                        success(input.value);
                    }else{
                        cancel();
                    }
                }

                input.addEventListener("change", onChange);
                input.addEventListener("blur", onChange);
                input.addEventListener("keydown", function(e){
                    switch(e.keyCode){
                        case 13:
                            onChange();
                            break;
                        case 27:
                            cancel();
                            break;
                    }
                });

                $(input).on('input', function() {
                    autoGrow();
                });
                
                return input;
            };

            var weightageValidator = function(cell, value, parameters) {
                value = value.trim();

                if((value == '') || isNaN(value)) {
                    return false;
                }

                // maximum of 2 decimal places, decimals are optional
                var regex = /^\d+(\.\d{1,2})?$/;

                if(regex.test(value) === false) return false;

                var isWeightageValid = (value >= parameters.min);

                if(isWeightageValid) {
                    return true;
                } else {
                    return false;
                }
            };

            var subsidiaryNameFormatter = function(cell, formatterParams, onRendered) {
                var name = cell.getRow().getData().name;
                cell.getElement().style.whiteSpace = "pre-wrap";
                
                for(var i = 0; i < cell.getRow().getData().level; i++) {
                    name = this.sanitizeHTML(' '.repeat(8) + name);
                }

                return this.emptyToSpace(name);
            };

            var projectCodeSettingsTableColumns = [
                { title: "{{ trans('subsidiaries.name') }}", field: 'name', cssClass:"text-left", minWidth: 280, align: 'left', headerSort: false, formatter: subsidiaryNameFormatter },
                { title: "{{ trans('subsidiaries.subsidiaryCode') }}", field: 'subsidiary_code', width: 150, cssClass:"text-center", align: 'center', headerSort: false, editor: 'input', editable: subsidiaryCodeEditCheck, validator: 'required' },
                { title: "{{ $accountCodeSetting->apportionmentType->name }}", field: 'apportionment', width: 150, cssClass:"text-center", align: 'center', headerSort: false, 
                    editor: weightageEditor, 
                    editable: weightageEditCheck,
                    validator: [{
                        type: weightageValidator,
                        parameters: {
                            min: 0.01,
                        },
                    }],
                },
                { title: "{{ trans('accountCodes.proportion') }} (%)", field: 'proportion', width: 100, cssClass:"text-center", align: 'center', headerSort: false },
            ];

            projectCodeSettingsTable = new Tabulator('#projectCodeSettingsTable', {
                height:400,
                columns: projectCodeSettingsTableColumns,
                layout:"fitColumns",
                ajaxURL: projectCodeSettingsTableURL,
                ajaxConfig: "GET",
                ajaxParams: { projectId: "{{{ $project->id }}}", },
                placeholder:"{{ trans('general.noDataAvailable') }}",
                columnHeaderSortMulti:false,
                rowClick:function(e, row) {
                    projectCodeSettingsTable.deselectRow();
                    row.select();
                },
                cellEditing:function(cell) {
                    projectCodeSettingsTable.deselectRow();
                    cell.getRow().select();
                    cell.getElement().style.backgroundColor = '#FFFCD1';
                },
                cellEditCancelled:function(cell) {
                    projectCodeSettingsTable.deselectRow();
                    cell.getRow().select();
                    cell.getElement().style.backgroundColor = 'transparent';
                },
                cellEdited: function(cell) {
                    var self = this;
                    var row = cell.getRow();
                    var item = row.getData();
                    var field = cell.getField();
                    var value = cell.getValue();
                    var rowIdx = row.getIndex();

                    if(field == 'weightage') {
                        cell.getElement().style.backgroundColor = 'transparent';

                        return
                    }

                    this.modules.ajax.showLoader();

                    var itemData = {
                        id:item.id, 
                        field: field, 
                        val:value,  
                        projectId: "{{{ $project->id }}}", 
                        _token:'{{{csrf_token()}}}'
                    };

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('project.code.settings.update', [$project->id]) }}",
                        data: itemData,
                        dataType: "json",
                        success: function(success) {
                            if(success == true) {
                                self.deselectRow();
                                row.reformat();
                                self.modules.ajax.hideLoader();

                                if(field == 'apportionment') {
                                    self.setData();
                                }
                            }
                        },
                        error: function(){
                            self.deselectRow();
                            self.modules.ajax.hideLoader();
                        }
                    });
                },
            });

            $('#subsidiariesHierarchyModal').on('shown.bs.modal', function() {
                var subsidiaryHierarchySelectaionTableColumns = [
                    { formatter:"rowSelection", align:"center", width: 30, headerSort:false },
                    { title: "{{ trans('subsidiaries.name') }}", field: 'name', cssClass:"text-left", minWidth: 280, align: 'left', headerSort: false, formatter: subsidiaryNameFormatter },
                    { title: "{{ trans('subsidiaries.subsidiaryCode') }}", field: 'identifier', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                ];

                subsidiaryHierarchySelectionTable = new Tabulator('#subsidiariesHierarchySelectionTable', {
                    height:400,
                    columns: subsidiaryHierarchySelectaionTableColumns,
                    layout:"fitColumns",
                    ajaxURL: subsidiaryHierarchySelectaionTableURL,
                    ajaxConfig: "GET",
                    ajaxParams: { subsidiaryId: "{{{ $project->subsidiary->id }}}", },
                    placeholder:"{{ trans('general.noDataAvailable') }}",
                    columnHeaderSortMulti:false,
                    dataLoaded:function(data){
                        var self = this;

                        $.ajax({
                            url: "{{ route('project.code.settings.selected.subsidiaries.get', [$project->id]) }}",
                            method: 'GET',
                            data: {
                                projectId: "{{{ $project->id }}}",
                            },
                            success: function(data) {
                                data.forEach(function(subsidiaryId) {
                                    self.selectRow(subsidiaryId);
                                });
                            }
                        });
                    },
                    rowSelected: function(row) {
                        if(currentlySelectedHierarchyLevel === null) {
                            currentlySelectedHierarchyLevel = row.getData().level;

                            return;
                        }

                        if(row.getData().level != currentlySelectedHierarchyLevel) {
                            this.deselectRow(row.getData().id);
                        }
                    },
                    rowDeselected: function(row) {
                        if(subsidiaryHierarchySelectionTable.getSelectedRows().length == 0) {
                            currentlySelectedHierarchyLevel = null;
                        }
                    },
                });
            });

            $('#subsidiariesHierarchyModal').on('hide.bs.modal', function() {
                subsidiaryHierarchySelectionTable.destroy();
                subsidiaryHierarchySelectionTable = null;
            });

            $('#btnSaveSelectedSubsidiaries').on('click', function(e) {
                var selectedSubsidiaries = subsidiaryHierarchySelectionTable.getSelectedRows();
                var subsidiaryIds = [];
                
                selectedSubsidiaries.forEach(function(el) {
                    subsidiaryIds.push(el.getData().id);
                });

                $.ajax({
                    url: "{{ route('project.code.settings.selected.subsidiaries.save', [$project->id]) }}",
                    method: 'GET',
                    data: {
                        projectId: "{{{ $project->id }}}",
                        subsidiaryIds: subsidiaryIds,
                    },
                    success: function(success) {
                        if(success) {
                            projectCodeSettingsTable.setData();
                            $('#subsidiariesHierarchyModal').modal('hide');
                        }
                    }
                });
            });

            $('#apportionmentTypeFilter').on('change', function(e) {
                var apportionmentTypeId   = $(this).find(":selected").val();
                var apportionmentTypeText = $(this).find(":selected").text();

                $.ajax({
                    url: "{{ route('account.code.settings.apportionment.type.save', [$project->id]) }}",
                    method: 'POST',
                    data: {
                        projectId: "{{{ $project->id }}}",
                        apportionmentTypeId: apportionmentTypeId,
                        _token:'{{{csrf_token()}}}',
                    },
                    success: function(success) {
                        if(success == true) {
                            projectCodeSettingsTable.deleteColumn('apportionment');
                            projectCodeSettingsTable.addColumn({ title: apportionmentTypeText, field: 'apportionment', width: 150, cssClass:"text-center", align: 'center', headerSort: false, editor: 'input', editable: weightageEditCheck }, true, "proportion");
                            projectCodeSettingsTable.setData();
                        }
                    }
                });
            });

            selectedItemCodesTable = new Tabulator('#selectedItemCodesTable', {
                height:400,
                columns: [
                    { title: "{{ trans('accountCodes.accountCode') }}", field: 'accountCode', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                    { title: "{{ trans('accountCodes.description') }}", field: 'description', minWidth:280, cssClass:"text-left", align: 'left', headerSort: false },
                    { title: "{{ trans('accountCodes.amount') }}", field: 'amount', width: 180, cssClass:"text-right", align: 'right', headerSort: false ,
                        editable: @if($isLocked) false @else true @endif,
                        editor:"number",
                        editorParams:{
                            min:0,
                            verticalNavigation:"table"
                        },
                        formatter:"money", formatterParams:{
                            decimal:".",
                            thousand:",",
                            symbolAfter:"p",
                            precision:2,
                        },
                        cellEdited: function(cell){
                            var row   = cell.getRow();
                            var value = cell.getData()['amount'];

                            if(value == '') value = 0;

                            row.update({'amount': Math.round(value*100)/100});

                            row.reformat();

                            itemCodeSettingsAmountInformationVue.saveStatusLabel = "{{ trans('forms.editing') }}";

                            itemCodeSettingsAmountInformationVue.updateDisplay();
                        }
                    },
                    { title: "{{ trans('accountCodes.taxCode') }}", field: 'taxCode', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                ],
                layout:"fitColumns",
                ajaxURL: selectedItemCodesTableURL,
                ajaxConfig: "GET",
                ajaxParams: { projectId: "{{{ $project->id }}}", },
                placeholder:"{{ trans('general.noDataAvailable') }}",
                columnHeaderSortMulti:false,
                dataLoaded: function(){
                    itemCodeSettingsAmountInformationVue.updateDisplay(false);
                },
            });

            $('#itemCodeSelectionModal').on('shown.bs.modal', function() {
                $.ajax({
                    url: "{{ route('account.code.settings.account.group.selected.get', [$project->id]) }}",
                    method: 'GET',
                    data: {
                        projectId: "{{{ $project->id }}}",
                    },
                    success: function(data) {
                        $('#accountGroupFilter').val(data.selectedAccountGroupId);
                        $('#accountGroupFilter').trigger('change');
                    }
                });
            });

            $('#accountGroupFilter').on('change', function(e) {
                if(itemCodeSelectionTable) {
                    itemCodeSelectionTable.destroy();
                    itemCodeSelectionTable = null;
                }

                var accountGroupId = $(this).find(":selected").val();

                itemCodeSelectionTable = new Tabulator('#itemCodeSelectionTable', {
                    height:350,
                    columns: [
                        { formatter:"rowSelection", titleFormatter:"rowSelection", cssClass:"text-center", align:"center", width: 30, headerSort:false },
                        { title: "{{ trans('accountCodes.accountCode') }}", field: 'accountCode', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                        { title: "{{ trans('accountCodes.description') }}", field: 'description', minWidth:280, cssClass:"text-left", align: 'left', headerSort: false },
                        { title: "{{ trans('accountCodes.taxCode') }}", field: 'taxCode', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
                    ],
                    layout:"fitColumns",
                    ajaxURL: itemCodeSelectionTableURL,
                    ajaxConfig: "GET",
                    ajaxParams: { accountGroupId: accountGroupId },
                    placeholder:"{{ trans('general.noDataAvailable') }}",
                    columnHeaderSortMulti:false,
                    dataLoaded: function() {
                        var self = this;

                        $.ajax({
                            url: "{{ route('account.code.settings.account.codes.selected.get', [$project->id]) }}",
                            method: 'GET',
                            data: {
                                projectId: "{{{ $project->id }}}",
                            },
                            success: function(data) {
                                data.selectedAccountCodes.forEach(function(accountCodeId) {
                                    self.selectRow(accountCodeId);
                                });
                            }
                        });
                    },
                    rowSelectionChanged:function(data, rows){
                        $('#btnSaveSelectedAccountCodes').prop('disabled', (data.length == 0));
                    },
                });
            });

            $('#btnSaveSelectedAccountCodes').on('click', function(e) {
                var accountGroupId = $('#accountGroupFilter').find(":selected").val();
                var selectedItemCodes = itemCodeSelectionTable.getSelectedData();
                var itemCodeIds = [];

                selectedItemCodes.forEach(function(itemCode) {
                    itemCodeIds.push(itemCode.id);
                });

                $.ajax({
                    url: "{{ route('account.code.settings.account.codes.save', [$project->id]) }}",
                    method: 'POST',
                    data: {
                        projectId: "{{{ $project->id }}}",
                        accountGroupId: accountGroupId,
                        itemCodeIds: itemCodeIds,
                        _token:'{{{csrf_token()}}}',
                    },
                    success: function(data) {
                        $('#itemCodeSelectionModal').modal('hide');
                        selectedItemCodesTable.setData();
                        itemCodeSettingsAmountInformationVue.updateDisplay(false);
                    }
                });
            });

            $('#btnUpdateSupplierCode').on('click', function(e) {
                var successMessage = "{{ trans('accountCodes.supplierCodeUpdateSuccess') }}";
                var errorMessage   = "{{ trans('accountCodes.supplierCodeUpdateFailed') }}";
                var supplierCode   = $('#supplierCode').val();

                app_progressBar.show ();
                app_progressBar.maxOut();

                $.ajax({
                    url: "{{ route('supplier.code.update', [$project->id]) }}",
                    method: 'POST',
                    data: {
                        projectId: "{{{ $project->id }}}",
                        supplierCode: supplierCode,
                        _token:'{{{csrf_token()}}}',
                    },
                    dataType: "json",
                    success: function(data) {
                        if(data == true) {
                            app_progressBar.hide();

                            $.smallBox({
                                title : "Success",
                                content : "<i class='fa fa-check'></i> <i>" + successMessage + "</i>",
                                color : "#739E73",
                                sound: true,
                                iconSmall : "fa fa-paper-plane",
                                timeout : 5000
                            });
                        }
                        
                    },
                    error: function(request, ajaxOptions, throwError) {
                        app_progressBar.hide();

                        $.smallBox({
                            title : "An error occurred",
                            content : "<i class='fa fa-close'></i> <i>" + errorMessage + "</i>",
                            color : "#C46A69",
                            sound: true,
                            iconSmall : "fa fa-exclamation-triangle shake animated"
                        });
                    },
                });
            });

            $('#btnUpdateBeneficiaryBankAccountNumber').on('click', function(e) {
                var successMessage   = "{{ trans('forms.saved') }}";
                var errorMessage     = "{{ trans('forms.submissionFailed') }}";
                var value = $('#contractorInfoTabContent [name=beneficiary_bank_account_number]').val();

                app_progressBar.show ();
                app_progressBar.maxOut();

                $.ajax({
                    url: "{{ route('project.accountCodeSetting.beneficiaryBankAccountNumber.update', [$project->id]) }}",
                    method: 'POST',
                    data: {
                        beneficiary_bank_account_number: value,
                        _token:'{{{csrf_token()}}}',
                    },
                    dataType: "json",
                    success: function(data) {
                        if(data.success) {
                            app_progressBar.hide();

                            $.smallBox({
                                title : "Success",
                                content : "<i class='fa fa-check'></i> <i>" + successMessage + "</i>",
                                color : "#739E73",
                                sound: true,
                                iconSmall : "fa fa-paper-plane",
                                timeout : 5000
                            });
                        }
                        else{
                            app_progressBar.hide();

                            $.smallBox({
                                title : "An error occurred",
                                content : "<i class='fa fa-close'></i> <i>" + data.errorMsg + "</i>",
                                color : "#C46A69",
                                sound: true,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });
                        }
                    },
                    error: function(request, ajaxOptions, throwError) {
                        app_progressBar.hide();

                        $.smallBox({
                            title : "An error occurred",
                            content : "<i class='fa fa-close'></i> <i>" + errorMessage + "</i>",
                            color : "#C46A69",
                            sound: true,
                            iconSmall : "fa fa-exclamation-triangle shake animated"
                        });
                    },
                });
            });

            @if($vendorManagementModuleEnabled)
                $('#btnUpdateVendorCategory').on('click', function(e) {
                    var successMessage   = "{{ trans('forms.saved') }}";
                    var errorMessage     = "{{ trans('forms.submissionFailed') }}";
                    var vendorCategoryId = $('#contractorInfoTabContent [name=vendor_category_id]').val();

                    app_progressBar.show ();
                    app_progressBar.maxOut();

                    $.ajax({
                        url: "{{ route('project.accountCodeSetting.vendorCategory.update', [$project->id]) }}",
                        method: 'POST',
                        data: {
                            vendor_category_id: vendorCategoryId,
                            _token:'{{{csrf_token()}}}',
                        },
                        dataType: "json",
                        success: function(data) {
                            if(data.success) {
                                app_progressBar.hide();

                                $.smallBox({
                                    title : "Success",
                                    content : "<i class='fa fa-check'></i> <i>" + successMessage + "</i>",
                                    color : "#739E73",
                                    sound: true,
                                    iconSmall : "fa fa-paper-plane",
                                    timeout : 5000
                                });
                            }
                            else{
                                app_progressBar.hide();

                                $.smallBox({
                                    title : "An error occurred",
                                    content : "<i class='fa fa-close'></i> <i>" + data.errorMsg + "</i>",
                                    color : "#C46A69",
                                    sound: true,
                                    iconSmall : "fa fa-exclamation-triangle shake animated"
                                });
                            }
                        },
                        error: function(request, ajaxOptions, throwError) {
                            app_progressBar.hide();

                            $.smallBox({
                                title : "An error occurred",
                                content : "<i class='fa fa-close'></i> <i>" + errorMessage + "</i>",
                                color : "#C46A69",
                                sound: true,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });
                        },
                    });
                });
            @endif

            $('#verifierForm button[name=approve], #verifierForm button[name=reject]').on('click', function(e) {
				e.preventDefault();

				if(this.name == 'reject') {
					$('#accountCodeSettingVerifierRejectRemarksModal').modal('show');
				}

				if(this.name == 'approve') {
					$('#accountCodeSettingVerifierApproveRemarksModal').modal('show');           
                } 
			});

            $('button#verifier_approve_account_code_setting-submit_btn, button#verifier_reject_account_code_setting-submit_btn').on('click', function(e) {
				e.preventDefault();

				var remarksId;
				            
				switch(this.id) {
					case 'verifier_approve_account_code_setting-submit_btn':
						var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
						$('#verifierForm').append(input);
						remarksId = 'approve_verifier_remarks';
						break;
					case 'verifier_reject_account_code_setting-submit_btn':
						remarksId = 'reject_verifier_remarks';
						break;
				}

				if($('#'+remarksId)){
					$('#verifierForm').append($("<input>")
					.attr("type", "hidden")
					.attr("name", "verifier_remarks").val($('#'+remarksId).val()));
				}

				$('#verifierForm').submit();
			});

            $('#btnSubmit').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $.ajax({
                    url: "{{ route('account.code.settings.approval.submit.check', [$project->id]) }}",
                    method: 'POST',
                    data: {
                        projectId: "{{{ $project->id }}}",
                        _token:'{{{csrf_token()}}}',
                    },
                    dataType: "json",
                    success: function(data) {
                        app_progressBar.hide();
                        $('#errorMessagesModal ol').empty();

                        errorMessages = data.errorMessages;

                        if(data.errorMessages.length > 0) {
                            data.errorMessages.forEach(function(errorMessage) {
                                $('#errorMessagesModal ol').append('<li>' + errorMessage + '</li>');
                            });

                            $('#errorMessagesModal').modal('show');
                        } else {
                            $('#accountCodeSettingForm').submit();
                        }
                    },
                    error: function(request, ajaxOptions, throwError) {
                        app_progressBar.hide();
                    },
                });
            });

            $('#btnViewLogs').on('click', function(e) {
				e.preventDefault();
				$('#accountCodeSettingVerifierLogModal').modal('show');
			});

            $('#accountCodeSettingForm').on('submit', function(e) {
                if(noVerifier(e)) {
                    $('#verifiersRequiredModal').modal('show');

                    return false;
                }
            });

            var itemCodeSettingsAmountInformationVue = new Vue({
                el: '#item-code-settings-amount-information',
                data: {
                    contractSum: {{ $contractSum }},
                    assignedAmount: 0,
                    balance: 0,
                    saveStatusLabel: "",
                    labelClass: "text-success",
                },
                methods: {
                    updateDisplay: function(updateAmount = true){
                        this.labelClass = "text-warning";

                        var total = 0;
                        selectedItemCodesTable.getData().forEach(function(row){
                            return total += parseFloat(row['amount']);
                        });

                        var balance = this.contractSum - total;

                        itemCodeSettingsAmountInformationVue.assignedAmount = total.toLocaleString('en-US', {minimumFractionDigits: 2});
                        itemCodeSettingsAmountInformationVue.balance = balance.toLocaleString('en-US', {minimumFractionDigits: 2});

                        if(balance === 0){
                            this.labelClass = "text-success";
                            if(updateAmount) this.updateItemCodeSettingsAmounts();
                        }
                    },
                    updateItemCodeSettingsAmounts: function()
                    {
                        var self = this;
                        var data = [];

                        selectedItemCodesTable.getData().forEach((row) => {
                            data.push({
                                id: row.id,
                                amount: row.amount,
                            });
                        });

                        $.ajax({
                            url: "{{{ route('account.code.settings.itemCodeSettings.amount.save', $project->id) }}}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                item_code_setting_amounts: data,
                            },
                            success: function (data) {
                                if (data['success']) {
                                    self.saveStatusLabel = "";
                                    SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                                }
                                else
                                {
                                    SmallErrorBox.refreshAndRetry();
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                SmallErrorBox.refreshAndRetry();
                            }
                        });
                    }
                }
            });
        });

        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();

            return !input.some(function(element){
                return (element.value > 0);
            });
        }
    </script>
@endsection