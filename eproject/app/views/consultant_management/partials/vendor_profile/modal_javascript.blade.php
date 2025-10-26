<script type="text/javascript">
    $("select#vp-vendor_categories").select2();
    $("select#vp-cidb_codes").select2();
    new Tabulator("#company-personnel-directors-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
        ]
    });

    new Tabulator("#company-personnel-shareholders-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.designation') }}", field:"designation", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.amountOfShare') }}", field:"amount_of_share", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.holdingPercentage') }}", field:"holding_percentage", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
        ]
    });

    new Tabulator("#company-personnel-head-of-company-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
        ]
    });

    new Tabulator('#completed-project-track-record-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.title') }}", field:"title", width: 480, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
            {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicOrConquasScore') }}", field:"has_qlassic_or_conquas_score", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicScore') }}", field:"qlassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicYearOfAchievement') }}", field:"qlassic_year_of_achievement", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.conquasScore') }}", field:"conquas_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.conquasYearOfAchievement') }}", field:"conquas_year_of_achievement", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.awardsReceived') }}", field:"awards_received", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfAwardsReceived') }}", field:"year_of_recognition_awards", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
        ]
    });

    new Tabulator('#current-project-track-record-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.title') }}", field:"title", minWidth: 480, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
            {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
        ],
    });

    new Tabulator("#vendor-prequalification-table", {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        layout:"fitColumns",
        paginationSize: 100,
        pagination: "remote",
        columns:[
            {title:"{{ trans('vendorManagement.form') }}", field:"form", minWidth: 200, hozAlign:'left', headerSort:false},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendorWorkCategory", width: 280, hozAlign:'left', headerSort:false},
            {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 250, cssClass:"text-center text-middle", headerSort:false}
        ]
    });

    new Tabulator('#vendor_work_categories-table', {
        height:360,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data:[],
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            { title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: function(rowData){
                    var c = '<div class="well">';
                    $.each(rowData.vendor_categories, function( key, value ) {
                        c+='<p>'+value+'</p>';
                    });
                    c+='</div>';
                    return c;
                }
            }},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: function(rowData){
                    var c = '<div class="well">';
                    c+='<p>'+rowData.vendor_work_category_name+'</p>';
                    c+='</div>';
                    return c;
                }
            }},
            {title:"{{ trans('vendorManagement.qualified') }}", field:"qualified", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ],
    });

    new Tabulator('#awarded-projects-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data: [],
        ajaxConfig: "GET",
        layout:"fitColumns",
        dataLoaded:function(data){
            if(data.length < 1) return;
        },
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('projects.status') }}", field:"status", width: 120, hozAlign:"center", headerSort:false},
            {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
            {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false}
        ],
    });

    new Tabulator('#completed-projects-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        data: [],
        ajaxConfig: "GET",
        layout:"fitColumns",
        dataLoaded:function(data){
            if(data.length < 1) return;
        },
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
            {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false}
        ],
    });
</script>