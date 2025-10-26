@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('technicalEvaluation.sets', trans('technicalEvaluation.technicalEvaluation')) }}</li>
        <li>{{ link_to_route('technicalEvaluation.sets', $setReference->set->name) }}</li>
        <li>{{{ trans('technicalEvaluation.attachments') }}}</li>
    </ol>
@endsection

@section('content')

<div id="listItemsIndex">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-paperclip"></i> {{{ trans('technicalEvaluation.attachments') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <button data-action="add-item" class="btn btn-primary btn-md pull-right header-btn" data-target="#addListItemModal" data-toggle="modal">
                <i class="fa fa-plus"></i> {{{ trans('technicalEvaluation.add') }}}
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>
                        {{{ trans('technicalEvaluation.attachments') }}}
                    </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table  table-hover" id="item_table">
                                <thead>
                                <tr>
                                    <th class="text-middle text-center squeeze">{{{ trans('technicalEvaluation.no') }}}</th>
                                    <th class="text-middle text-left">{{{ trans('technicalEvaluation.name') }}}</th>
                                    <th class="text-middle text-left">&nbsp;</th>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('technicalEvaluation.mandatory') }}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $index = 1; ?>
                                @foreach($setReference->attachmentListItems as $listItem)
                                    <tr>
                                        <td class="text-middle text-center">
                                            {{{ $index++ }}}
                                        </td>
                                        <td class="text-middle text-left">
                                            {{{ $listItem->description }}}
                                        </td>
                                        <td>
                                            <span class="nowrap">
                                                <a href="#"
                                                    class="btn btn-xs btn-warning update-button"
                                                    data-target="#addListItemModal" data-toggle="modal"
                                                    data-action="update-list-item"
                                                    data-id="{{{ $listItem->id }}}"
                                                    data-description="{{{ $listItem->description }}}"
                                                    data-compulsory="{{{ $listItem->compulsory }}}">
                                                    <i class="fa fa-pen-square"></i>
                                                    <strong>
                                                        {{ trans('technicalEvaluation.update') }}
                                                    </strong>
                                                </a>
                                                <a href="{{ route('technicalEvaluation.attachments.listItem.delete', array($setReference->id, $listItem->id) ) }}"
                                                    class="btn btn-xs btn-danger"
                                                    data-method="delete"
                                                    data-csrf_token="{{{ csrf_token() }}}">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($listItem->compulsory)
                                                <i class="fa fa-check text-success"></i>
                                            @endif
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

    @include('technical_evaluation.attachments.list_items.add_list_item_modal')

</div>

@endsection

@section('js')
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
            el: '#listItemsIndex',

            data: {
                listItemId: '',
                description: '',
                compulsory: true
            },

            methods: {
            }
        });

        $(document ).on('click', '.update-button[data-action=update-list-item]', function(){
            vue.listItemId = $(this ).data('id');
            vue.description = $(this ).data('description');
            vue.compulsory = $(this ).data('compulsory');
        });

        $(document ).on('click', '[data-action=add-item]', function(){
            vue.listItemId = null;
            vue.description = null;
            vue.compulsory = true;
        });

        $('#addListItemModal' ).on('shown.bs.modal', function(){
            $('#attachment_item-description' ).select();

            var checkbox = $('#attachment_item-compulsory' );

            checkbox.prop( 'checked', true );

            if( !vue.compulsory ) checkbox.prop( 'checked', false );
        });

    </script>
@endsection