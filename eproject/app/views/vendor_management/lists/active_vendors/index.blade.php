@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ trans('vendorManagement.activeVendorList') }}</li>
    </ol>
@endsection

@section('content')
<?php use PCK\ContractGroupCategory\ContractGroupCategory; ?>
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.activeVendorList') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            <div class="dropdown {{{ $classes ?? 'pull-right' }}}">
                <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
                <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
                    <li class="dropdown-submenu left">
                        <a tabindex="-1" href="javascript:void(0);" class="text-center">{{ trans('general.summary') }}</a>
                        <ul class="dropdown-menu">
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-group-summary">
                                    {{ trans('vendorManagement.vendorGroupSummary') }}
                                </button>
                            </li>
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-category-summary">
                                    {{ trans('vendorManagement.vendorCategorySummary') }}
                                </button>
                            </li>
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-work-category-summary">
                                    {{ trans('vendorManagement.vendorWorkCategorySummary') }}
                                </button>
                            </li>
                        </ul>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <button type="button" class="btn btn-block btn-primary btn-mg header-btn" data-action="view-scores">
                            {{ trans('vendorManagement.scores') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-block btn-primary btn-mg header-btn" data-action="view-scores-with-sub-work-categories">
                            {{ trans('vendorManagement.categories') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('vendorManagement.activeVendorList') }}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <fieldset>
                        <section>
                            <div class="col col-xs-6">
                                <label class="select">{{ trans('vendorManagement.vendorWorkCategoryQualification') }}</label>
                                <select id="vendorWorkCategoryQualificationSelect" class="select2" data-action="filterAVL">
                                    <option value="">{{ trans('general.selectAnOption') }}</option>
                                    <option value="yes">{{ trans('vendorManagement.qualified') }}</option>
                                    <option value="no">{{ trans('vendorManagement.unqualified') }}</option>
                                </select>
                            </div>
                            <div class="col col-xs-6">
                                <label class="select">{{ trans('vendorManagement.vendorActiveStatus') }}</label>
                                <select id="vendorActiveStatusSelect" class="select2" data-action="filterAVL">
                                    <option value="">{{ trans('general.selectAnOption') }}</option>
                                    <option value="active">{{ trans('general.active') }}</option>
                                    <option value="expired">{{ trans('general.expired') }}</option>
                                </select>
                            </div>
                        </section>
                    </fieldset>
                    <br>
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="vendors-breakdown-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="vendors-breakdown-modal-title">
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div id="vendors-breakdown-table"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
            </div>
        </div>
    </div>
</div>
@include('module_parameters.email_notification_settings.partials.modifiable_contents_modal', [
    'title'   => trans('vendorManagement.updateReminder'),
    'modalId' => 'modifiableContentsModal',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-group-summary-modal',
    'title'            => trans('vendorManagement.vendorGroupSummary'),
    'tableId'          => 'vendor-group-summary-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-category-summary-modal',
    'title'            => trans('vendorManagement.vendorCategorySummary'),
    'tableId'          => 'vendor-category-summary-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-work-category-summary-modal',
    'title'            => trans('vendorManagement.vendorWorkCategorySummary'),
    'tableId'          => 'vendor-work-category-summary-table',
    'modalDialogClass' => 'modal-xl',
])

<div class="modal fade" id="scores-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.scores') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="scores-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-action="export-scores"><i class="fa fa-download"></i> {{ trans('general.download') }}</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="scores-with-sub-work-categories-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.categories') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="scores-with-sub-work-categories-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-action="export-scores-with-sub-work-categories"><i class="fa fa-download"></i> {{ trans('general.download') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            <?php $canViewVendorProfile = $currentUser->canViewVendorProfile(); ?>
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorManagement.activeVendorList.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true, frozen:true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:view']+'">'+cell.getData()['name']+'</a>';
                        @else
                            return cell.getData()['name'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", width:200, hozAlign:'left', headerFilter: true, headerSort:false, editor:"select", headerFilterParams:{{ json_encode($externalVendorGroupsFilterOptions) }} },
                    {title:"{{ trans('vendorManagement.expiryDate') }}", field:"expiry_date", width: 150, cssClass:"text-center text-middle", headerSort:true},
                    {title:"{{ trans('companies.cidbGrade') }}", field:"cidbGrade", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter: 'select', headerFilterParams: {{ json_encode($cidbGradeFilterOptions) }} },
                    {title:"{{ trans('companies.bimInformation') }}", field:"bimInformation", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter: 'select', headerFilterParams: {{ json_encode($bimLevelFilterOptions) }} },
                    {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width: 150, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($statusFilterOptions) }}},
                    {title:"{{ trans('vendorManagement.submissionType') }}", field:"submission_type", width: 150, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($submissionTypeFilterOptions) }}},
                    {title:"{{ trans('tags.tags') }}", field:"tags", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var tagsArray = cell.getData()['tagsArray'];
                        var output = [];
                        for(var i in tagsArray){
                            output.push('<span class="label label-success">'+tagsArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategories') }}", field:"vendor_work_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorWorkCategoriesArray = cell.getData()['vendorWorkCategoriesArray'];
                        var output = [
                            '<span data-id="'+cell.getData()['id']+'" data-action="show-breakdown" class="label label-default text-white">{{ trans("general.view") }}</span>',
                            '<span data-id="'+cell.getData()['id']+'" data-action="show-breakdown" class="label label-default text-white">'+cell.getData()['vendorWorkCategoriesArray'].length+'</span>',
                        ];
                        for(var i in vendorWorkCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorWorkCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-route':'route:reminder'},
                                    attributes: {'class': 'btn btn-xs btn-warning text-white', 'data-action':'send-renewal-reminder'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {title: "{{ trans('vendorManagement.sendRenewalReminder') }}", class: 'fa fa-envelope'}
                                    }
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                },
                            },{
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-route':'route:update-reminder'},
                                    attributes: {'class': 'btn btn-xs btn-primary text-white', 'data-action':'send-update-reminder'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {title: "{{ trans('vendorManagement.sendUpdateReminder') }}", class: 'fa fa-envelope'}
                                    }
                                }
                            }
                        ]
                    }}
                ],
            });

            $('#main-table').on('click', '[data-action=send-renewal-reminder]', function(){
                $.post($(this).data('route'), {_token:_csrf_token})
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.sentRenewalReminder')}}");
                    }
                })
                .fail(function(){
                    SmallErrorBox.refreshAndRetry();
                });
            });

            $('[data-action="filterAVL"]').on('change', function(e) {
                e.preventDefault();

                var json = {
                    vendor_work_category_qualification : $('#vendorWorkCategoryQualificationSelect').val(),
                    vendor_active_status : $('#vendorActiveStatusSelect').val(),
                };

                mainTable.setData("{{ route('vendorManagement.activeVendorList.ajax.list') }}", json);
            });

            $(document).on('click', '[data-action="send-update-reminder"]', function() {
                $('#modifiableContentsModal [data-action="saveContent"]').data('url', $(this).data('route'));
                $('#modifiableContentsModal [data-action="saveContent"]').prop('disabled', true);
                $('#modifiableContentsModal').modal('show');
            });

            $('#modifiableContentsModal').on('show.bs.modal', function() {
				$('#email_contents').removeAttr('style');
				$('#email_contents').css('height', '200px');
				$('#email_contents').css('overflow-y', 'scroll');
                $('#email_contents').val('');
			});

			$('#modifiableContentsModal').on('shown.bs.modal', function() {
				$('#email_contents').focus();
			});

            $(document).on('input propertychange', '#email_contents', function() {
                var contents = $(this).val().trim();

                var disableFlag = (contents.length > 0) ? false : true;

                $('#modifiableContentsModal [data-action="saveContent"]').prop('disabled', disableFlag);
            });

            $('#modifiableContentsModal [data-action="saveContent"]').on('click', function(e) {
				e.preventDefault();

				var url 	 = $(this).data('url');
				var contents = $('#email_contents').val();

                $.post(url, {
                    contents: DOMPurify.sanitize(contents.trim()),
                    _token:_csrf_token
                })
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.sentUpdateReminder')}}");

                        $('#modifiableContentsModal').modal('hide');
                    }
                })
                .fail(function(){
                    SmallErrorBox.refreshAndRetry();
                });
			});

            var breakdownTable = new Tabulator('#vendors-breakdown-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"name", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vpeScore') }}", field:"score", width: 120, cssClass:"text-center text-middle"},
                    {title:"{{ trans('vendorManagement.preqScore') }}", field:"pre_qualification_score", width: 120, cssClass:"text-center text-middle"},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"pre_qualification_grade", width: 250, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:[]},
                    {title:"{{ trans('vendorManagement.qualified') }}", field:"is_qualified", width: 120, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($isQualifiedFilterOptions) }}}
                ],
            });

            $('#main-table').on('click', '[data-action=show-breakdown]', function(){
                var row = mainTable.getRow($(this).data('id'));
                $('#vendors-breakdown-modal-title').html(row.getData()['name']);
                breakdownTable.updateColumnDefinition("pre_qualification_grade", {headerFilterParams:{values:row.getData()['preq_grade_filter_options']}});
                breakdownTable.setData(row.getData()['route:breakdown']);
                $('#vendors-breakdown-modal').modal('show');
            });

            var contractGroupCategorySummaryTable = new Tabulator('#vendor-group-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-group-summary]').on('click', function(){
                contractGroupCategorySummaryTable.setData("{{ route('vendorManagement.activeVendorList.summary.contractGroupCategories') }}");
                $('#vendor-group-summary-modal').modal('show');
            });

            var vendorCategorySummaryTable = new Tabulator('#vendor-category-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width: 200, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-category-summary]').on('click', function(){
                vendorCategorySummaryTable.setData("{{ route('vendorManagement.activeVendorList.summary.vendorCategories') }}");
                $('#vendor-category-summary-modal').modal('show');
            });

            var vendorWorkCategorySummaryTable = new Tabulator('#vendor-work-category-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var arr = cell.getData()['contract_group_categories'];
                        var output = [];
                        for(var i in arr){
                            output.push('<span class="label label-primary text-white">'+arr[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorCategoriesArray = cell.getData()['vendor_categories'];
                        var output = [];
                        for(var i in vendorCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-work-category-summary]').on('click', function(){
                vendorWorkCategorySummaryTable.setData("{{ route('vendorManagement.activeVendorList.summary.vendorWorkCategories') }}");
                $('#vendor-work-category-summary-modal').modal('show');
            });

            var scoresTable = new Tabulator('#scores-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:view']+'">'+cell.getData()['company']+'</a>';
                        @else
                            return cell.getData()['company'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width:200, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_work_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]}
                ],
            });

            $('[data-action=view-scores]').on('click', function(){
                scoresTable.setData("{{ route('vendorManagement.activeVendorList.scores.list') }}");
                $('#scores-modal').modal('show');
            });

            var scoresWithSubWorkCategoriesTable = new Tabulator('#scores-with-sub-work-categories-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:view']+'">'+cell.getData()['company']+'</a>';
                        @else
                            return cell.getData()['company'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width:200, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_work_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategories') }}", field:"vendor_sub_work_categories", width:250, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                ],
            });

            $('[data-action=view-scores-with-sub-work-categories]').on('click', function() {
                scoresWithSubWorkCategoriesTable.setData("{{ route('vendorManagement.activeVendorList.scores.subWorkCategories.list') }}");
                $('#scores-with-sub-work-categories-modal').modal('show');
            });

            $('[data-action=export-scores]').on('click', function(){
                var filters = scoresTable.getHeaderFilters();
                var parameters = [];
                var url = "{{ route('vendorManagement.activeVendorList.scores.export') }}";

                for (var i=0;i< filters.length;i++){
                    if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                        parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                        parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));
                    }
                }

                if(parameters.length){
                    url += '?'+parameters.join('&');
                }

                window.open(url, '_blank');
            });

            $('[data-action=export-scores-with-sub-work-categories]').on('click', function(){
                var filters = scoresWithSubWorkCategoriesTable.getHeaderFilters();
                var parameters = [];
                var url = "{{ route('vendorManagement.activeVendorList.scores.subWorkCategories.export') }}";

                for (var i=0;i< filters.length;i++){
                    if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                        parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                        parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));
                    }
                }

                if(parameters.length){
                    url += '?'+parameters.join('&');
                }

                window.open(url, '_blank');
            });
        });
    </script>
@endsection