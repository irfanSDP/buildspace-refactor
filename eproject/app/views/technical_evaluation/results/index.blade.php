@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('technicalEvaluation.technicalEvaluationResults') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <?php $latestTenderId = $project->latestTender->id; ?>

    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h1 class="page-title txt-color-blueDark">
                {{ trans('technicalEvaluation.technicalEvaluation') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('technicalEvaluation.tenders') }} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="datatable_fixed_column">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="text-align: center; width: 20%;">Reference</th>
                                    <th style="text-align: center; width: 20%;">No. of Completed Tenderers</th>
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
                                            <td style="text-align: center;">{{ link_to_route('technicalEvaluation.results.show', $tender->current_tender_name, array($project->id, $tender->id)) }}</td>
                                            <td style="text-align: center;">{{{ $completedCompanies[$tender->id] }}}</td>
                                            <td style="text-align: center;">{{{ $tender->project->getProjectTimeZoneTime($tender->technical_tender_closing_date) }}}</td>
                                            <td style="text-align: center;">
                                                @if ( $tender->technicalEvaluationIsSubmitted() )
                                                    {{ link_to_route('technicalEvaluation.results.verifiers.logs', trans('technicalEvaluation.viewLog'), array($tender->project_id, $tender->id)) }}
                                                @elseif ( $tender->id === $latestTenderId && !$tender->technicalEvaluationIsSubmitted() && !$project->onPostContractStages() && $currentUser->isEditor($project) && $currentUser->hasCompanyProjectRole($project, \PCK\Filters\TechnicalEvaluationFilters::editorRoles($project)))
                                                    {{ link_to_route('technicalEvaluation.results.verifiers.form', $tender->technical_evaluation_verifying_status, array($project->id, $tender->id)) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td style="text-align: center;">{{{ $tender->technical_evaluation_status_text }}}</td>
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
        });</script>
@endsection