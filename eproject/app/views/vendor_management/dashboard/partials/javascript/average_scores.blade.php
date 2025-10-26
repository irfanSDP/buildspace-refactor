<script>
    $(document).ready(function() {
        $.get("{{ route('vendorManagement.dashboard.averageScores') }}", function(data){
            new Tabulator("#average-scores-table", {
                columnHeaderVertAlign:"middle",
                data: data,
                layout:"fitColumns",
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"name", minWidth: 300, hozAlign: 'center', cssClass: 'text-middle text-center'},
                    {title:"{{ trans('vendorManagement.averageScoreIndex')}}", field:'average_score', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                ]
            });
        });
    });
</script>