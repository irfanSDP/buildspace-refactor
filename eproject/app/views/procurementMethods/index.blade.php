@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('navigation/mainnav.procurementMethods') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-cube"></i> {{{ trans('navigation/mainnav.procurementMethods') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <button data-action="create" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
                <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2> {{{ trans('navigation/mainnav.procurementMethods') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover" id="dt_basic">
                                <thead>
                                    <tr>
                                        <th class="text-middle text-center" style="width:40px;">{{ trans('general.no') }}</th>
                                        <th class="text-middle">{{{ trans('general.name') }}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $count = 0;?>
                                @foreach ($procurementMethods as $procurementMethod)
                                    <tr>
                                        <td class="text-middle text-center">{{{ ++$count }}}</td>
                                        <td>
                                            <a href="javascript:void(0);"
                                               data-action="update"
                                               data-id="{{{ $procurementMethod->id }}}"
                                               data-name="{{{ $procurementMethod->name }}}">
                                                {{{ $procurementMethod->name }}}
                                            </a>
                                            <a href="{{{ route('procurement-methods.destroy', array($procurementMethod->id)) }}}"
                                               class="pull-right btn btn-xs btn-danger delete-button"
                                               data-id="{{{ $procurementMethod->id }}}"
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

    @include('form_partials.editor_modal', array('fieldGroups' => array(
        array(
            array(
                'name' => 'name',
                'displayName' => trans('general.name'),
                'colSize' => 12
            )
        )
      )))

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#dt_basic').dataTable({
                "sDom": "t",
                "bPaginate": false,
                "autoWidth": true
            });

            function changeEditorModalTitle(title) {
                $('#editorModal [data-id=editorLabel]').text(title);
            }

            function setResourceField(field, value)
            {
                $('#editorModal [data-category=form] input[name='+field+']').val(value);
            }

            function getResourceField(field)
            {
                return $('#editorModal [data-category=form] input[name='+field+']').val();
            }

            $('#editorModal').on('shown.bs.modal', function (e) {
                selectInputField();
                disableSubmit(false);
            });

            $('#editorModal').on('hidden.bs.modal', function (e) {
                setResourceField('id', null);
                setResourceField('name', null);
                $(this).find('[data-category=error]').remove();
            });

            function selectInputField() {
                $('#editorModal [data-category=form] input[name=name]').select();
            }

            function setFormAction(action)
            {
                <?php $idPlaceholder = str_random(); ?>
                var idPlaceholder = "{{{ $idPlaceholder }}}";
                var templateRoute = "{{ route('procurement-methods.update', array($idPlaceholder)) }}";
                var method = 'PUT';
                if(action == 'store')
                {
                    templateRoute = "{{ route('procurement-methods.store') }}";
                    method = "POST";
                }

                var route = templateRoute.replace(idPlaceholder, getResourceField('id'));

                $('#editorModal [data-category=form] form').prop('action', route);
                $('#editorModal [data-category=form] form input[name=_method]').val(method);
            }

            $(document).on('click', '[data-action=create]', function () {
                changeEditorModalTitle("{{ trans('forms.add') }}");

                setResourceField('id', null);
                setResourceField('name', null);

                setFormAction('store');
                $('#editorModal').modal('show');
            });

            $(document).on('click', '[data-action=update]', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                changeEditorModalTitle("{{ trans('forms.update') }}");
                setResourceField('id', id);
                setResourceField('name', name);
                setFormAction('update');
                $('#editorModal').modal('show');
            });

            function disableSubmit(disable) {
                $('#editorModal button[data-action=submit]').prop('disabled', disable);
            }

            $("#editorModal form").on('submit', function(e){
                disableSubmit(true);
            });

            $(document).on('click', '#editorModal button[data-action=submit]', function () {
                $("#editorModal form").submit();
            });

            @if(!$errors->isEmpty())
                $('#editorModal').modal('show');
            @endif
        });
    </script>
@endsection