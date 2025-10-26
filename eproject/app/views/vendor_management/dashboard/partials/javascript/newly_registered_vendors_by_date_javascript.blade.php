<script>
    var newlyRegisteredVendorsByDateTable = new Tabulator('#registration-statistics-newly-registered-vendors-by-date', {
            columnHeaderVertAlign:"middle",
            ajaxURL: "{{ route('vendorManagement.dashboard.registrationStatistics.newlyRegisteredVendorsByDate') }}",
            ajaxConfig: "GET",
            layout:"fitColumns",
            height: 400,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            pagination: 'remote',
            ajaxFiltering: true,
            columns:[
                {title:"{{ trans('general.no') }}", field: 'counter', width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.day') }}", field: 'day', width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('general.month') }}", field: 'month', width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('general.year') }}", field: 'year', width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", hozAlign: 'center', cssClass: 'text-middle text-center', headerFilter:true},
                {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category", hozAlign: 'center', cssClass: 'text-middle text-center', headerFilter:true},
                {title:"{{ trans('general.count') }}", field: 'total', width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.view') }}", width: 100, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter:function(cell, formatterParams, onRendered) {
                    var listVendorsButton = document.createElement('a');
                    listVendorsButton.dataset['company_ids'] = cell.getRow().getData().company_ids;
                    listVendorsButton.dataset.toggle = 'tooltip';
                    listVendorsButton.dataset.action = 'listVendors';
                    listVendorsButton.title = "{{ trans('general.view') }}";
                    listVendorsButton.className = 'btn btn-xs btn-warning';
                    listVendorsButton.innerHTML = "{{ trans('general.view') }}";

                    return listVendorsButton;
                }},
            ],
            ajaxResponse:function(url, params, response){
                $('span[data-component="newlyRegisteredVendorsCount"]').text(response.totalVendorsCount);

                return response.data;
            },
        });

        $('[name="date_from"], [name="date_to"]').datetimepicker({
            format: 'DD/MM/YYYY',
            stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
            showTodayButton: true,
            allowInputToggle: true,
        });

        $('#btnFilterTotalNumberOfNewlyRegisteredVendorsByDate').on('click', function() {
            var dateFrom = $('#date_from').val().trim();
            var dateTo   = $('#date_to').val().trim();

            var ajaxData = {
                dateFrom: dateFrom,
                dateTo: dateTo,
            };

            newlyRegisteredVendorsByDateTable.setData("{{ route('vendorManagement.dashboard.registrationStatistics.newlyRegisteredVendorsByDate') }}", ajaxData);
        });

        $(document).on('click', '[data-action="listVendors"]', function(e) {
            e.preventDefault();

            var companyIds = $(this).data('company_ids');

            $('#newlyRegisteredVendorListModal').data('company_ids', companyIds);
            $('#newlyRegisteredVendorListModal').modal('show');
        });

        $(document).on('shown.bs.modal', '#newlyRegisteredVendorListModal', function (e) {
            e.preventDefault();

            var companyIds = $(this).data('company_ids');

            var newlyRegisteredVendorListTable = new Tabulator('#newlyRegisteredVendorListTable', {
                columnHeaderVertAlign:"middle",
                ajaxURL: "{{ route('vendorManagement.dashboard.registrationStatistics.vendorList') }}",
                ajaxParams:{companyIds:companyIds},
                ajaxConfig: "GET",
                layout:"fitColumns",
                height: 400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                pagination: 'local',
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.name') }}", field: 'name', hozAlign:'left', cssClass:"text-middle", headerSort:false, headerFilter:true},
                ]
            });
        });
</script>