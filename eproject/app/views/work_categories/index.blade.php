@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('workCategories.workCategories') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-wrench"></i> {{{ trans('workCategories.workCategories') }}}
        </h1>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <button id="createWorkCategory" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
            <i class="fa fa-plus"></i> {{{ trans('workCategories.addWorkCategory') }}}
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('workCategories.workCategories') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="workCategoryTable"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('work_categories.partials.editor_modal')

@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            var enabledStateFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var hideWorkCategoryButton = document.createElement('a');
                hideWorkCategoryButton.dataset.id = data.id;
                hideWorkCategoryButton.dataset.name = data.name;
                hideWorkCategoryButton.dataset.toggle = 'tooltip';
                hideWorkCategoryButton.className = data.enabled ? 'btn btn-xs btn-success' : 'btn btn-xs btn-danger';
                hideWorkCategoryButton.innerHTML = data.enabled ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';

                hideWorkCategoryButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    app_progressBar.toggle();

                    $.ajax({
                        url: "{{ route('workCategories.enabledState.toggle') }}",
                        method: 'POST',
                        data: {
                            id: data.id,
                            _token: '{{{ csrf_token() }}}',
                        },
                        success: function (response) {
                            if (response.success) {
                                workCategoryTable.updateData([{id:response.item.id, enabled:response.item.enabled}]);
                                workCategoryTable.redraw(true);
                                workCategoryTable.scrollToRow(response.item.id, 'center', false);
                            }

                            app_progressBar.maxOut();
                            app_progressBar.toggle();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                });

                return hideWorkCategoryButton;
            };

            var actionsFormatter  = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var editWorkCategoryButton = document.createElement('a');
                editWorkCategoryButton.dataset.id = data.id;
                editWorkCategoryButton.dataset.name = data.name;
                editWorkCategoryButton.dataset.identifier = data.identifier;
                editWorkCategoryButton.dataset.toggle = 'tooltip';
                editWorkCategoryButton.className = 'btn btn-xs btn-warning update-button';
                editWorkCategoryButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';

                return editWorkCategoryButton;
            };

            var workCategoryTable = new Tabulator('#workCategoryTable', {
                height:500,
                columns: [
					{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('workCategories.name') }}", field: 'name', headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
					{ title:"{{ trans('workCategories.identifier') }}", width: 150, field: 'identifier', hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
					{ title:"{{ trans('general.actions') }}", width: 70, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter },
					{ title:"{{ trans('general.enabled') }}", width: 70, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:enabledStateFormatter },

				],
                layout:"fitColumns",
				ajaxURL: "{{ route('workCategories.list') }}",
                placeholder:"{{ trans('general.noRecordsFound') }}",
                resizable:false,
            });

            /* ----- Editor ----- */

            new Vue({
                el: '#editorModal',

                data: {
                    name: '',
                    identifier: ''
                },

                methods: {
                    generateIdentifier: function () {
                        if (getWorkCategoryId() > 0) {
                            // Disable generate identifier for existing records (update).
                            return;
                        }
                        var identifier = this.name;
                        identifier = identifier.replace(/[^A-Za-z]/g, '');
                        var maxAutoGeneratedCharacters = 3;
                        this.identifier = identifier.substring(0, maxAutoGeneratedCharacters).toUpperCase();
                    }
                }
            });

            function changeEditorModalTitle(title) {
                $('#editorLabel').text(title);
            }

            function setWorkCategoryId(id) {
                $('#submit-button').data('id', id);
            }

            function getWorkCategoryId() {
                return $('#submit-button').data('id');
            }

            function getWorkCategoryName() {
                return $('#workCategory-name-input').val();
            }

            function getWorkCategoryIdentifier() {
                return $('#workCategory-identifier-input').val();
            }

            function setWorkCategoryName(name) {
                return $('#workCategory-name-input').val(name);
            }

            function setWorkCategoryIdentifier(identifier) {
                return $('#workCategory-identifier-input').val(identifier);
            }

            /* Errors */
            function setWorkCategoryNameError(error) {
                $('#workCategory-name-error').text(error);
            }

            function setWorkCategoryIdentifierError(error) {
                $('#workCategory-identifier-error').text(error);
            }

            $('#editorModal').on('shown.bs.modal', function (e) {
                selectInputField();
                disableSubmit(false);
            });

            $('#editorModal').on('hidden.bs.modal', function (e) {
                setWorkCategoryId(null);
                setWorkCategoryName(null);
                setWorkCategoryIdentifier(null);
                setWorkCategoryNameError(null);
                setWorkCategoryIdentifierError(null);
            });


            function selectInputField() {
                $('#workCategory-name-input').select();
            }

            function showEditorModal() {
                setWorkCategoryNameError('');
                setWorkCategoryIdentifierError('');
                $('#editorModal').modal('show');
            }

            /* Create */
            $(document).on('click', '#createWorkCategory', function () {
                changeEditorModalTitle("{{{ trans('workCategories.addWorkCategory') }}}");
                setWorkCategoryId(null);
                setWorkCategoryName(null);
                setWorkCategoryIdentifier(null);
            });

            /* Edit */
            $(document).on('click', '.update-button', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var identifier = $(this).data('identifier');
                changeEditorModalTitle("{{{ trans('workCategories.updateWorkCategory') }}}");
                setWorkCategoryId(id);
                setWorkCategoryName(name);
                setWorkCategoryIdentifier(identifier);
                showEditorModal();
            });

            function disableSubmit(disable) {
                $('#submit-button').prop('disabled', disable);
            }

            /* Submit */
            $(document).on('click', '#submit-button', function () {
                disableSubmit(true);
                submit(getWorkCategoryId(), getWorkCategoryName(), getWorkCategoryIdentifier());
            });

            /* Ajax call post request */
            function submit(id, name, identifier) {
                app_progressBar.toggle();

                var url = '{{{ route('workCategories.store') }}}';
                var isNew = true;

                if (id != null) {
                    url = '{{{ route('workCategories.update') }}}';
                    isNew = false;
                }

                setWorkCategoryNameError('');
                setWorkCategoryIdentifierError('');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        data: {
                            id: id,
                            name: name,
                            identifier: identifier
                        }
                    },
                    success: function (response) {
                        if (response.success) {
                            if(isNew) {
                                workCategoryTable.addRow(response.item);
                            } else {
                                workCategoryTable.updateData([{id:response.item.id, name:response.item.name, identifier:response.item.identifier, enabled:response.item.enabled}]);
                                workCategoryTable.redraw(true);
                            }

                            workCategoryTable.scrollToRow(response.item.id, 'center', false);
                            $('#editorModal').modal('hide');
                        }
                        else {
                            setWorkCategoryNameError(response['errors']['name']);
                            setWorkCategoryIdentifierError(response['errors']['identifier']);
                            disableSubmit(false);
                        }

                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }
        });
    </script>
@endsection