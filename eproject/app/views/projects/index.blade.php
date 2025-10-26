@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{{ trans('projects.projects') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-table"></i> {{{ trans('projects.projects') }}}
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 mb-4">
        @if ($user->isProjectCreator())
        <a href="{{route('projects.create')}}" class="btn btn-primary pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('projects.addNew') }}}
        </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2> {{{ trans('projects.projects') }}} </h2>
            </header>
            <div class="widget-body">
                <div class="smart-form" data-options="filter-options">
                    <section>
                        <div class="inline-group">
                            <div class ="row">
                                <section class="col col-xs-6 col-md-6 col-lg-6">
                                    <label class="checkbox">
                                    <input type="checkbox" name="projects" checked>
                                    <i></i>{{ trans('projects.projects') }}
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="subProjects" {{{ $user->hasCompanyRoles([\PCK\ContractGroups\Types\Role::CONTRACTOR]) ? 'checked' : '' }}}>
                                        <i></i>{{ trans('projects.subProjects') }}
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="openTender">
                                        <i></i>{{{ trans('openTender.openTender') }}}
                                    </label>
                                </section>
                                <section class="col col-xs-6 col-md-6 col-lg-6">
                                    <label class="control-label" for="subsidiaryFilter"><strong>{{ trans('subsidiaries.filterBySubsidiary') }}</strong></label>&nbsp;&nbsp;
                                    <select class="form-control select2" id="subsidiaryFilter" data-action="filter" data-select-width="80%">
                                        <option value="">{{ trans('forms.none') }}</option>
                                        @foreach ($subsidiaries as $subsidiaryId => $subsidiaryName)
                                        <option value="{{{ $subsidiaryId }}}">{{{ $subsidiaryName }}}</option>
                                        @endforeach
                                    </select>
                                </section>
                            </div>
                        </div>
                    </section>
                </div>
                <div id="projects-table"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script type="text/javascript">
        Tabulator.prototype.extendModule("format", "formatters", {
            textarea:function(cell, formatterParams){
                cell.getElement().style.whiteSpace = "pre-wrap";
                var obj = cell.getRow().getData();
                var str = '<a href="'+obj["route:projects.show"]+'" class="plain">'
                    + '<div class="well">' +this.sanitizeHTML(obj.projectTitle)+ '</div>'
                    + '<p style="padding-top:4px;">'
                    + '<span class="label label-success">'
                    + this.sanitizeHTML(obj.projectCreatedAt)
                    + '</span>'
                    + '&nbsp;'
                    + '<span class="label label-info">'
                    + this.sanitizeHTML(obj.country) + ', ' + this.sanitizeHTML(obj.state)
                    + '</span>'
                    + '&nbsp;'
                    + '<span class="label label-warning">'
                    + this.sanitizeHTML(obj.contractName)
                    + '</span>'
                    +'</p></a>';
                return this.emptyToSpace(str);
            }
        });

        $(document).ready(function() {
            var projectsTable = new Tabulator("#projects-table", {
                ajaxURL: "{{ route('projects.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                placeholder: "{{ trans('general.noMatchingResults') }}",
                fillHeight: true,
                tooltips:true,
                resizableColumns:false,
                columns: [
                    {title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 60, headerSort:false, field:'counter'},
                    {title:"{{ trans('projects.reference') }}", field: 'reference', cssClass:"text-center text-middle", width: 180, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('projects.name') }}", field: 'projectTitle', cssClass:"auto-width text-left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
                    {title:"{{ trans('projects.status') }}", field: 'projectStatus', cssClass:"text-center text-middle", width: 140, headerSort:false, headerFilterPlaceholder: "{{ trans('general.filter') }}", editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($projectStatuses) }} },
                    {title:"{{ trans('general.actions') }}", cssClass:"text-center text-middle", width: 180, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: [{
                                tag: 'a',
                                attributes: {'class': 'btn btn-sm btn-primary', 'title': '{{ trans("general.view") }}'},
                                rowAttributes: {'href': 'route:projects.show'},
                                innerHtml: function(){
                                    return '<i class="fa fa-sign-in-alt"></i>';
                                }
                            },{
                                innerHtml: function(){ return '&nbsp'; }
                            },{
                                tag: 'span',
                                attributes: {'title': '{{ trans("projects.subPackages") }}'},
                                innerHtml: function(rowData){
                                    return (!rowData.isSubProject) ? '<a href="'+rowData['route:projects.subPackages.index']+'" class="btn btn-sm btn-success"><i class="fa fa-gift"></i> '+rowData.subProjectCount+'</a>' : "";
                                }
                            },{
                                innerHtml: function(){ return '&nbsp'; }
                            }
                            @if($user->isSuperAdmin()),{
                                tag: 'a',
                                attributes: {'title': '{{ trans("forms.delete") }}', 'class': 'btn btn-sm btn-danger', 'data-method': 'delete', 'data-csrf_token': '{{{ csrf_token() }}}'},
                                rowAttributes: {'href': 'route:projects.delete'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {'class': 'fa fa-trash'}
                                }
                            }
                            @endif
                            ]
                        }
                    }
                ]
            });

            var selectedSubsidiaryId = $('#subsidiaryFilter').val();
            manageSubsidiaryFilter(selectedSubsidiaryId, projectsTable, false);//to set grid filter when first time page loaded

            $('[data-options=filter-options] input[type=checkbox]').each(function(idx){//to set grid filter when first time page loaded
                manageCustomCheckboxFilters($(this), projectsTable, false);
            });

            function subsidiaryFilter(data, filterParams){
                var treeNodeIds = treeFx.getBranchKeys(treeFx.getBranch(webClaim.subsidiariesTree, filterParams.selectedId));

                treeNodeIds.push(filterParams.selectedId);

                return arrayFx.inArray(treeNodeIds, data.subsidiaryId.toString());
            }

            $('#subsidiaryFilter').on('change', function(){
                manageSubsidiaryFilter($(this).val(), projectsTable, true);
            });

            $('[data-options=filter-options] input[type=checkbox]').on('change', function(){
                manageCustomCheckboxFilters($(this), projectsTable, true);
            });

            function manageSubsidiaryFilter(selectedSubsidiaryId, tabulatorGrid, toRemoveFilter){
                if(toRemoveFilter){
                    app_tabulator_utilities.removeCustomFilter(tabulatorGrid, 'subsidiaryId');
                }

                if(selectedSubsidiaryId && parseInt(selectedSubsidiaryId) > 0) {
                    tabulatorGrid.addFilter('subsidiaryId', "=", selectedSubsidiaryId);
                }
            }

            function manageCustomCheckboxFilters(elem, tabulatorGrid, toRemoveFilter){
                var fieldName;

                switch(elem.prop('name')){
                    case 'projects':
                        fieldName = 'isMainProject';
                        break;
                    case 'subProjects':
                        fieldName = 'isSubProject';
                        break;
                    case 'openTender':
                        fieldName = 'isOpenTender';
                        break;
                }

                if(toRemoveFilter){
                    app_tabulator_utilities.removeCustomFilter(tabulatorGrid, fieldName);
                }
                
                if(elem.prop('checked')){
                    tabulatorGrid.addFilter(fieldName, '=', 1);
                }
            }
        });
    </script>
@endsection