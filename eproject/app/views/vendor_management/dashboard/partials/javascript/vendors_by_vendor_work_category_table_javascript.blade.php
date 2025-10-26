<script>
    var vendorsByVendorWorkCategoryTable = new Tabulator("#vendorsByVendorWorkCategoryTable", {
        layout:"fitColumns",
        height: 350,
        ajaxConfig: "GET",
        placeholder: "{{ trans('general.noRecordsFound') }}",
        pagination: 'local',
        columns:[
            { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field: 'vendor_work_category', align:"left", headerSort:false, headerFilter: "input", headerSort:false },
            {title:"{{ trans('general.count') }}", field: 'vendor_count', hozAlign:'center', cssClass:"text-center text-middle", width: 100, headerSort:false },
        ],
    });

    var formInputs = {
        country: '',
        state: '',
        vendor_group: '',
        vendor_category: '',
        vendor_work_category: '',
        vendor_work_subcategory: '',
        registration_status: '',
        company_status: '',
        preq_grade: '',
        vpe_grade: '',
    };

    renderVendorsByVendorWorkCategoryTable(formInputs, 'vendorsByVendorWorkCategory');

    function renderVendorsByVendorWorkCategoryTable(data, identifier) {
        data.identifier = identifier;

        vendorsByVendorWorkCategoryTable.setData("{{ route('vendorManagement.dashboard.vendorStatistics') }}", data);
    }
</script>