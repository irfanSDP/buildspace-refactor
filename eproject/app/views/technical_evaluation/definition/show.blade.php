@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('technicalEvaluation.sets', trans('technicalEvaluation.technicalEvaluationSets'), array()) }}</li>
        <li>{{{ trans('technicalEvaluation.technicalEvaluation') }}}</li>
    </ol>
@endsection

@section('content')

    <?php use \PCK\TechnicalEvaluationItems\TechnicalEvaluationItem as Item; ?>

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-check-square"></i> {{{ trans('technicalEvaluation.technicalEvaluation') }}} ({{{ Item::getTypeName($item->getChildrenType()) }}})
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <button id="createItem" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal" {{{ $item->saturated() ? 'disabled' : null }}}>
                <i class="fa fa-plus"></i> {{{ trans('technicalEvaluation.add') }}}
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>
                        @foreach($item->getAncestors() as $ancestor)
                            <a href="{{ route('technicalEvaluation.item.show', array($ancestor->id)) }}" class="plain">
                                {{{ $ancestor->name }}} /
                            </a>
                        @endforeach
                        {{{ $item->name }}}
                    </h2>
                    <?php
                        $label = Item::getValueName($item->getChildrenType());
                        $value = number_format($item->getChildrenValueTotal(), 2);
                        $max = number_format($item->getMaxChildrenValueTotal(), 2);
                    ?>

                    <h2 style="float:right">
                        [
                            @if($item->type != Item::TYPE_ITEM)
                                @include('templates.completionRatio', array('value' => $value, 'max' => $max))
                            @else
                                {{{ trans('technicalEvaluation.max') }}} : <strong>{{{ $max }}}</strong>
                            @endif
                        ] &nbsp;
                    </h2>

                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover" id="item_table">
                                <thead>
                                <tr>
                                    <th style="width:40px;" class="text-center">{{{ trans('technicalEvaluation.no') }}}</th>
                                    <th style="width:auto;min-width:180px;">{{{ trans('technicalEvaluation.name') }}}</th>
                                    <th style="width:120px;" class="text-center">
                                        <?php
                                            $columnName = ($item->type == Item::TYPE_OPTION)
                                                    ? Item::getValueName($item->type)
                                                    : Item::getValueName($item->getChildrenType())
                                        ?>
                                        {{{ $columnName }}}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $index = 0; ?>
                                @foreach ($item->children as $child)
                                    <tr>
                                        <td class="text-center">{{{ ++$index }}}</td>
                                        <td>
                                            @if($child->type != Item::TYPE_OPTION)
                                            <a href="{{{ route('technicalEvaluation.item.show', array($child->id)) }}}">
                                                {{{ $child->name }}}
                                            </a>
                                            @else
                                                {{{ $child->name }}}
                                            @endif
                                            <a href="{{{ route('technicalEvaluation.item.delete', array($child->id)) }}}"
                                               class="pull-right btn btn-xs btn-danger delete-button"
                                               data-id="{{{ $child->id }}}"
                                               data-method="delete"
                                               data-csrf_token="{{{ csrf_token() }}}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <a href="#"
                                               class="pull-right btn btn-xs btn-default update-button"
                                               style="margin-right:2px;"
                                               data-id="{{{ $child->id }}}"
                                               data-name="{{{ $child->name }}}"
                                               data-value="{{{ $child->value }}}">
                                                <i class="fas fa-pen-square"></i> {{ trans('technicalEvaluation.update') }}
                                            </a>
                                            <span class="pull-right">
                                                &nbsp;
                                            </span>
                                            <span class="badge bg-bootstrap-default pull-right inbox-badge">
                                                {{{ $child->children->count() }}}
                                            </span>
                                        </td>
                                        <td class="text-center {{{ $child->isDescendantsSufficient() ? 'success' : 'danger' }}}">
                                            <a href="#"
                                            class="update-button"
                                            data-id="{{{ $child->id }}}"
                                            data-name="{{{ $child->name }}}"
                                            data-value="{{{ $child->value }}}">
                                            {{{ $child->value }}}
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

    @include('technical_evaluation.partials.editor_modal')

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

        new Vue({
            el: '#editorModal',

            data: {
                name: '',
                value: ''
            },

            methods: {
            }
        });

        function changeEditorModalTitle(title) {
            $('#editorLabel').text(title);
        }

        function setItemId(id) {
            $('#submit-button').data('id', id);
        }

        function getItemId() {
            return $('#submit-button').data('id');
        }

        function getItemName() {
            return $('#item-name-input').val();
        }

        function getItemValue() {
            return $('#item-value-input').val();
        }

        function setItemName(name) {
            return $('#item-name-input').val(name);
        }

        function setItemValue(value) {
            return $('#item-value-input').val(value);
        }

        /* Errors */
        function setItemNameError(error) {
            $('#item-name-error').text(error);
        }

        function setItemValueError(error) {
            $('#item-value-error').text(error);
        }

        $('#editorModal').on('shown.bs.modal', function (e) {
            selectInputField();
            disableSubmit(false);
        });

        $('#editorModal').on('hidden.bs.modal', function (e) {
            setItemId(null);
            setItemName(null);
            setItemValue(null);
            setItemNameError(null);
            setItemValueError(null);
        });

        function selectInputField() {
            $('#item-name-input').select();
        }

        function showEditorModal() {
            setItemNameError('');
            setItemValueError('');
            $('#editorModal').modal('show');
        }

        /* Create */
        $(document).on('click', '#createItem', function () {
            changeEditorModalTitle("{{{ trans('technicalEvaluation.add') }}}");
            setItemId(null);
            setItemName(null);
            setItemValue(null);
        });

        /* Edit */
        $(document).on('click', '.update-button', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var value = $(this).data('value');
            changeEditorModalTitle("{{{ trans('technicalEvaluation.update') }}}");
            setItemId(id);
            setItemName(name);
            setItemValue(value);
            showEditorModal();
        });

        function disableSubmit(disable) {
            $('#submit-button').prop('disabled', disable);
        }

        /* Submit */
        $(document).on('click', '#submit-button', function () {
            disableSubmit(true);
            submit(getItemId(), getItemName(), getItemValue());
        });

        /* Ajax call post request */
        function submit(id, name, value) {
            var url = '{{ route('technicalEvaluation.item.store') }}';

            if (id != null) {
                url = '{{ route('technicalEvaluation.item.update') }}';
            }

            setItemNameError('');
            setItemValueError('');

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    data: {
                        id: id,
                        name: name,
                        value: value,
                        parentId: "{{{ $item->id }}}"
                    }
                },
                success: function (data) {
                    if (data['success']) {
                        location.reload();
                    }
                    else {
                        setItemNameError(data['errors']['name']);
                        setItemValueError(data['errors']['value']);
                        disableSubmit(false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        }
    </script>
@endsection