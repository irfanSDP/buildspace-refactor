<script>
    var vendorsByVendorWorkSubCategoryTable = new Tabulator("#vendorsByVendorWorkSubCategoryTable", {
        layout:"fitColumns",
        height: 350,
        ajaxConfig: "GET",
        placeholder: "{{ trans('general.noRecordsFound') }}",
        pagination: 'local',
        columns:[
            { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
            {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field: 'vendor_work_subcategory', align:"left", headerSort:false, headerFilter: "input", headerSort:false },
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

    renderVendorWorkSubCategoryTable(formInputs, 'vendorsByVendorWorkSubCategory')

    function renderVendorWorkSubCategoryTable(data, identifier) {
        data.identifier = identifier;

        vendorsByVendorWorkSubCategoryTable.setData("{{ route('vendorManagement.dashboard.vendorStatistics') }}", data);
    }
</script>