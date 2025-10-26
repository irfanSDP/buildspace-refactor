@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('architectInstructions.architectInstruction') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                {{ trans('architectInstructions.architectInstruction') }}
            </h1>
        </div>

        @if ( \PCK\ProjectModulePermission\ProjectModulePermission::isAssigned($project, $currentUser, \PCK\ProjectModulePermission\ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION) )
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                {{ link_to_route('indonesiaCivilContract.architectInstructions.create', trans('architectInstructions.issueNewArchitectInstruction'), $project->id, array('class' => 'btn btn-primary btn-md pull-right header-btn')) }}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('architectInstructions.architectInstructions') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="datatable_fixed_column">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter AI Name" />
                                    </th>
                                    <th class="hasinput">
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Status" />
                                    </th>
                                    <th class="hasinput">
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{ trans('tables.no') }}</th>
                                    <th class="text-middle text-left text-nowrap">{{ trans('architectInstructions.ai') }}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{ trans('architectInstructions.dateIssued') }}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{ trans('architectInstructions.status') }}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{ trans('architectInstructions.dateToComply') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $counter = 0; ?>
                                @foreach ( $ais as $ai )
                                    <tr>
                                        <td class="text-middle text-center text-nowrap squeeze"><?php echo ++$counter; ?></td>
                                        <td class="text-middle text-left">{{ link_to_route('indonesiaCivilContract.architectInstructions.show', $ai->reference, array($project->id, $ai->id)) }}</td>
                                        <td class="dateSubmitted text-middle text-center text-nowrap squeeze">{{{ $project->getProjectTimeZoneTime($ai->created_at) }}}</td>
                                        <td class="text-middle text-center text-nowrap squeeze">{{{ $ai->statusText }}}</td>
                                        <td class="text-middle text-center text-nowrap squeeze">{{{ $ai->project->getProjectTimeZoneTime($ai->deadline_to_comply) ?? '-' }}}</td>
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
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var otable = $('#datatable_fixed_column').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : false
            });

            $("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            } );
        });
    </script>
@endsection