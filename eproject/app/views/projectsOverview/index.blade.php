@extends('layout.main', array('hide_ribbon'=>true))

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fas fa-chart-pie"></i> {{{ trans('projectsOverview.projectsOverview') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <div class="btn-group pull-right header-btn">
                @include('projects.partials.index_actions_menu', array('classes' => 'pull-right'))
            </div>
        </div>
    </div>

    @include('projects.partials.dashboard_master')

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('projects.projects') }}} </h2>
                </header>
                <div class="no-padding">
                    <div class="widget-body">
                        <div class="form-horizontal" data-options="filter-options">
                            <div class="row bg-grey-e">
                                <div class="col-sm-4 text-right">
                                    <label class="checkbox">
                                        <input type="checkbox" name="projects" checked> {{ trans('projects.projects') }}
                                    </label>
                                </div>
                                <div class="col-sm-2 text-right">
                                    <label class="checkbox">
                                        <input type="checkbox" name="subProjects"> {{ trans('projects.subProjects') }}
                                    </label>
                                </div>
                                <label class="control-label col-sm-2 text-right" for="subsidiaryFilter"><strong>{{ trans('subsidiaries.filterBySubsidiary') }}</strong></label>
                                <div class="col-sm-4" style="padding-top:4px;padding-bottom:4px;">
                                    <select class="form-control select2" id="subsidiaryFilter" data-action="filter">
                                        <option value="">{{ trans('forms.none') }}</option>
                                        @foreach($subsidiaries->sortBy('name') as $subsidiary)
                                            <option value="{{{ $subsidiary->name }}}">{{{ $subsidiary->name }}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <table class="table  table-hover" id="dt_basic">
                            <thead>
                            <tr>
                                <th style="width: 5%;">{{{ trans('projects.no') }}}</th>
                                <th style="width: 15%;">{{{ trans('projects.reference') }}}</th>
                                <th style="width: auto;">{{{ trans('projects.name') }}}</th>
                                <th style="width: 15%;" class="text-center">{{{ trans('projects.status') }}}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    @include('projects.partials.js_partials.js_dashboard_master')
    <script>
        $(document).ready(function() {

            var table = $('#dt_basic').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : false,
                bServerSide:true,
                "sAjaxSource":"{{ route('projectsOverview.data') }}",
                "drawCallback": function(){
                    $('[data-toggle=tooltip]').tooltip({
                        trigger: 'hover'
                    });
                },
                "fnServerParams": function ( aoData ) {
                    aoData.push( { name: 'subsidiaryName', value: $('[data-action=filter]').val() } );
                    aoData.push( { name: 'includeProjects', value: $('[data-options=filter-options] input[type=checkbox][name=projects]').prop('checked') } );
                    aoData.push( { name: 'includeSubProjects', value: $('[data-options=filter-options] input[type=checkbox][name=subProjects]').prop('checked') } );
                },
                "aoColumnDefs": [
                    {
                        "aTargets": [ 0 ],
                        "mData": function ( source, type, val ) {
                            return source['indexNo'];
                        },
                        "sClass": "text-middle text-center"
                    },
                    {
                        "aTargets": [ 1 ],
                        "mData": function ( source, type, val ) {
                            return '<span class="monospace">'+source['reference']+'</span>';
                        },
                        "sClass": "text-middle text-center nowrap"
                    },
                    {
                        "aTargets": [ 2 ],
                        "mData": function ( source, type, val ) {
                            var displayData =
                                    '<div class="row">'
                                    + '<div class="col-md-9 col-lg-9 col-xs-9">'
                                    + '<div class="well"><h5 data-toggle="tooltip" title="'+source['projectTitle']+'" data-placement="bottom">'
                                    + source['projectShortTitle']
                                    + '</h5></div>'
                                    + '<p class="padded-top">'
                                    + '<span class="label label-success">'
                                    + source['projectCreatedAt']
                                    + '</span>'
                                    + '&nbsp;'
                                    + '<span class="label label-info">'
                                    + source['country'] + ', ' + source['state']
                                    + '</span>'
                                    + '&nbsp;'
                                    + '<span class="label label-warning">'
                                    + source['contractName']
                                    + '</span>';
                            if(source['isSubPackage'])
                            {
                                displayData += '&nbsp;<span class="label bg-color-greenDark" data-toggle="tooltip" title="{{ trans('projects.thisIsASubPackage') }} [' + source[ 'parentProjectTitle' ] + ': ' + source['parentProjectReference'] + ']" data-placement="right"><i class="fa fa-gift"></i></span>';
                            }
                            displayData += '</p>'
                                + '</div>'
                                + '<div class="col-md-3 col-lg-3 col-xs-3">'
                                + '<div class="btn-group btn-group-xs">';

                            if(!source['isSubPackage'])
                            {
                                displayData += '<button class="btn bg-color-greenDark txt-color-white margined-top"><strong>{{ trans('projects.subPackages') }} ['+source['subPackagesCount']+']</strong></button>';
                            }

                            displayData += '</div>'
                                + '</div>'
                                + '</div>';
                            return displayData;
                        },
                        "sClass": "text-middle"
                    },
                    {
                        "aTargets": [ 3 ],
                        "mData": function ( source, type, val ) {
                            return '<strong>'+source['projectStatus']+'</strong>';
                        },
                        "sClass": "text-middle text-center"
                    }
                ]
            });

            $('[data-action=filter],[data-options=filter-options] input[type=checkbox]').change( function() {
                table.draw();
            } );

        });
    </script>
@endsection