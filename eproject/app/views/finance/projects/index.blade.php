@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('finance.claim-certificate', trans('navigation/mainnav.financeModule'), []) }}</li>
        <li>{{ trans('finance.accountCodeSettings') }}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-cogs"></i> {{ trans('finance.accountCodeSettings') }}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <div class="btn-group pull-right header-btn">
                @include('finance.projects.partials.index_action_menu')
            </div>
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
                                <label class="control-label"><strong>{{ trans('subsidiaries.filterBySubsidiary') }}</strong></label>
                                <select id="subsidiaryFilter" class="form-control select2" data-action="filter" data-select-width="52%"> 
                                    <option value="">{{ trans('forms.none') }}</option>
                                    @foreach($subsidiaries as $subsidiaryId => $subsidiaryName)
                                        <option value="{{{ $subsidiaryId }}}">{{{ $subsidiaryName }}}</option>
                                    @endforeach
                                </select>
                                <label class="control-label"><strong>{{ trans("general.status") }}</strong></label>
                                <select id="statusFilter" class="form-control select2" data-action="filter" data-select-width="28%">
                                    <option value="">{{ trans('forms.none') }}</option>
                                    @foreach($statuses as $statusId => $statusText)
                                        <option value="{{{ $statusId }}}">{{{ $statusText }}}</option>
                                    @endforeach
                                </select>
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
    <script type="text/javascript">
        Tabulator.prototype.extendModule("format", "formatters", {
            textarea:function(cell, formatterParams){
                cell.getElement().style.whiteSpace = "pre-wrap";
                var obj = cell.getRow().getData();
                var str = '<a href="'+obj.route_show+'" class="plain">'
                    + this.sanitizeHTML(obj.projectTitle)
                    + '</a>';
                return this.emptyToSpace(str);
            }
        });

        $(document).ready(function() {
            var projectsTable = null;
            var projectsTableUrl = "{{ route('finance.account.code.settings.projects.list.get') }}";
            var subsidiaryId = null;
            var statusId = null;

            var statusColumnFormatter = function(cell, formatterParams, onRendered) {
                var backgrounColor = null;

                switch(cell.getRow().getData().status) {
                    case {{{ \PCK\AccountCodeSettings\AccountCodeSetting::STATUS_OPEN }}}:
                        backgrounColor = '#81DAF5';
                        break;
                    case {{{ \PCK\AccountCodeSettings\AccountCodeSetting::STATUS_PENDING_FOR_APPROVAL }}}:
                        backgrounColor = '#FDCF8D';
                        break;
                    case {{{ \PCK\AccountCodeSettings\AccountCodeSetting::STATUS_APPROVED }}}:
                        backgrounColor = '#A9F5A9';
                        break;
                }

                var statusLabel = document.createElement('label');
                statusLabel.innerHTML = cell.getRow().getData().statusText;

                cell.getElement().style.backgroundColor = backgrounColor;

                return statusLabel;
            }

            var columns = [
                { title: "{{ trans('general.no') }}", field: 'indexNo', width: 60, 'align': 'center', headerSort:true },
                { title: "{{ trans('projects.reference') }}", field: 'projectReference', width: 150, headerSort:false, headerFilter: 'input', headerFilterPlaceHolder: 'filter', },
                { title: "{{ trans('projects.title') }}", field: 'projectTitle', headerSort:false, headerFilter: 'input', headerFilterPlaceHolder: 'filter', formatter: "textarea" },
                { title: "{{ trans('general.status') }}", field: 'statusText', width: 150, cssClass:"text-center", headerSort: false, formatter: statusColumnFormatter },
                { title: "{{ trans('finance.company') }}", field: 'subsidiary', width: 300, headerFilter: 'input', headerFilterPlaceHolder: 'filter', headerSort:false },
            ];

            projectsTable = new Tabulator('#projects-table', {
                height:420,
                columns: columns,
                layout:"fitColumns",
                ajaxURL: projectsTableUrl,
                ajaxConfig: "GET",
                movableColumns:true,
                placeholder:"No Data Available",
                columnHeaderSortMulti:false,
            });

            $('[data-action=filter]').on('change', function() {
                switch(this.id)
                {
                    case subsidiaryFilter.id:
                        subsidiaryId = this.options[this.selectedIndex].value != "" ? this.options[this.selectedIndex].value : null;
                        break;
                    case statusFilter.id:
                        statusId = this.options[this.selectedIndex].value != "" ? this.options[this.selectedIndex].value : null;
                        break;
                    default:
                        console.log('An error has occured.');
                }

                var params = {
                    subsidiaryId: subsidiaryId,
                    statusId: statusId,
                };

                projectsTable.setData(projectsTableUrl, params);
            });
        });
    </script>
@endsection