@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('scheduledMaintenance.scheduled_maintenances') }}</li>
    </ol>

@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-lg fa-cogs"></i></i>&nbsp;&nbsp;{{ trans('scheduledMaintenance.scheduled_maintenances') }}
        </h1>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
    	<a href="{{ route('scheduled_maintenance.create')}}">
	        <button id="createDefect" class="btn btn-primary btn-md pull-right header-btn">
	            <i class="fa fa-plus"></i> {{ trans('scheduledMaintenance.create_scheduled_maintenance') }}
	        </button>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{ trans('scheduledMaintenance.scheduled_maintenance_list') }} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dt_basic">
                            <thead>
                                <tr>
                                    <th class="text-center text-middle">{{ trans('scheduledMaintenance.no') }}</th>
                                    <th>
                                        {{ trans('scheduledMaintenance.message') }}
                                        <input type="text" class="form-control" placeholder="Filter Message"/>
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('scheduledMaintenance.image') }}
                                        <input type="text" class="form-control" placeholder="Filter Image" />
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('scheduledMaintenance.start_time') }}
                                        <input type="text" class="form-control" placeholder="Filter Start Time" />
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('scheduledMaintenance.end_time') }}
                                        <input type="text" class="form-control" placeholder="Filter End Time" />
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('scheduledMaintenance.status') }}
                                        <input type="text" class="form-control" placeholder="Filter Status" />
                                    </th>
                                    <th class="text-center text-middle">{{ trans('scheduledMaintenance.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($scheduledMaintenances as $scheduledMaintenance)
                                    <tr>
                                        <td class="text-center text-middle">
                                            {{++$count}}
                                        </td>
                                        <td>
                                           <a>
                                           	    {{$scheduledMaintenance->message}}
                                           </a>
                                        </td>
                                        <td>
                                           <a>
                                           	    {{$scheduledMaintenance->image}}
                                           </a>
                                        </td>
                                        <td class="text-center text-middle">
                                           <a>
                                           	    {{$scheduledMaintenance->start_time}}
                                           </a>
                                        </td>
                                        <td class="text-center text-middle">
                                           <a>
                                           	    {{$scheduledMaintenance->end_time}}
                                           </a>
                                        </td>
                                        <td class="text-center text-middle">
                                           <a>
                                           {{ $scheduledMaintenance->is_under_maintenance ? '<span class="label label-info">Active</span>' : '<span class="label label-success">Deactivate</span>' }}
                                           </a>
                                        </td>
                                        <td class="text-center text-middle" style="white-space: nowrap;">
                                            <a class="btn btn-xs btn-primary" href="{{ route('scheduled_maintenance.edit', array($scheduledMaintenance->id)) }}" style="margin-right: 5px;"><i class="fa fa-pencil-alt"></i></a>
                                            <a href="{{ route('scheduled_maintenance.delete', array($scheduledMaintenance->id)) }}" 
                                               class="btn btn-xs btn-danger" data-method="delete"
                                               data-csrf_token="{{ csrf_token() }}"><i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var otable = $('#dt_basic').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : false
            });

            $("#dt_basic thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            } );
        });
    </script>
    
@endsection