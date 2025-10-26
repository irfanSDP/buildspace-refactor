<script>
    $(document).ready(function() {
        $.get("{{ route('vendorManagement.dashboard.totalEvaluationsByRating.vendorGroup') }}", function(data){
            if(data.ratings.length < 1){
                $('#vendor-group-total-evaluations-by-rating').hide();
                $('#vendor-category-total-evaluations-by-rating').hide();
                $('#vpe-grade-link').show();
                return;
            }

            var ratingColumns = [];

            for(var i in data.ratings){
                ratingColumns.push({title:data.ratings[i]['description'], field:data.ratings[i]['id']+'_count', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'});
            }

            var vendorGroupEvaluationsByRating = new Tabulator("#vendor-group-total-evaluations-by-rating", {
                columnHeaderVertAlign:"middle",
                data: data.data,
                layout:"fitColumns",
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen: true},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendorGroup", minWidth: 300, hozAlign: 'center', cssClass: 'text-middle text-center', frozen: true},
                    {
                        title:"{{ trans('vendorManagement.rating') }}", cssClass: 'text-middle text-center',
                        columns:ratingColumns,
                    }
                ] 
            });
        });

        $.get("{{ route('vendorManagement.dashboard.totalEvaluationsByRating.vendorCategory') }}", function(data){
            var ratingColumns = [];

            for(var i in data.ratings){
                ratingColumns.push({title:data.ratings[i]['description'], field:data.ratings[i]['id']+'_count', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'});
            }

            new Tabulator("#vendor-category-total-evaluations-by-rating", {
                columnHeaderVertAlign:"middle",
                data: data.data,
                layout:"fitColumns",
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen: true},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendorCategory", minWidth: 300, hozAlign: 'center', cssClass: 'text-middle text-center', frozen: true},
                    {
                        title:"{{ trans('vendorManagement.rating') }}", cssClass: 'text-middle text-center',
                        columns:ratingColumns,
                    }
                ]
            });
        });
    });
</script>