@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('tenderDocumentFolders.tenderDocumentFolders') }}</li>
        <li>{{ trans('general.templates') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-folder"></i> {{{ trans('tenderDocumentFolders.tenderDocumentFoldersTemplate') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('tender_documents.template.set.create') }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('tenderDocumentFolders.addTemplate') }}}
            </a>
        </div>
    </div>

    <div class="jarviswidget ">
        <header>
            <h2>{{ trans('tenderDocumentFolders.tenderDocumentFoldersTemplate') }}</h2>
        </header>
        <!-- widget div-->
        <div>

            <!-- widget content -->
            <div class="widget-body">
                <div class="table-responsive">
                    <table class="table  datatable">
                        <thead>
                            <tr>
                                <th class="text-center text-middle" style="width:40px;">{{ trans('general.no') }}</th>
                                <th class="text-middle">{{ trans('workCategories.workCategory') }}</th>
                                <th class="text-center text-middle" style="width:120px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 0; ?>
                            @foreach($rootFolders as $item)
                                <tr>
                                    <td class="text-center text-middle squeeze">{{{ ++$count }}}</td>
                                    <td>
                                        <a href="{{ route('tender_documents.template.index', array($item->id)) }}" class="plain">
                                            {{{ $item->name }}} {{{ $item->serial_number }}}
                                        </a>
                                    </td>
                                    <td class="text-center text-middle">
                                        <a href="{{ route('tender_documents.template.index', array($item->id)) }}"
                                           class="btn btn-xs btn-default">
                                            <i class="fas fa-pen-square"></i> {{ trans('forms.edit') }}
                                        </a>
                                        <a href="{{{ route('tender_documents.template.set.delete', array($item->id)) }}}"
                                           class="btn btn-xs btn-danger delete-button"
                                           data-id="{{{ $item->id }}}"
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
            <!-- end widget content -->

        </div>
        <!-- end widget div -->

    </div>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $('.datatable').dataTable({
            "bSort": false,
            "autoWidth": true
        });
    </script>
@endsection