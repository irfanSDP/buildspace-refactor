<script>
    $(document).ready(function() {
        $.get("{{ route('vendorManagement.dashboard.overallVendorPerformanceStatisticsTable') }}", function(data){
            var displayNonZeroNumberFormatter = function(cell){
                if(cell.getValue() == 0) return "";

                return cell.getValue();
            }

            var vendorColumns = [];
            for(var vendorGroupId in data.vendorGroups)
            {
                vendorColumns.push({title:data.vendorGroups[vendorGroupId], field:vendorGroupId+"_assignedEvaluations", sorter:"number", width:150, hozAlign: 'center', cssClass: 'text-middle text-center', formatter: displayNonZeroNumberFormatter});
            }

            vendorColumns.push({title:"{{ trans('general.total') }}", field:"totalEvaluations", sorter:"number", width:100, hozAlign: 'center', cssClass: 'text-middle text-center', formatter: displayNonZeroNumberFormatter});

            var overallVendorPerformanceStatisticsTable = new Tabulator("#overallVendorPerformanceStatisticsTable", {
                columnHeaderVertAlign:"middle",
                data: data.data,
                layout:"fitColumns",
                height: 500,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                pagination: 'local',
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen: true},
                    {title:"{{ trans('subsidiaries.subsidiary') }}", field:"subsidiary", minWidth: 300, hozAlign: 'center', cssClass: 'text-middle text-center', frozen: true},
                    {title:"{{ trans('vendorManagement.numberOfProjectsAssigned') }}", field:"projectsAssigned", width: 200, hozAlign: 'center', cssClass: 'text-middle text-center', formatter: displayNonZeroNumberFormatter},
                    {
                        title:"{{ trans('vendorManagement.numberOfVendorsAssigned') }}", hozAlign: 'center', cssClass: 'text-middle text-center',
                        columns:vendorColumns,
                    },
                    {title:"{{ trans('vendorManagement.numberOfCompletedAppraisals') }}", field:"completedEvaluations", width:150, hozAlign: 'center', cssClass: 'text-middle text-center', formatter: displayNonZeroNumberFormatter},
                    {title:"{{ trans('vendorManagement.completionRate') }}", field:"completionPercentage", width:150, hozAlign: 'center', cssClass: 'text-middle text-center', formatter: displayNonZeroNumberFormatter},
                    {title:"{{ trans('vendorManagement.numberOfPendingSubmissions') }}", field:"pendingEvaluations", width:150, hozAlign: 'center', cssClass: 'text-middle text-center', formatter: displayNonZeroNumberFormatter},
                ]
            });
        });
    });
</script>