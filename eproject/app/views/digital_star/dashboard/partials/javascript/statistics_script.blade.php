<script>
    $(document).ready(function() {
        var companyFormCompletionTable = new Tabulator('#company-form-completion', {
            layout: "fitColumns",
            height: 450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('digital-star.dashboard.stats', array('company')) }}",
            ajaxConfig: "GET",
            ajaxFiltering: true,
            pagination: "remote",
            paginationSize: 100,
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('digitalStar/digitalStar.company') }}", field:"company", minWidth: 200, hozAlign: 'center', cssClass: 'text-middle text-center'},
                {title:"{{ trans('digitalStar/digitalStar.totalCompletedForms')}}", field:'completed', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                {title:"{{ trans('digitalStar/digitalStar.completionRate')}}", field:'completion_rate', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                {title:"{{ trans('digitalStar/digitalStar.totalPendingForms')}}", field:'pending', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
            ]
        });

        var projectFormCompletionTable = new Tabulator('#project-form-completion', {
            layout: "fitColumns",
            height: 450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('digital-star.dashboard.stats', array('project')) }}",
            ajaxConfig: "GET",
            ajaxFiltering: true,
            pagination: "remote",
            paginationSize: 100,
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('digitalStar/digitalStar.company') }}", field:"company", minWidth: 200, hozAlign: 'center', cssClass: 'text-middle text-center'},
                {title:"{{ trans('digitalStar/digitalStar.totalCompletedForms')}}", field:'completed', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                {title:"{{ trans('digitalStar/digitalStar.completionRate')}}", field:'completion_rate', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
                {title:"{{ trans('digitalStar/digitalStar.totalPendingForms')}}", field:'pending', width: 150, hozAlign: 'center', cssClass: 'text-middle text-center'},
            ]
        });
    });
</script>