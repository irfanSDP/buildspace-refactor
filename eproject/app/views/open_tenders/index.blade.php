@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('navigation/projectnav.openTender') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <?php $latestTenderId = $project->latestTender->id; ?>

    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h1 class="page-title txt-color-blueDark">
                {{ trans('navigation/projectnav.openTender') }}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="btn-group pull-right header-btn">
                @include('open_tenders.partials.index_actions_menu', array('classes' => 'pull-right'))
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('navigation/projectnav.openTender') }} Listing </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="datatable_fixed_column">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="text-align: center; width: 20%;">Reference</th>
                                    <th style="text-align: center; width: 20%;">No. of Submitted Tender</th>
                                    <th style="text-align: center;">Closing Date</th>
                                    <th style="text-align: center;">Verifier</th>
                                    <th style="text-align: center;">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if ( $tenders->count() > 0 )
                                    <?php $counter = 1; ?>
                                    @foreach ( $tenders as $tender )
                                        <tr>
                                            <td class="text-middle text-center">
                                                <?php echo $counter ++; ?>
                                            </td>
                                            <td style="text-align: center;">{{ link_to_route('projects.openTender.show', $tender->current_tender_name, array($project->id, $tender->id)) }}</td>
                                            <td style="text-align: center;">{{{ $tender->submittedTenderRateContractors->count() }}}</td>
                                            <td style="text-align: center;">{{{ $tender->project->getProjectTimeZoneTime($tender->tender_closing_date) }}}</td>
                                            <td style="text-align: center;">
                                                @if ( $tender->isTenderOpen() )
                                                    {{ link_to_route('projects.openTender.viewOTVerifierLogs', trans('tenders.viewLog'), array($tender->project_id, $tender->id)) }}
                                                @elseif ( $tender->id === $latestTenderId && !$tender->isTenderOpen() && !$project->onPostContractStages() && $currentUser->hasCompanyProjectRole($project, PCK\Filters\OpenTenderFilters::editorRoles($project)) && $currentUser->isEditor($project))
                                                    {{ link_to_route('projects.openTender.assignOTVerifiers', $tender->open_tender_verifying_status, array($project->id, $tender->id)) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td style="text-align: center;">{{{ $tender->open_tender_status_text }}}</td>
                                        </tr>
                                    @endforeach
                                @endif
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