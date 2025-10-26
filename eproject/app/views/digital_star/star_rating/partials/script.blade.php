@if(! $isVendor)
    @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
        <script>
            $(document).ready(function () {
                var dsModalStack = new ModalStack();

                var dsRatingsTable = new Tabulator('#ds-ratings-table', {
                    height: 450,
                    ajaxURL: "{{ route('digital-star.star-rating.list', array($company->id)) }}",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxConfig: "GET",
                    paginationSize: 100,
                    pagination: "remote",
                    ajaxFiltering:true,
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('digitalStar/digitalStar.cycleName') }}", field:"cycle", hozAlign:"left", headerSort:false, headerFilter: true},
                        {title:"{{ trans('digitalStar/digitalStar.starRating') }}", width:500, hozAlign:"center", cssClass:"text-center text-middle", columns:[
                            {title:"{{ trans('digitalStar/digitalStar.score') }}", field:"score", width: 250, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('digitalStar/digitalStar.rating') }}", field:"rating", width: 250, cssClass:"text-center text-middle", headerSort:false},
                        ]},
                        {title:"{{ trans('general.actions') }}", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml:[
                                {
                                    tag: 'button',
                                    attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('digitalStar/digitalStar.companyEvaluation') }}", 'data-action': 'show-company'},
                                    rowAttributes: {'data-id': 'id'},
                                    innerHtml: function(rowData){
                                        return "{{ trans('digitalStar/digitalStar.companyEvaluation') }}";
                                    }
                                },{
                                    innerHtml: function(){
                                        return '&nbsp;';
                                    }
                                },{
                                    tag: 'button',
                                    attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('digitalStar/digitalStar.projectEvaluation') }}", 'data-action': 'show-project'},
                                    rowAttributes: {'data-id': 'id'},
                                    innerHtml: function(rowData){
                                        return "{{ trans('digitalStar/digitalStar.projectEvaluation') }}";
                                    }
                                }
                            ]
                        }}
                    ]
                });

                var dsCompanyScoreTable = new Tabulator('#ds-company-score-table', {
                    height: 450,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxConfig: "GET",
                    paginationSize: 100,
                    pagination: "remote",
                    ajaxFiltering:true,
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('digitalStar/digitalStar.company') }}", field:"company", hozAlign:"left", headerSort:false},
                        {title:"{{ trans('digitalStar/digitalStar.score') }}", field:"score", width: 250, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('digitalStar/digitalStar.rating') }}", field:"rating", width: 250, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.actions') }}", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml:[
                                {
                                    tag: 'button',
                                    attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('digitalStar/digitalStar.view') }}", 'data-action': 'show-form'},
                                    rowAttributes: {'data-id': 'id'},
                                    innerHtml: function(rowData){
                                        return "{{ trans('digitalStar/digitalStar.view') }}";
                                    }
                                }
                            ]
                        }}
                    ]
                });

                var dsProjectScoreTable = new Tabulator('#ds-project-score-table', {
                    height: 450,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxConfig: "GET",
                    paginationSize: 100,
                    pagination: "remote",
                    ajaxFiltering:true,
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('projects.reference') }}", field:"contract_no", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('projects.project') }}", field:"project", hozAlign:"left", headerSort:false, headerFilter: true},
                        {title:"{{ trans('digitalStar/digitalStar.score') }}", field:"score", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('digitalStar/digitalStar.rating') }}", field:"rating", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml:[
                                {
                                    tag: 'button',
                                    attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('digitalStar/digitalStar.view') }}", 'data-action': 'show-form'},
                                    rowAttributes: {'data-id': 'id'},
                                    innerHtml: function(rowData){
                                        return "{{ trans('digitalStar/digitalStar.view') }}";
                                    }
                                }
                            ]
                        }}
                    ]
                });

                var dsFormTable = new Tabulator('#ds-form-table', {
                    dataTree: true,
                    dataTreeStartExpanded:true,
                    height:450,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    layout:"fitColumns",
                    dataLoaded: function(){
                        var table = this;

                        var excludedNodeIds = [];

                        // Use getData() to walk through the plain JSON from server
                        function traverse(data) {
                            data.forEach(function(row) {
                                if (row.type === 'node' && row.is_excluded) {
                                    excludedNodeIds.push(row.id); // id like 'node-15401'
                                }

                                if (row._children && row._children.length > 0) {
                                    traverse(row._children);
                                }
                            });
                        }

                        var tableData = table.getData();
                        traverse(tableData);

                        // Now based on IDs you found, use getNestedRow
                        excludedNodeIds.forEach(function(nodeId){
                            // Collapse the node using getNestedRow function
                            var excludedRow = getNestedRow(table, nodeId);
                            if (excludedRow) {
                                excludedRow.treeCollapse();
                            }
                        });
                    },
                    columns:[
                        {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                            var cellData = cell.getData();

                            var description = cellData['description'];

                            if(cell.getData()['type'] === 'node'){
                                description = '<strong>'+description+'</strong>';
                            }
                            else if(cell.getData()['type'] === 'score' && cell.getData()['selected']){
                                description = '<strong>'+description+'</strong>';
                            }

                            return description;
                        }},
                        {title:"{{ trans('forms.notApplicable') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: function(rowData){
                                if(rowData.hasOwnProperty('id')){
                                    if(rowData['type'] === 'node' && rowData['depth'] > 0 && rowData['hasScores'])
                                    {
                                        var checked = rowData['is_excluded'] ? 'checked' : '';
                                        return '<input type="checkbox" '+checked+' disabled>';
                                    }
                                }
                            }
                        }},
                        {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                        {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false,
                            formatter: function(cell){
                                var rowData = cell.getData();
                                var value = rowData['selected'];
                                var tickIcon = '<svg enable-background="new 0 0 24 24" height="14" width="14" viewBox="0 0 24 24" xml:space="preserve"><path fill="#2DC214" clip-rule="evenodd" d="M21.652,3.211c-0.293-0.295-0.77-0.295-1.061,0L9.41,14.34c-0.293,0.297-0.771,0.297-1.062,0L3.449,9.351C3.304,9.203,3.114,9.13,2.923,9.129C2.73,9.128,2.534,9.201,2.387,9.351l-2.165,1.946C0.078,11.445,0,11.63,0,11.823c0,0.194,0.078,0.397,0.223,0.544l4.94,5.184c0.292,0.296,0.771,0.776,1.062,1.07l2.124,2.141c0.292,0.293,0.769,0.293,1.062,0l14.366-14.34c0.293-0.294,0.293-0.777,0-1.071L21.652,3.211z" fill-rule="evenodd"/></svg>';

                                if (rowData['type'] === 'score' && value) {
                                    var isExcluded = false;
                                    var parentRow = cell.getRow().getTreeParent();
                                    if (parentRow) {
                                        var parentData = parentRow.getData();
                                        isExcluded = parentData['is_excluded'];
                                    }

                                    if (isExcluded == true) {
                                        return '';
                                    } else {
                                        return value !== null ? tickIcon : '';
                                    }
                                }

                                return ''; // for non-score rows
                            }
                        },
                        {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: [
                                {
                                    show: function(cell){
                                        return cell.getData()['route:getDownloads'];
                                    },
                                    tag:'button',
                                    attributes: {
                                        type:'button',
                                        'class':'btn btn-xs btn-default',
                                        'data-action':'get-downloads'
                                    },
                                    rowAttributes: {'data-get-downloads':'route:getDownloads'},
                                    innerHtml:{
                                        tag:'i',
                                        attributes:{class:'fa fa-paperclip'}
                                    }
                                }
                            ]
                        }},
                    ],
                });

                var dsFormEvaluationLogTable = new Tabulator('#ds-form-evaluation-log-table', {
                    height: 450,
                    ajaxConfig: "GET",
                    ajaxFiltering: true,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    paginationSize: 100,
                    pagination: "remote",
                    layout: "fitColumns",
                    columns:[
                        { title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('digitalStar/digitalStar.actionBy') }}", field:'actionBy', minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true },
                        { title:"{{ trans('digitalStar/digitalStar.actionType') }}", field:'actionType', width: 200, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('digitalStar/digitalStar.actionDate') }}", field: 'actionDate', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    ]
                });

                var dsFormVerifierLogTable = new Tabulator('#ds-form-verifier-log-table', {
                    height:450,
                    ajaxConfig: "GET",
                    ajaxFiltering: true,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    paginationSize: 100,
                    pagination: "remote",
                    layout:"fitColumns",
                    columns:[
                        {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('users.name') }}", field:"name", minWidth:250, hozAlign:"left", headerSort:false, headerFilter:true},
                        { title:"{{ trans('verifiers.status') }}", field: 'approved', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                                innerHtml:function(rowData){
                                    if(rowData['approved'] === true){
                                        return "<span class='text-success'><i class='fa fa-thumbs-up'></i> <strong>{{ trans('verifiers.approved') }}</strong></span>";
                                    }
                                    else if(rowData['approved'] === false){
                                        return "<span class='text-danger'><i class='fa fa-thumbs-down'></i> <strong>{{ trans('verifiers.rejected') }}</strong></span>";
                                    }
                                    else{
                                        return "<span class='text-warning'><i class='fa fa-question'></i> <strong>{{ trans('verifiers.unverified') }}</strong></span>";

                                    }
                                }
                            }},
                        { title:"{{ trans('verifiers.verifiedAt') }}", field: 'verified_at', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('verifiers.remarks') }}", field: 'remarks', width: 240, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    ]
                });

                $('#ds-ratings-table').on('click', '[data-action^=show-]', function () {
                    var action = $(this).data('action').split('-')[1]; // "company" or "project"
                    var row = dsRatingsTable.getRow($(this).data('id'));

                    var config = {
                        company: {
                            table: dsCompanyScoreTable,
                            dataKey: 'route:company',
                            modalId: '#ds-company-score-modal'
                        },
                        project: {
                            table: dsProjectScoreTable,
                            dataKey: 'route:project',
                            modalId: '#ds-project-score-modal'
                        }
                    };

                    var current = config[action];
                    if (current) {
                        current.table.setData(row.getData()[current.dataKey]);
                        $(current.modalId + ' .modal-title').html(row.getData()['cycle']);
                        dsModalStack.push(current.modalId);
                    }
                });

                $('#ds-company-score-table, #ds-project-score-table').on('click', '[data-action=show-form]', function () {
                    var isProject = $(this).closest('#ds-project-score-table').length > 0;
                    var table = isProject ? dsProjectScoreTable : dsCompanyScoreTable;
                    var row = table.getRow($(this).data('id'));

                    dsFormEvaluationLogTable.setData(row.getData()['route:evaluation_log']);
                    dsFormVerifierLogTable.setData(row.getData()['route:verifier_log']);

                    $.get(row.getData()['route:form_info'], function (data) {
                        dsFormTable.setData(data['route:grid']);

                        // Show/hide project info
                        $('#ds-form-modal .project-info').toggle(isProject);
                        if (isProject) {
                            $('#ds-form-modal [data-name=project-reference]').html(data['reference']);
                            $('#ds-form-modal [data-name=project]').html(data['project']);
                        }

                        // Set common fields
                        $('#ds-form-modal [data-name=company]').html(data['company']);
                        $('#ds-form-modal [data-name=vendor_group]').html(data['vendor_group']);
                        $('#ds-form-modal [data-name=form_name]').html(data['form_name']);
                        $('#ds-form-modal [data-name=status]').html(data['status']);
                        $('#ds-form-modal [data-name=evaluator]').html(data['evaluator']);
                        $('#ds-form-modal [data-name=score]').html(data['score']);
                        $('#ds-form-modal [data-name=rating]').html(data['rating']);
                        $('#ds-form-modal [data-name=remarks]').html(data['evaluator_remarks']);

                        dsModalStack.push('#ds-form-modal');
                    });
                });

                $('#ds-form-modal').on('click', '[data-action=get-downloads]', function() {
                    dsModalStack.push('#downloadModal');
                });

                $('#ds-form-modal').on('click', '[data-action=show-evaluation-log]', function() {
                    dsModalStack.push('#ds-form-evaluation-log-modal');
                });

                $('#ds-form-modal').on('click', '[data-action=show-verifier-log]', function() {
                    dsModalStack.push('#ds-form-verifier-log-modal');
                });

                function getNestedRow(table, id)
                {
                    // hackish way of getting a nested row since tabulator doesn't have a method for it.
                    function traverseAndFind(rows, targetId)
                    {
                        var row, resultFromChildSearch;

                        for(var i in rows)
                        {
                            row = rows[i];

                            if(row.getData()['id'] == targetId) return row;

                            if(row.getData()['hasScores']) continue;

                            resultFromChildSearch = traverseAndFind(row.getTreeChildren(), targetId);

                            if(resultFromChildSearch) return resultFromChildSearch;
                        }

                        return false;
                    }

                    return traverseAndFind(table.getRows(), id);
                }
            });
        </script>
    @endif
@endif