@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('projects.subPackages') }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-gift"></i> {{{ trans('projects.subPackages') }}}
            </h1>
        </div>

        @if ($currentUser->hasCompanyRoles([PCK\ContractGroups\Types\Role::PROJECT_OWNER]) and $currentUser->isGroupAdmin())
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <a href="{{route('projects.subPackages.create', array($project->id))}}" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
                </a>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('projects.subPackages') }}} </h2>
                </header>
                <div class="no-padding">
                    <div class="widget-body">
                        <div id="subPackages-table" class="tabulator-no-border"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script type="text/javascript">
        Tabulator.prototype.extendModule("format", "formatters", {
            textarea:function(cell, formatterParams){
                cell.getElement().style.whiteSpace = "pre-wrap";
                var obj = cell.getRow().getData();
                var str = '<a href="'+obj.route_show+'" class="plain">'
                    + '<div class="well">' +this.sanitizeHTML(obj.title)+ '</div>'
                    + '<p style="padding-top:4px;">'
                    + '<span class="label label-success">'
                    + this.sanitizeHTML(obj.created_at)
                    + '</span>'
                    + '&nbsp;'
                    + '<span class="label label-info">'
                    + this.sanitizeHTML(obj.country) + ', ' + this.sanitizeHTML(obj.state)
                    + '</span>'
                    + '&nbsp;'
                    + '<span class="label label-warning" data-toggle="tooltip" title="{{ trans('projects.thisIsASubPackage') }}" data-placement="right">'
                    + '<i class="fa fa-gift"></i>'
                    + '</span>'
                    +'</p></a>';
                return this.emptyToSpace(str);
            }
        });
        $(document).ready(function() {
            var tbl = new Tabulator("#subPackages-table", {
                ajaxURL: "{{ route('projects.subPackages.list.ajax', [$project->id]) }}",
                ajaxConfig:"get",
                layout:"fitColumns",
                placeholder: "{{ trans('general.noMatchingResults') }}",
                fillHeight:true,
                tooltips:true,
                resizableColumns:false,
                columns: [
                    {title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 20, headerSort:false, formatter:"rownum"},
                    {title:"{{ trans('projects.reference') }}", field: 'reference', cssClass:"text-center text-middle", width: 180, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('projects.name') }}", field: 'title', cssClass:"auto-width text-left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
                    {title:"{{ trans('projects.status') }}", field: 'status', cssClass:"text-center text-middle", width: 160, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    {title:"{{ trans('general.actions') }}", cssClass:"text-center text-middle", width: 120, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: [{
                                tag: 'a',
                                attributes: {'class': 'btn btn-sm btn-primary', 'title': '{{ trans("general.view") }}'},
                                rowAttributes: {'href': 'route_show'},
                                innerHtml: function(){
                                    return '<i class="fa fa-sign-in-alt"></i>';
                                }
                            },
                            { innerHtml: function(){ return '&nbsp'; } }
                            @if($currentUser->isSuperAdmin()),{
                                tag: 'a',
                                attributes: {'title': '{{ trans("forms.delete") }}', 'class': 'btn btn-sm btn-danger', 'data-method': 'delete', 'data-csrf_token': '{{{ csrf_token() }}}'},
                                rowAttributes: {'href': 'route_delete'},
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
        });
    </script>
@endsection