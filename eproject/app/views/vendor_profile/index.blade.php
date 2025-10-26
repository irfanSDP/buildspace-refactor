@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorProfile.vendorProfiles') }}}</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorProfile.vendorProfiles') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            @include('vendor_profile.partials.index_action_menu')
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('vendorProfile.vendorProfiles') }}</h2>
            </header>
            <div class="widget-body">
                
                @include('vendor_profile.partials.advanced_search')

                <hr class="simple"/>

                <div class="smart-form">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('tags.searchByTag') }}}</label>
                            <label class="fill-horizontal">
                                <select name="tags" class="form-control" multiple="multiple" id="tag-filter" style="width:100%;"></select>
                            </label>
                        </section>
                    </div>
                </div>
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
    $('.datetimepicker').datepicker({
        dateFormat : 'dd-mm-yy',
        prevText : '<i class="fa fa-chevron-left"></i>',
        nextText : '<i class="fa fa-chevron-right"></i>',
        onSelect: function(){
            var selected = $(this).val();
            $(this).attr('value', selected);
        }
    });

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
        $("#criteria_company_name").prop("checked", true);
        $('#criteria_search_str-input').val('');
        $("#vendor_status_active").prop("checked", false);
        $("#vendor_status_watchlist").prop("checked", false);
        $("#vendor_status_expired").prop("checked", false);
        $("#vendor_status_deactivated").prop("checked", false);
        $('#contract_group_category_id-select').val('').trigger('change');
        $("#bumi_status_bumiputera").prop("checked", true);
        $("#activation_date_from-input").val("");
        $("#activation_date_to-input").val("");
        $("#expiry_date_from-input").val("");
        $("#expiry_date_to-input").val("");
        $("#deactivation_date_from-input").val("");
        $("#deactivation_date_to-input").val("");
    });

    var tagFilterData = [];
    @foreach($allTags as $tag)
        tagFilterData.push({
            id: {{ $tag->id }},
            text: "{{{ $tag->name }}}"
        });
    @endforeach

    $("select#tag-filter").select2({
        data: tagFilterData
    });

    var mainTable = new Tabulator('#main-table', {
        height:580,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('vendorProfile.ajax.list') }}",
        ajaxConfig: "POST",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        ajaxURLGenerator:function(url, config, params){
            var formParams = $("#advanced_search-form").serializeArray();
            formParams = $.param(formParams);
            return url + "?"+formParams;
        },
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
            {title:"{{ trans('vendorProfile.vendor') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true, frozen:true, formatter:function(cell) {
                if(cell.getData()['route:show']){
                    return '<a href="'+cell.getData()['route:show']+'">'+cell.getData()['name']+'</a>';
                }
                else{
                    return cell.getData()['name'];
                }
            }},
            {title:"{{ trans('vendorManagement.vendorStatus') }}", field:"vendor_status_text", width: 180, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($vendorStatusTextFilterOptions) }}, formatter:function(cell){
                var labelClass;
                switch(cell.getData()['vendor_status']){
                    case {{ \PCK\Companies\Company::STATUS_DEACTIVATED }}:
                        labelClass = 'label-danger';
                        break;
                    case {{ \PCK\Companies\Company::STATUS_EXPIRED }}:
                        labelClass = 'label-warning';
                        break;
                    case {{ \PCK\Companies\Company::STATUS_ACTIVE }}:
                        labelClass = 'label-success';
                        break;
                    default:
                        labelClass = 'label-default';
                }
                return '<span class="label '+labelClass+'">'+cell.getData()['vendor_status_text']+'</span>';
            }},
            {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", width:200, hozAlign:'left', headerFilter: true, headerSort:false, editor:"select", headerFilterParams:{{ json_encode($externalVendorGroupsFilterOptions) }} },
            {title:"{{ trans('vendorManagement.activationDate') }}", field:"activationDate", width:150, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.expiryDate') }}", field:"expiryDate", width:150, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.cidbGrade') }}", field:"cidbGrade", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter: 'select', headerFilterParams: {{ json_encode($cidbGradeFilterOptions) }} },
            {title:"{{ trans('companies.bimInformation') }}", field:"bimInformation", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter: 'select', headerFilterParams: {{ json_encode($bimLevelFilterOptions) }} },
            {title:"{{ trans('companies.country') }}", field:"country", width:180, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.state') }}", field:"state", width:120, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.status') }}", field:"status", width: 150, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($statusFilterOptions) }}},
            {title:"{{ trans('vendorManagement.submissionType') }}", field:"submission_type", width: 150, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($submissionTypeFilterOptions) }}, formatter:function(cell){
                var labelClass;
                switch(cell.getData()['submission_type']){
                    case {{ \PCK\VendorRegistration\VendorRegistration::SUBMISSION_TYPE_NEW }}:
                        labelClass = 'label-success';
                        break;
                    case {{ \PCK\VendorRegistration\VendorRegistration::SUBMISSION_TYPE_RENEWAL }}:
                        labelClass = 'label-primary';
                        break;
                    case {{ \PCK\VendorRegistration\VendorRegistration::SUBMISSION_TYPE_UPDATE }}:
                        labelClass = 'label-warning';
                        break;
                    default:
                        labelClass = 'label-default';
                }
                return '<span class="label '+labelClass+'">'+cell.getData()['submission_type_text']+'</span>';
            }},
            @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
            {title:"{{ trans('vendorManagement.avgPreqScore') }}", field:"pre_qualification_score", width: 130, cssClass:"text-center text-middle", headerSort:true, editable: false},
            {title:"{{ trans('vendorManagement.grade') }}", field:"pre_qualification_grade", width: 250, cssClass:"text-center text-middle", headerSort:true, editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($gradeFilterOptions) }}},
            @endif
            {title:"{{ trans('tags.tags') }}", field:"tags", width: 280, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                var tagsArray = cell.getData()['tagsArray'];
                var output = [];
                for(var i in tagsArray){
                    output.push('<span class="label label-success">'+tagsArray[i]+'</span>');
                }
                return output.join('&nbsp;', output);
            }}
        ],
    });

    $('select#tag-filter').on('select2:select', function (e) {
        var data = e.params.data;
        mainTable.addFilter("tids", "=", data['id']);
    });

    $('select#tag-filter').on('select2:unselect', function (e) {
        var data = e.params.data;
        mainTable.removeFilter("tids", "=", data['id']);
    });

    $("#advanced_search-form").on('submit', function(e){
        e.preventDefault();
        app_progressBar.toggle();
        mainTable.setData().then(function(){
            app_progressBar.maxOut();
            app_progressBar.toggle();
        });
    });

    $('[data-action="vendor-profile-export"]').on('click', function(e){
        var filters = mainTable.getHeaderFilters();
        var parameters = [];
        var url = $(this).data('url');

        var formParams = $("#advanced_search-form").serializeArray();
        formParams = $.param(formParams);
        
        url += '?'+formParams;

        for (var i=0;i< filters.length;i++){
            if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));

            }
        }

        if(parameters.length){
            url += '&'+parameters.join('&');
        }

        window.open(url, '_blank');
    });
});
</script>
@endsection