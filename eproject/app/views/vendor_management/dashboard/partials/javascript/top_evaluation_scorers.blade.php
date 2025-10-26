<script>
    @if($historicalCycleIds && (count($historicalCycleIds) > 0))
    $(document).ready(function() {
        @foreach($externalVendors as $vendor)
            @if(!$vendor->vendorCategories->isEmpty())
                var topScorerTable{{ $vendor->id }} = new Tabulator("#top-evaluation-score-table-{{ $vendor->id }}", {
                    columnHeaderVertAlign:"middle",
                    layout:"fitColumns",
                    height:400,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    columns:[
                        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('companies.company') }}", field:"company", minWidth:300, hozAlign: 'center', cssClass: 'text-middle text-center'},
                        {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", width:350, hozAlign: 'center', cssClass: 'text-middle text-center'},
                        {title:"{{ trans('vendorManagement.historicalVpeScore') }}", hozAlign: 'center', cssClass: 'text-middle text-center',
                        columns:[
                            @if(array_key_exists(0, $historicalCycleIds))
                            {title:"{{ trans('vendorManagement.lastVPECycleScore') }}", field:"{{ $historicalCycleIds[0] }}_score", width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                            @endif
                            @if(array_key_exists(1, $historicalCycleIds))
                            {title:"{{ trans('vendorManagement.secondLastVPECycleScore') }}", field:"{{ $historicalCycleIds[1] }}_score", width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                            @endif
                        ]},
                    ]
                });
                $('#top-evaluation-score-select-{{ $vendor->id }}').on('change', function(){
                    topScorerTable{{ $vendor->id }}.setData("{{ route('vendorManagement.dashboard.topEvaluationScorers') }}", {vendor_category_id: $('#top-evaluation-score-select-{{ $vendor->id }}').val()});
                });

                topScorerTable{{ $vendor->id }}.setData("{{ route('vendorManagement.dashboard.topEvaluationScorers') }}", {vendor_category_id: $('#top-evaluation-score-select-{{ $vendor->id }}').val()});
            @endif
        @endforeach
    });
    @endif
</script>