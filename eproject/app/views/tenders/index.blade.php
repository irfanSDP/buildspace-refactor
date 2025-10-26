@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('navigation/projectnav.tenders') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                {{ trans('tenders.tenders') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('tenders.tenders') }} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="datatable_fixed_column">
                                <thead>
                                    <tr>
                                        <th style="width:20px;">{{ trans('tenders.no') }}</th>
                                        <th style="text-align: center;">{{ trans('tenders.reference') }}</th>
                                        <th style="text-align: center;width:200px;">{{ trans('tenders.status') }}</th>
                                        <th style="text-align: center;width:200px;">{{ trans('tenders.formOfTender') }}</th>
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
                                            <td class="text-center" style="vertical-align: middle">{{ link_to_route('projects.tender.show', $tender->current_tender_name, array($project->id, $tender->id)) }}</td>
                                            <td class="text-center" style="vertical-align: middle;">{{{ $tender->current_form_type_name }}}</td>
                                            <td class="text-center">
                                                <a href="{{ route('form_of_tender.edit', array($project->id, $tender->id)) }}" class="btn btn-sm btn-info">{{ trans('tenders.show') }}</a>
                                                <a href="{{ route('form_of_tender.print', array($project->id, $tender->id)) }}" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-lg fa-fw fa-print"></i> {{ trans('tenders.print') }}</a>
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