@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('technicalEvaluation.sets', trans('technicalEvaluation.technicalEvaluation'), array()) }}</li>
        <li>{{{ trans('contractLimit.contractLimit') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-list"></i> {{{ trans('contractLimit.contractLimit') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('contractLimit.create') }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>
                        {{{ trans('contractLimit.contractLimit') }}}
                    </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="item_table">
                                <thead>
                                <tr>
                                    <th style="width:5%" class="text-center">{{{ trans('general.no') }}}</th>
                                    <th style="width:auto" class="text-center">{{{ trans('contractLimit.limit') }}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $index = 0; ?>
                                @foreach ($contractLimits as $contractLimit)
                                    <tr>
                                        <td class="text-center">{{{ ++$index }}}</td>
                                        <td class="text-left">
                                            <a href="{{{ route('contractLimit.edit', array($contractLimit->id)) }}}">
                                                {{{ $contractLimit->limit }}}
                                            </a>

                                            <a href="{{{ route('contractLimit.destroy', array($contractLimit->id)) }}}"
                                               class="pull-right btn btn-xs btn-danger delete-button"
                                               data-method="delete"
                                               data-csrf_token="{{{ csrf_token() }}}">
                                                <i class="fa fa-trash"></i>
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
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        $('#item_table').dataTable({
            "sDom": "t",
            "bPaginate": false,
            "autoWidth": true
        });
    </script>
@endsection