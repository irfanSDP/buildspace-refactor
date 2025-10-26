@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
    <li>{{{ trans('general.consultantManagementReports') }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-book"></i> {{{ trans('general.consultantManagementReports') }}}
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 mb-4">
        {{ Form::open(['route' => ['consultant.management.reports.export.excel'], 'id'=>'export_to_excel-form', 'target'=>'_blank', 'method' => 'post']) }}
        <button type="submit" id="export_to_excel-btn" class="btn btn-success btn-md pull-right header-btn">
            <i class="fa fa-file-excel"></i> {{{ trans('general.exportToExcel') }}}
        </button>
        {{ Form::close() }}
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('general.consultantManagementReports') }}</h2>
            </header>
            <div class="widget-body">
                @include('consultant_management.reports.partials.advanced_search')
                <div id="main-table"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#global_search-toggle-btn').on('click', function(e){
        if($('#global_search-content:visible').length){
            $(this).html('<i class="far fa-eye"></i> {{ trans("general.show") }}');
            $('#global_search-content').hide(500);
        }else{
            $(this).html('<i class="far fa-eye-slash"></i> {{ trans("general.hide") }}');
            $('#global_search-content').show(500);
        }
    });

    $('#advanced_search-reset-btn').on('click', function(e){
        $("#criteria_reference_no").prop("checked", true);
        $('#criteria_search_str-input').val('');
        $("#criteria_consultant_name").prop("checked", false);
        $("#criteria_vendor_category").prop("checked", false);
        $("#criteria_subsidiary_name").prop("checked", false);
        $("#roc_approved_date_from-input").val("");
        $("#roc_approved_date_to-input").val("");
        $("#loa_date_from-input").val("");
        $("#loa_date_to-input").val("");
    });
        
    $('.datetimepicker').datepicker({
        dateFormat : 'dd-mm-yy',
        prevText : '<i class="fa fa-chevron-left"></i>',
        nextText : '<i class="fa fa-chevron-right"></i>',
        onSelect: function(){
            var selected = $(this).val();
            $(this).attr('value', selected);
        }
    });

    var formData = $('#advanced_search-form').serializeArray(); // store json string
    var ajaxParams={};
    for(var i=0;i < formData.length;i++)
    {
        ajaxParams[formData[i].name] = formData[i].value;
    }
    var mainTable = new Tabulator('#main-table', {
        height:580,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.reports.ajax.list') }}",
        ajaxParams: ajaxParams,
        ajaxConfig: "POST",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.referenceNo') }}", field:"reference_no", width:180, hozAlign:'left', headerSort:false, formatter:'textarea'},
            {title:"{{ trans('vendorManagement.consultant') }}", field:"company_name", minWidth: 420, hozAlign:"left", headerSort:false, formatter:'textarea'},
            {title:"{{ trans('general.consultantCategories') }}", field:"vendor_category_name", width:260, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.subsidiaryTownship') }}", field:"subsidiary_name", width:350, hozAlign:'left', headerSort:false, formatter:'textarea'},
            {title:"ROC Approved Date", field:"roc_approved_date", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.totalConstructionCost') }}", field:"construction_cost", width:180, hozAlign:'right', cssClass:"text-right text-middle", headerSort:false},
            {title:"{{ trans('general.totalLandscapeCost') }}", field:"landscape_cost", width:180, hozAlign:'right', cssClass:"text-right text-middle", headerSort:false},
            {title:"{{ trans('tenders.budget') }}", field:"budget", width:180, hozAlign:'right', cssClass:"text-right text-middle", headerSort:false},
            {title:"Fee", field:"fee_amount", width:180, hozAlign:'right', cssClass:"text-right text-middle", headerSort:false},
            {title:"Fee % ", field:"fee_percentage", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"Budget vs Fee", field:"budget_vs_fee", width:180, hozAlign:'right', cssClass:"text-right text-middle", headerSort:false, formatter:function(cell){
                const data = cell.getData();
                if(parseFloat(data.budget_vs_fee) > 0){
                    return '<label class="text-success">'+$.number(data.budget_vs_fee, 2, '.', ',')+'</label>';
                }
                else{
                    return '<label class="text-danger">'+$.number(data.budget_vs_fee, 2, '.', ',')+'</label>';
                }
            }},
            {title:"RFP Status", field: 'status', cssClass:"text-center text-middle", width: 140, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        var txt;
                        switch(rowData.status){
                            case "{{ trans('general.awarded') }}":
                                txt = ' <b class="badge bg-color-green inbox-badge">'+rowData.status+'</b>';
                                break;
                            case "{{ trans('verifiers.approved') }}":
                                txt = '<b class="badge bg-color-purple inbox-badge">'+rowData.status+'</b>';
                                break;
                            case "{{ trans('general.callingRFP') }}":
                                txt = '<b class="badge bg-color-yellow inbox-badge">'+rowData.status+'</b>';
                                break;
                            default:
                                txt = rowData.status;
                        }
                        return txt;
                    }
                }]
            }},
            {title:"LOA Date", field:"loa_date", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},

        ],
    });

    $("#advanced_search-form").on('submit', function(e){
        e.preventDefault();

        var formData = $('#advanced_search-form').serializeArray();
        var ajaxParams={};
        for(var i=0;i < formData.length;i++)
        {
            ajaxParams[formData[i].name] = formData[i].value;
        }
        app_progressBar.toggle();
        mainTable.setData("{{ route('consultant.management.reports.ajax.list') }}", ajaxParams).then(function(){
            app_progressBar.maxOut();
            app_progressBar.toggle();
        });
    });

    $('#export_to_excel-btn').on('click', function(e){
        e.preventDefault();

        var formData = $('#advanced_search-form').serializeArray();
        for(var i=0;i < formData.length;i++)
        {
            if(formData[i].name != '_token'){
                $("<input />").attr("type", "hidden").attr('name', formData[i].name).attr('value', formData[i].value).appendTo($(this.form));
            }
        }

        $($(this.form)).submit();
    });
});
</script>
@endsection

