@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('contracts.contracts') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-list"></i> {{ trans('contracts.contracts') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{ trans('contracts.contracts') }} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table ">
                                <thead>
                                <tr>
                                    <th class="text-middle text-center text-nowrap squeeze">{{ trans('tables.no') }}</th>
                                    <th class="text-middle text-center text-nowrap">{{ trans('tables.name') }}</th>
                                    <th class="text-middle text-center text-nowrap squeeze">{{ trans('contracts.clauses') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $counter = 0; ?>
                                @foreach ($contracts as $contract)
                                    <tr>
                                        <td class="text-middle text-center text-nowarp squeeze">
                                            {{{ ++$counter }}}
                                        </td>
                                        <td class="text-middle text-center text-nowarp">
                                            {{{ $contract->name }}}
                                        </td>
                                        <td class="text-middle text-center text-nowarp squeeze">
                                            <a href="{{ route('clauses', array($contract->id)) }}" class="btn btn-default btn-xs">{{ trans('tables.view') }}</a>
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
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#dt_basic').dataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : true
            });
        });
    </script>
@endsection