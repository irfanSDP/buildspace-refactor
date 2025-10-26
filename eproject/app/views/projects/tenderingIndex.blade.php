@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('navigation/projectnav.submitTender') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                {{ trans('navigation/projectnav.submitTender') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('navigation/projectnav.submitTender') . ' ' . trans('tenders.listing') }} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="datatable_fixed_column">
                                <thead>
                                <tr>
                                    <th style="width:18px;">{{ trans('tenders.no') }}</th>
                                    <th style="text-align: center; vertical-align: middle;">{{ trans('tenders.reference') }}</th>
                                    <th style="text-align: center; vertical-align: middle;width:160px;">{{ trans('tenders.closingDate') }}</th>
                                    <th style="text-align: center; vertical-align: middle;width:260px;">{{ trans('tenders.status') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if ( $contractor->tenders->count() > 0 )
                                    <?php $counter = 1; ?>
                                    @foreach ( $contractor->tenders as $tender )
                                        <tr>
                                            <td class="text-middle text-center">
                                                <?php echo $counter ++; ?>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">{{ link_to_route('projects.submitTender.rates', $tender->current_tender_name, array($project->id, $tender->id)) }}</td>
                                            <td style="text-align: center; vertical-align: middle;">{{{ $tender->project->getProjectTimeZoneTime($tender->tender_closing_date) }}}</td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                @if ( $tender->pivot->tenderSubmissionIsComplete() )
                                                    <strong class="text-success">{{ trans('tenders.submitted') }}</strong><br>
                                                    {{{ $tender->project->getProjectTimeZoneTime($tender->pivot->submitted_at) }}}
                                                @else
                                                <div class="well text-left text-middle">
                                                    @foreach($tender->pivot->getSubmitTenderChecklist() as $itemName => $isSubmitted)
                                                        @if($isSubmitted)
                                                            <i class="fa fa-check text-success"></i> {{{ $itemName }}}
                                                        @else
                                                            <i class="fa fa-times-circle text-warning"></i> {{{ $itemName }}}
                                                        @endif
                                                        <br/>
                                                    @endforeach
                                                    <hr/>
                                                    <strong class="text-warning">{{ trans('tenders.submissionIncomplete') }}</strong>
                                                </div>
                                                @endif
                                            </td>
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
                "language" : {
                    "paginate": {
                        "previous": "{{ trans('general.previous') }}",
                        "next": "{{ trans('general.next') }}",
                    },
                },
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