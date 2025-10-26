<script type="text/javascript">
    var myProcessesModalStack = new ModalStack();
    
    $('#my-processes-show').on('click', function(){
        myProcessesModalStack.push('#my-processes-modal');
    });

    var viewProcessRoute;

    var myProcessesVerifiersTable = new Tabulator('#my-processes-verifiers-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"name", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.status') }}", field:"approved", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter: function(cell, formatterParams, onRendered){
                var label;
                switch(cell.getValue())
                {
                    case true:
                        label = '<span class="text-success"><i class="fa fa-thumbs-up"></i> <strong>{{ trans("verifiers.approved") }}</strong></span>';
                        break;
                    case false:
                        label = '<span class="text-danger"><i class="fa fa-thumbs-down"></i> <strong>{{ trans("verifiers.rejected") }}</strong></span>';
                        break;
                    default:
                        label = '<span class="text-warning"><i class="fa fa-question"></i> <strong>{{ trans("verifiers.unverified") }}</strong></span>';
                }
                return label;
            }},
            {title:"{{ trans('verifiers.verifiedAt') }}", field:"verified_at", width: 160, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
        ],
    });

    $('#my-processes-verifiers-modal').on('hide.bs.modal', function(){
        $('#my-processes-verifiers-modal [data-action=info]').data('url', '#');
    });

    $('#my-processes-verifiers-modal').on('click', '[data-action=info]', function(){
        location.href = $(this).data('url');
    });

    var recommendationOfTendererProcessesTable = new Tabulator('#recommendation-of-tenderer-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('#my-processes-modal').on('show.bs.modal', function(){
        recommendationOfTendererProcessesTable.setData("{{ route('home.myProcesses.recommendationOfTenderer') }}");
    });

    $('a[data-toggle="tab"][href="#tendering-processes"]').on('shown.bs.tab', function (e) {
        recommendationOfTendererProcessesTable.setData('{{ route("home.myProcesses.recommendationOfTenderer") }}');
    })

    $('a[data-toggle="tab"][href="#recommendation-of-tenderer-processes-tab"]').on('shown.bs.tab', function (e) {
        recommendationOfTendererProcessesTable.setData("{{ route('home.myProcesses.recommendationOfTenderer') }}");
    })

    $('#recommendation-of-tenderer-processes-table').on('click', '[data-action=view]', function(){
        var rowData = recommendationOfTendererProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.recommendationOfTenderer') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var listOfTendererProcessesTable = new Tabulator('#list-of-tenderer-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#list-of-tenderer-processes-tab"]').on('shown.bs.tab', function (e) {
        listOfTendererProcessesTable.setData("{{ route('home.myProcesses.listOfTenderer') }}");
    })

    $('#list-of-tenderer-processes-table').on('click', '[data-action=view]', function(){
        var rowData = listOfTendererProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.listOfTenderer') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var callingTenderProcessesTable = new Tabulator('#calling-tender-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#calling-tender-processes-tab"]').on('shown.bs.tab', function (e) {
        callingTenderProcessesTable.setData("{{ route('home.myProcesses.callingTender') }}");
    })

    $('#calling-tender-processes-table').on('click', '[data-action=view]', function(){
        var rowData = callingTenderProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.callingTender') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var openTenderProcessesTable = new Tabulator('#open-tender-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.progress') }}", field:"verifier_progress", width: 70, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:function(cell){
                var rowData = cell.getData();
                return "<strong class='text-warning'>"+rowData['completed_verifiers']+"/"+rowData['total_verifiers']+"</strong>";
            }},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#open-tender-processes-tab"]').on('shown.bs.tab', function (e) {
        openTenderProcessesTable.setData("{{ route('home.myProcesses.openTender') }}");
    })

    $('#open-tender-processes-table').on('click', '[data-action=view]', function(){
        var rowData = openTenderProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.openTender') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var technicalEvaluationProcessesTable = new Tabulator('#technical-evaluation-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.progress') }}", field:"verifier_progress", width: 70, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:function(cell){
                var rowData = cell.getData();
                return "<strong class='text-warning'>"+rowData['completed_verifiers']+"/"+rowData['total_verifiers']+"</strong>";
            }},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#technical-evaluation-processes-tab"]').on('shown.bs.tab', function (e) {
        technicalEvaluationProcessesTable.setData("{{ route('home.myProcesses.technicalEvaluation') }}");
    })

    $('#technical-evaluation-processes-table').on('click', '[data-action=view]', function(){
        var rowData = technicalEvaluationProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.technicalOpening') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var technicalAssessmentProcessesTable = new Tabulator('#technical-assessment-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#technical-assessment-processes-tab"]').on('shown.bs.tab', function (e) {
        technicalAssessmentProcessesTable.setData("{{ route('home.myProcesses.technicalAssessment') }}");
    })

    $('#technical-assessment-processes-table').on('click', '[data-action=view]', function(){
        var rowData = technicalAssessmentProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.technicalAssessment') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var awardRecommendationProcessesTable = new Tabulator('#award-recommendation-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#award-recommendation-processes-tab"]').on('shown.bs.tab', function (e) {
        awardRecommendationProcessesTable.setData("{{ route('home.myProcesses.awardRecommendation') }}");
    })

    $('#award-recommendation-processes-table').on('click', '[data-action=view]', function(){
        var rowData = awardRecommendationProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.awardRecommendation') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var letterOfAwardProcessesTable = new Tabulator('#letter-of-award-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#letter-of-award-processes-tab"]').on('shown.bs.tab', function (e) {
        letterOfAwardProcessesTable.setData("{{ route('home.myProcesses.letterOfAward') }}");
    })

    $('#letter-of-award-processes-table').on('click', '[data-action=view]', function(){
        var rowData = letterOfAwardProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.letterOfAward') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var tenderResubmissionProcessesTable = new Tabulator('#tender-resubmission-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#tender-resubmission-processes-tab"]').on('shown.bs.tab', function (e) {
        tenderResubmissionProcessesTable.setData("{{ route('home.myProcesses.tenderResubmission') }}");
    })

    $('#tender-resubmission-processes-table').on('click', '[data-action=view]', function(){
        var rowData = tenderResubmissionProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.tenderResubmission') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var requestForInformationMessageProcessesTable = new Tabulator('#request-for-information-message-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#request-for-information-message-processes-tab"]').on('shown.bs.tab', function (e) {
        requestForInformationMessageProcessesTable.setData("{{ route('home.myProcesses.requestForInformationMessage') }}");
    })

    $('#request-for-information-message-processes-table').on('click', '[data-action=view]', function(){
        var rowData = requestForInformationMessageProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.requestForInformation') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var riskRegisterMessageProcessesTable = new Tabulator('#risk-register-message-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#risk-register-message-processes-tab"]').on('shown.bs.tab', function (e) {
        riskRegisterMessageProcessesTable.setData("{{ route('home.myProcesses.riskRegisterMessage') }}");
    })

    $('#risk-register-message-processes-table').on('click', '[data-action=view]', function(){
        var rowData = riskRegisterMessageProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.riskRegister') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var publishToPostContractProcessesTable = new Tabulator('#publish-to-post-contract-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
            {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#post-contract-processes"]').on('shown.bs.tab', function (e) {
        publishToPostContractProcessesTable.setData("{{ route('home.myProcesses.publishToPostContract') }}");
    })

    $('a[data-toggle="tab"][href="#publish-to-post-contract-processes-tab"]').on('shown.bs.tab', function (e) {
        publishToPostContractProcessesTable.setData("{{ route('home.myProcesses.publishToPostContract') }}");
    })

    $('#publish-to-post-contract-processes-table').on('click', '[data-action=view]', function(){
        var rowData = publishToPostContractProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.publishToPostContract') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    <?php
        $postContractClaimModules = [
            [
                'tableName'  => 'waterDepositProcessesTable',
                'tabId'      => 'water-deposit-processes-tab',
                'tableId'    => 'water-deposit-processes-table',
                'tableRoute' => route('home.myProcesses.waterDeposit'),
                'title'      => trans('toDoLists.waterDeposit'),
            ],[
                'tableName'  => 'depositProcessesTable',
                'tabId'      => 'deposit-processes-tab',
                'tableId'    => 'deposit-processes-table',
                'tableRoute' => route('home.myProcesses.deposit'),
                'title'      => trans('toDoLists.deposit'),
            ],[
                'tableName'  => 'outOfContractItemsProcessesTable',
                'tabId'      => 'out-of-contract-item-processes-tab',
                'tableId'    => 'out-of-contract-item-processes-table',
                'tableRoute' => route('home.myProcesses.outOfContractItems'),
                'title'      => trans('toDoLists.outOfContractItems'),
            ],[
                'tableName'  => 'purchaseOnBehalfProcessesTable',
                'tabId'      => 'purchase-on-behalf-processes-tab',
                'tableId'    => 'purchase-on-behalf-processes-table',
                'tableRoute' => route('home.myProcesses.purchaseOnBehalf'),
                'title'      => trans('toDoLists.purchaseOnBehalf'),
            ],[
                'tableName'  => 'advancedPaymentProcessesTable',
                'tabId'      => 'advanced-payment-processes-tab',
                'tableId'    => 'advanced-payment-processes-table',
                'tableRoute' => route('home.myProcesses.advancedPayment'),
                'title'      => trans('toDoLists.advancedPayment'),
            ],[
                'tableName'  => 'workOnBehalfProcessesTable',
                'tabId'      => 'work-on-behalf-processes-tab',
                'tableId'    => 'work-on-behalf-processes-table',
                'tableRoute' => route('home.myProcesses.workOnBehalf'),
                'title'      => trans('toDoLists.workOnBehalf'),
            ],[
                'tableName'  => 'workOnBehalfBackChargeProcessesTable',
                'tabId'      => 'work-on-behalf-back-charge-processes-tab',
                'tableId'    => 'work-on-behalf-back-charge-processes-table',
                'tableRoute' => route('home.myProcesses.workOnBehalfBackCharge'),
                'title'      => trans('toDoLists.workOnBehalfBackCharge'),
            ],[
                'tableName'  => 'penaltyProcessesTable',
                'tabId'      => 'penalty-processes-tab',
                'tableId'    => 'penalty-processes-table',
                'tableRoute' => route('home.myProcesses.penalty'),
                'title'      => trans('toDoLists.penalty'),
            ],[
                'tableName'  => 'permitProcessesTable',
                'tabId'      => 'permit-processes-tab',
                'tableId'    => 'permit-processes-table',
                'tableRoute' => route('home.myProcesses.permit'),
                'title'      => trans('toDoLists.permit'),
            ],[
                'tableName'  => 'variationOrderProcessesTable',
                'tabId'      => 'variation-order-processes-tab',
                'tableId'    => 'variation-order-processes-table',
                'tableRoute' => route('home.myProcesses.variationOrder'),
                'title'      => trans('toDoLists.variationOrder'),
            ],[
                'tableName'  => 'materialOnSiteProcessesTable',
                'tabId'      => 'material-on-site-processes-tab',
                'tableId'    => 'material-on-site-processes-table',
                'tableRoute' => route('home.myProcesses.materialOnSite'),
                'title'      => trans('toDoLists.materialOnSite'),
            ],[
                'tableName'  => 'claimCertificateProcessesTable',
                'tabId'      => 'claim-certificate-processes-tab',
                'tableId'    => 'claim-certificate-processes-table',
                'tableRoute' => route('home.myProcesses.claimCertificate'),
                'title'      => trans('toDoLists.claimCertificate'),
            ],[
                'tableName'  => 'requestForVariationProcessesTable',
                'tabId'      => 'request-for-variation-processes-tab',
                'tableId'    => 'request-for-variation-processes-table',
                'tableRoute' => route('home.myProcesses.requestForVariation'),
                'title'      => trans('toDoLists.requestForVariation'),
            ],[
                'tableName'  => 'accountCodeSettingProcessesTable',
                'tabId'      => 'account-code-setting-processes-tab',
                'tableId'    => 'account-code-setting-processes-table',
                'tableRoute' => route('home.myProcesses.accountCodeSetting'),
                'title'      => trans('toDoLists.accountCodeSettings'),
            ],[
                'tableName'  => 'siteManagementDefectProcessesTable',
                'tabId'      => 'site-management-defect-processes-tab',
                'tableId'    => 'site-management-defect-processes-table',
                'tableRoute' => route('home.myProcesses.siteManagementDefectBackchargeDetail'),
                'title'      => trans('toDoLists.siteManagementDefects'),
            ],[
                'tableName'  => 'requestForInspectionProcessesTable',
                'tabId'      => 'request-for-inspection-processes-tab',
                'tableId'    => 'request-for-inspection-processes-table',
                'tableRoute' => route('home.myProcesses.requestForInspection'),
                'title'      => trans('toDoLists.requestForInspection'),
            ],[
                'tableName'  => 'siteDiaryProcessesTable',
                'tabId'      => 'site-diary-processes-tab',
                'tableId'    => 'site-diary-processes-table',
                'tableRoute' => route('home.myProcesses.siteManagementSiteDiaryList'),
                'title'      => trans('toDoLists.siteDiary'),
            ],[
                'tableName'  => 'instructionToContractorProcessesTable',
                'tabId'      => 'instruction-to-contractor-processes-tab',
                'tableId'    => 'instruction-to-contractor-processes-table',
                'tableRoute' => route('home.myProcesses.instructionToContractorList'),
                'title'      => trans('toDoLists.instructionToContractor'),
            ],[
                'tableName'  => 'dailyReportProcessesTable',
                'tabId'      => 'daily-report-processes-tab',
                'tableId'    => 'daily-report-processes-table',
                'tableRoute' => route('home.myProcesses.dailyReportList'),
                'title'      => trans('toDoLists.dailyReport'),
            ]
        ];
    ?>

    @foreach($postContractClaimModules as $moduleInfo)
        var {{ $moduleInfo['tableName'] }} = new Tabulator('#{{ $moduleInfo["tableId"] }}', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
                {title:"{{ trans('projects.reference') }}", field: 'reference', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                {title:"{{ trans('projects.project') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
                {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
                {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
                {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: [
                        {
                            tag: 'button',
                            attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-users'}
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                            rowAttributes: {'href': 'route:view'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-search'}
                            }
                        },
                    ]
                }}
            ],
        });

        $('a[data-toggle="tab"][href="#{{ $moduleInfo["tabId"]}}"]').on('shown.bs.tab', function (e) {
            {{ $moduleInfo['tableName'] }}.setData("{{ $moduleInfo['tableRoute'] }}");
        })

        $('#{{ $moduleInfo["tableId"] }}').on('click', '[data-action=view]', function(){
            var rowData = {{ $moduleInfo['tableName'] }}.getRow($(this).data('id')).getData();
            myProcessesVerifiersTable.setData(rowData['route:verifiers']);

            var infoDiv = $('#my-processes-modal-templates [data-id=project-process-info]').clone();
            $(infoDiv).find('[data-id=process]').html("{{ $moduleInfo['title'] }}");
            $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
            $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
            $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
            $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
            $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

            $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

            $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

            myProcessesModalStack.push('#my-processes-verifiers-modal');
        });
    @endforeach

    $('a[data-toggle="tab"][href="#site-module-processes"]').on('shown.bs.tab', function (e) {
        requestForInspectionProcessesTable.setData('{{ route("home.myProcesses.requestForInspection") }}');
        siteDiaryProcessesTable.setData('{{ route("home.myProcesses.siteManagementSiteDiaryList") }}');

    })

    var vendorRegistrationProcessesTable = new Tabulator('#vendor-registration-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('companies.name') }}", field:"company_name", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#vendor-management-processes"]').on('shown.bs.tab', function (e) {
        vendorRegistrationProcessesTable.setData('{{ route("home.myProcesses.vendorRegistration") }}');
    })

    $('a[data-toggle="tab"][href="#vendor-registration-processes-tab"]').on('shown.bs.tab', function (e) {
        vendorRegistrationProcessesTable.setData('{{ route("home.myProcesses.vendorRegistration") }}');
    })

    $('#vendor-registration-processes-table').on('click', '[data-action=view]', function(){
        var rowData = vendorRegistrationProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=vendor-registration-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.vendorRegistration') }}");
        $(infoDiv).find('[data-id=company-name]').html(rowData['company_name']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    var vendorEvaluationProcessesTable = new Tabulator('#vendor-evaluation-processes-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('companies.name') }}", field:"company_name", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
            {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
            {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-users'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'href': 'route:view'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-search'}
                        }
                    },
                ]
            }}
        ],
    });

    $('a[data-toggle="tab"][href="#vendor-evaluation-processes-tab"]').on('shown.bs.tab', function (e) {
        vendorEvaluationProcessesTable.setData('{{ route("home.myProcesses.vendorEvaluation") }}');
    })

    $('#vendor-evaluation-processes-table').on('click', '[data-action=view]', function(){
        var rowData = vendorEvaluationProcessesTable.getRow($(this).data('id')).getData();
        myProcessesVerifiersTable.setData(rowData['route:verifiers']);

        var infoDiv = $('#my-processes-modal-templates [data-id=vendor-evaluation-process-info]').clone();
        $(infoDiv).find('[data-id=process]').html("{{ trans('toDoLists.vendorEvaluation') }}");
        $(infoDiv).find('[data-id=project-reference]').html(rowData['reference']);
        $(infoDiv).find('[data-id=project-title]').html(rowData['title']);
        $(infoDiv).find('[data-id=company-name]').html(rowData['company_name']);
        $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
        $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
        $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

        $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

        $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

        myProcessesModalStack.push('#my-processes-verifiers-modal');
    });

    <?php
        $consultantManagementModules = [
            [
                'tableName'  => 'recommendationOfConsultantProcessesTable',
                'tabId'      => 'recommendation-of-consultant-processes-tab',
                'tableId'    => 'recommendation-of-consultant-processes-table',
                'tableRoute' => route('home.myProcesses.recommendationOfConsultant'),
                'title'      => trans('toDoLists.recommendationOfConsultant'),
            ],[
                'tableName'  => 'listOfConsultantProcessesTable',
                'tabId'      => 'list-of-consultant-processes-tab',
                'tableId'    => 'list-of-consultant-processes-table',
                'tableRoute' => route('home.myProcesses.listOfConsultant'),
                'title'      => trans('toDoLists.listOfConsultant'),
            ],[
                'tableName'  => 'callingRfpProcessesTable',
                'tabId'      => 'calling-rfp-processes-tab',
                'tableId'    => 'calling-rfp-processes-table',
                'tableRoute' => route('home.myProcesses.callingRfp'),
                'title'      => trans('toDoLists.callingRfp'),
            ],[
                'tableName'  => 'openRfpProcessesTable',
                'tabId'      => 'open-rfp-processes-tab',
                'tableId'    => 'open-rfp-processes-table',
                'tableRoute' => route('home.myProcesses.openRfp'),
                'title'      => trans('toDoLists.openRfp'),
            ],[
                'tableName'  => 'rfpResubmissionProcessesTable',
                'tabId'      => 'rfp-resubmission-processes-tab',
                'tableId'    => 'rfp-resubmission-processes-table',
                'tableRoute' => route('home.myProcesses.rfpResubmission'),
                'title'      => trans('toDoLists.rfpResubmission'),
            ],[
                'tableName'  => 'approvalDocumentProcessesTable',
                'tabId'      => 'approval-documents-processes-tab',
                'tableId'    => 'approval-documents-processes-table',
                'tableRoute' => route('home.myProcesses.approvalDocument'),
                'title'      => trans('toDoLists.approvalDocument'),
            ],[
                'tableName'  => 'consultantManagementLetterOfAwardProcessesTable',
                'tabId'      => 'consultant-management-letter-of-award-processes-tab',
                'tableId'    => 'consultant-management-letter-of-award-processes-table',
                'tableRoute' => route('home.myProcesses.consultantManagementLetterOfAward'),
                'title'      => trans('toDoLists.letterOfAward'),
            ],
        ];
    ?>

    @foreach($consultantManagementModules as $moduleInfo)
        var {{ $moduleInfo['tableName'] }} = new Tabulator('#{{ $moduleInfo["tableId"] }}', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
                {title:"{{ trans('companies.referenceNo') }}", field: 'reference_no', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                {title:"{{ trans('projects.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerFilter: "input", headerSort:false},
                {title:"{{ trans('vendorManagement.vendorCategory') }}", field: 'vendor_category', hozAlign:"center", cssClass:"text-center", width: 150, headerFilter: "input", headerSort:false },
                {title:"{{ trans('verifiers.daysFromSubmission') }}", field:"days_from_submission", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
                {title:"{{ trans('verifiers.daysPending') }}", field:"days_pending", width: 100, hozAlign:"center", cssClass:"text-center text-top", headerSort:false},
                {title:"{{ trans('verifiers.verifier') }}", field:"current_verifier", width: 150, hozAlign:"center", cssClass:"text-center text-top", headerFilter: "input", headerSort:false},
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-top", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: [
                        {
                            tag: 'button',
                            attributes: {type: 'button', 'data-action': 'view', class:'btn btn-xs btn-info', title: '{{ trans("verifiers.verifiers") }}'},
                            rowAttributes: {'data-id': 'id'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-users'}
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                            rowAttributes: {'href': 'route:view'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-search'}
                            }
                        },
                    ]
                }}
            ],
        });

        $('a[data-toggle="tab"][href="#{{ $moduleInfo["tabId"] }}"]').on('shown.bs.tab', function (e) {
            {{ $moduleInfo['tableName'] }}.setData('{{ $moduleInfo["tableRoute"] }}');
        })

        $('#{{ $moduleInfo["tableId"] }}').on('click', '[data-action=view]', function(){
            var rowData = {{ $moduleInfo['tableName'] }}.getRow($(this).data('id')).getData();
            myProcessesVerifiersTable.setData(rowData['route:verifiers']);

            var infoDiv = $('#my-processes-modal-templates [data-id=consultant-management-process-info]').clone();
            $(infoDiv).find('[data-id=process]').html("{{ $moduleInfo['title'] }}");
            $(infoDiv).find('[data-id=reference-no]').html(rowData['reference_no']);
            $(infoDiv).find('[data-id=title]').html(rowData['title']);
            $(infoDiv).find('[data-id=vendor-category]').html(rowData['vendor_category']);
            $(infoDiv).find('[data-id=submitted-by]').html(rowData['submitted_by']);
            $(infoDiv).find('[data-id=submitted-at]').html(rowData['submitted_at']);
            $(infoDiv).find('[data-id=days-since-start]').html(rowData['days_from_submission']);

            $('#my-processes-verifiers-modal [data-id=info-div]').html(infoDiv);

            $('#my-processes-verifiers-modal [data-action=info]').data('url', rowData['route:view']);

            myProcessesModalStack.push('#my-processes-verifiers-modal');
        });
    @endforeach

    $('a[data-toggle="tab"][href="#consultant-management-processes"]').on('shown.bs.tab', function (e) {
        recommendationOfConsultantProcessesTable.setData('{{ route("home.myProcesses.recommendationOfConsultant") }}');
    })

    $.get("{{ route('home.myProcesses.count') }}", function(data){
        $('#my-processes-show span').prop('hidden', null);
        $('#my-processes-show [data-id=count]').html(data.all);

        $('#my-processes-modal .nav-link[href="#tendering-processes"] [data-category=count]').html(data['tendering']);
        $('#my-processes-modal .nav-link[href="#post-contract-processes"] [data-category=count]').html(data['postContract']);
        $('#my-processes-modal .nav-link[href="#site-module-processes"] [data-category=count]').html(data['siteModule']);
        $('#my-processes-modal .nav-link[href="#vendor-management-processes"] [data-category=count]').html(data['vendorManagement']);
        $('#my-processes-modal .nav-link[href="#consultant-management-processes"] [data-category=count]').html(data['consultantManagement']);

        $('#my-processes-modal .nav-link[href="#recommendation-of-tenderer-processes-tab"] [data-category=count]').html(data['recommendationOfTenderer']);
        $('#my-processes-modal .nav-link[href="#list-of-tenderer-processes-tab"] [data-category=count]').html(data['listOfTenderer']);
        $('#my-processes-modal .nav-link[href="#calling-tender-processes-tab"] [data-category=count]').html(data['callingTender']);
        $('#my-processes-modal .nav-link[href="#open-tender-processes-tab"] [data-category=count]').html(data['openTender']);
        $('#my-processes-modal .nav-link[href="#technical-evaluation-processes-tab"] [data-category=count]').html(data['technicalEvaluation']);
        $('#my-processes-modal .nav-link[href="#technical-assessment-processes-tab"] [data-category=count]').html(data['technicalAssessment']);
        $('#my-processes-modal .nav-link[href="#award-recommendation-processes-tab"] [data-category=count]').html(data['awardRecommendation']);
        $('#my-processes-modal .nav-link[href="#letter-of-award-processes-tab"] [data-category=count]').html(data['letterOfAward']);
        $('#my-processes-modal .nav-link[href="#tender-resubmission-processes-tab"] [data-category=count]').html(data['tenderResubmission']);
        $('#my-processes-modal .nav-link[href="#request-for-information-message-processes-tab"] [data-category=count]').html(data['requestForInformationMessage']);
        $('#my-processes-modal .nav-link[href="#risk-register-message-processes-tab"] [data-category=count]').html(data['riskRegisterMessage']);

        $('#my-processes-modal .nav-link[href="#publish-to-post-contract-processes-tab"] [data-category=count]').html(data['publishToPostContract']);
        $('#my-processes-modal .nav-link[href="#water-deposit-processes-tab"] [data-category=count]').html(data['waterDeposit']);
        $('#my-processes-modal .nav-link[href="#deposit-processes-tab"] [data-category=count]').html(data['deposit']);
        $('#my-processes-modal .nav-link[href="#out-of-contract-item-processes-tab"] [data-category=count]').html(data['outOfContractItems']);
        $('#my-processes-modal .nav-link[href="#purchase-on-behalf-processes-tab"] [data-category=count]').html(data['purchaseOnBehalf']);
        $('#my-processes-modal .nav-link[href="#advanced-payment-processes-tab"] [data-category=count]').html(data['advancedPayment']);
        $('#my-processes-modal .nav-link[href="#work-on-behalf-processes-tab"] [data-category=count]').html(data['workOnBehalf']);
        $('#my-processes-modal .nav-link[href="#work-on-behalf-back-charge-processes-tab"] [data-category=count]').html(data['workOnBehalfBackCharge']);
        $('#my-processes-modal .nav-link[href="#penalty-processes-tab"] [data-category=count]').html(data['penalty']);
        $('#my-processes-modal .nav-link[href="#permit-processes-tab"] [data-category=count]').html(data['permit']);
        $('#my-processes-modal .nav-link[href="#variation-order-processes-tab"] [data-category=count]').html(data['variationOrder']);
        $('#my-processes-modal .nav-link[href="#material-on-site-processes-tab"] [data-category=count]').html(data['materialOnSite']);
        $('#my-processes-modal .nav-link[href="#claim-certificate-processes-tab"] [data-category=count]').html(data['claimCertificate']);
        $('#my-processes-modal .nav-link[href="#request-for-variation-processes-tab"] [data-category=count]').html(data['requestForVariation']);
        $('#my-processes-modal .nav-link[href="#account-code-setting-processes-tab"] [data-category=count]').html(data['accountCodeSetting']);
        $('#my-processes-modal .nav-link[href="#site-management-defect-processes-tab"] [data-category=count]').html(data['siteManagementDefectBackchargeDetail']);

        $('#my-processes-modal .nav-link[href="#request-for-inspection-processes-tab"] [data-category=count]').html(data['requestForInspection']);
        $('#my-processes-modal .nav-link[href="#site-diary-processes-tab"] [data-category=count]').html(data['siteDiary']);
        $('#my-processes-modal .nav-link[href="#instruction-to-contractor-processes-tab"] [data-category=count]').html(data['instructionToContractor']);
        $('#my-processes-modal .nav-link[href="#daily-report-processes-tab"] [data-category=count]').html(data['dailyReport']);

        $('#my-processes-modal .nav-link[href="#vendor-registration-processes-tab"] [data-category=count]').html(data['vendorRegistration']);
        $('#my-processes-modal .nav-link[href="#vendor-evaluation-processes-tab"] [data-category=count]').html(data['vendorEvaluation']);

        $('#my-processes-modal .nav-link[href="#recommendation-of-consultant-processes-tab"] [data-category=count]').html(data['recommendationOfConsultant']);
        $('#my-processes-modal .nav-link[href="#list-of-consultant-processes-tab"] [data-category=count]').html(data['listOfConsultant']);
        $('#my-processes-modal .nav-link[href="#calling-rfp-processes-tab"] [data-category=count]').html(data['callingRfp']);
        $('#my-processes-modal .nav-link[href="#open-rfp-processes-tab"] [data-category=count]').html(data['openRfp']);
        $('#my-processes-modal .nav-link[href="#rfp-resubmission-processes-tab"] [data-category=count]').html(data['rfpResubmission']);
        $('#my-processes-modal .nav-link[href="#approval-documents-processes-tab"] [data-category=count]').html(data['approvalDocument']);
        $('#my-processes-modal .nav-link[href="#consultant-management-letter-of-award-processes-tab"] [data-category=count]').html(data['consultantManagementLetterOfAward']);
    });
</script>