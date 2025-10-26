@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('technicalEvaluation.technicalEvaluationSets') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-object-group"></i> {{{ trans('technicalEvaluation.sets') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            @include("technical_evaluation.definition.actions_menu")
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>
                        {{{ trans('technicalEvaluation.sets') }}}
                    </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover" id="item_table">
                                <thead>
                                <tr>
                                    <th style="width:5%" class="text-center">{{{ trans('technicalEvaluation.no') }}}</th>
                                    <th style="width:auto" class="text-center">{{{ trans('technicalEvaluation.name') }}}</th>
                                    <th style="width:20%" class="text-center">{{{ trans('technicalEvaluation.workCategory') }}}</th>
                                    <th style="width:20%" class="text-center">{{{ trans('technicalEvaluation.contractLimit') }}}</th>
                                    <th class="text-center occupy-min">{{{ trans('technicalEvaluation.attachments') }}}</th>
                                    <th class="text-center occupy-min">{{{ trans('technicalEvaluation.preview') }}}</th>
                                    <th class="text-center occupy-min">{{{ trans('general.hide') }}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $index = 0; ?>
                                @foreach ($setReferences as $setReference)
                                    <tr>
                                        <td class="text-center">{{{ ++$index }}}</td>
                                        <td class="text-left">
                                            <a href="{{{ route('technicalEvaluation.item.show', array($setReference->set->id)) }}}">
                                                {{{ $setReference->set->name }}}
                                            </a>

                                            <a href="{{{ route('technicalEvaluation.sets.delete', array($setReference->id)) }}}"
                                               class="pull-right btn btn-xs btn-danger delete-button"
                                               title="{{ trans('forms.delete') }}" data-toggle="tooltip"
                                               data-method="delete"
                                               data-csrf_token="{{{ csrf_token() }}}">
                                                <i class="fa fa-trash"></i>
                                            </a>

                                            <button class="btn btn-xs btn-primary pull-right margin-less-right" style="margin-right:2px;" data-action="copy-set-reference" data-id="{{{ $setReference->id }}}" data-name="{{{ $setReference->set->name }}}" title="{{ trans('general.copy') }}" data-toggle="tooltip">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            @if($setReference->workCategory)
                                                {{{ $setReference->workCategory->name }}}
                                            @endif
                                        </td>
                                        <td class="text-left">
                                            @if($setReference->contractLimit)
                                                {{{ $setReference->contractLimit->limit }}}
                                            @else
                                                <strong class="text-warning">
                                                    {{ trans("general.none") }}
                                                </strong>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('technicalEvaluation.attachments.listItem.index', array($setReference->id)) }}" class="btn btn-warning btn-xs">
                                                <i class="fas fa-pen-square"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-xs text-white" data-action="form" data-id='{{ $setReference->id }}'>
                                                <i class="fa fa-clipboard-list"></i>
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" data-action="hide" data-id="{{ $setReference->id }}" value=1 @if($setReference->hidden) checked @endif >
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

    @include('technical_evaluation.partials.set_creator_modal')
    @include('technical_evaluation.form_modal_preview')
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

        var vue = new Vue({
            el: '#creatorModal',

            data: {
                work_category_id: '',
                contract_limit_id: '',
                contract_limit: '',
                mode: 'setExisting',
                templateId: null,
                templateName: '',
                generalError: '',
                templateNameIsHidden: true,
                templateError: ''
            },

            methods: {
                getWorkCategoryId: function(){
                    return $('#set-work_category_id-input').val();
                },
                getContractLimitId: function(){
                    return $('#set-contract_limit_id-input' ).val();
                },
                getContractLimit: function(){
                    return this.contract_limit;
                },
                setMode: function(setNew){
                    this.mode = 'setExisting';
                    if(setNew)
                    {
                        this.mode = 'setNew';
                    }
                }
            }
        });

        function setMode(setNewMode)
        {
            vue.setMode(setNewMode);

            var setNewElements = $('.set-new' );
            var setExisitngElements = $('.set-existing' );

            setNewElements.hide();
            setExisitngElements.show();

            if(setNewMode)
            {
                setExisitngElements.hide();
                setNewElements.show();
            }
        }

        setMode(false);

        $(document ).on('click', '#set-new-contract_limit-button', function(){
            setMode(true);
            $('#set-contract_limit-input' ).focus();
        });

        $(document ).on('click', '#set-existing-contract_limit-button', function(){
            setMode(false);
        });

        var creatorModal = $('#creatorModal');

        creatorModal.on('show.bs.modal', function (e) {
            vue.generalError = null;
            vue.templateError = null;
        });

        creatorModal.on('shown.bs.modal', function (e) {
            disableSubmit(false);
        });

        function disableSubmit(disable) {
            $('.submit-button').prop('disabled', disable);
        }

        /* Submit */
        $(document).on('click', '.submit-button', function () {
            disableSubmit(true);
            submit();
        });

        $('#item_table [data-action=copy-set-reference]').on('click', function(){
            vue.templateId = $(this).data('id');
            vue.templateName = $(this).data('name');
            vue.templateNameIsHidden = false;
            $('#creatorModal').modal('show');
        });

        $('#creatorModal').on('hide.bs.modal', function(){
            vue.templateId = null;
            vue.templateName = '';
            vue.templateNameIsHidden = true;
        });

        /* Ajax call post request */
        function submit() {

            $.ajax({
                url: '{{ route('technicalEvaluation.sets.store') }}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    data: {
                        workCategoryId: vue.getWorkCategoryId(),
                        contractLimitId: vue.getContractLimitId(),
                        contractLimit: vue.getContractLimit(),
                        templateId: vue.templateId,
                        setExisting: (vue.mode == 'setExisting')
                    }
                },
                success: function (data) {
                    if (data['success']) {
                        location.assign(data['route:setShow']);
                    }
                    else {
                        vue.generalError = data['errors']['general'];
                        vue.templateError = data['errors']['templateId'];
                        disableSubmit(false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        }

        $('#item_table').on('click', '[data-action=hide]', function(){
            var self = this;
            eproject.generateRoutes(function(routes){
                $.post(routes['technicalEvaluation.toggleHide'], {_token: _csrf_token})
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                    }
                    else{
                        $(self).prop('checked', !$(self).prop('checked'));
                        SmallErrorBox.refreshAndRetry();
                    }
                })
                .fail(function(data){
                    SmallErrorBox.refreshAndRetry();
                });
            }, {"technicalEvaluation.toggleHide": [$(this).data('id')]});
        });

        $('#item_table').on('click', '[data-action=form]', function(){
            $.get("{{ route('technicalEvaluation.getFormResponsesWithoutProject') }}?id="+$(this).data('id'), function(data){
                formModal.init(data);
            });
        });
    </script>
@endsection