<script>
    $(document).ready(function() {
        $.get("{{ route('vendorManagement.dashboard.totalEvaluated.vendorGroup') }}", function(data){
            new Tabulator("#total-evaluated-by-vendor-group", {
                columnHeaderVertAlign:"middle",
                data: data,
                layout:"fitColumns",
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", minWidth: 300, hozAlign: 'center', cssClass: 'text-middle text-center'},
                    {title:"{{ trans('vendorManagement.inProgress')}}", field:'in_progress', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                    {title:"{{ trans('vendorManagement.completed')}}", field:'completed', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                ]
            });
        });

        $.get("{{ route('vendorManagement.dashboard.totalEvaluated.vendorCategory') }}", function(data){
            new Tabulator("#total-evaluated-by-vendor-category", {
                columnHeaderVertAlign:"middle",
                data: data,
                layout:"fitColumns",
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category", minWidth: 300, hozAlign: 'center', cssClass: 'text-middle text-center'},
                    {title:"{{ trans('vendorManagement.inProgress')}}", field:'in_progress', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                    {title:"{{ trans('vendorManagement.completed')}}", field:'completed', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                ]
            });
        });
    });
</script>