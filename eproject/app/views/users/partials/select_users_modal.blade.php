<?php $modalId = 'selectUsersModal' ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg fill-horizontal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-check-square"></i>
                        {{{ trans('users.selectUsers') }}}
                    <i class="fa fa-users"></i>
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div>
                <div class="table-responsive">
                    <table class="table  table-hover" id="select-users-table">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th class="hasinput">
                                <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                            </th>
                            <th class="hasinput">
                                <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                            </th>
                            <th class="hasinput">
                                <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                            </th>
                            <th class="hasinput">
                                <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                            </th>
                            <th>&nbsp;</th>
                        </tr>
                        <tr>
                            <th style="width: 5%;">{{{ trans('users.number') }}}</th>
                            <th style="width: auto;">{{{ trans('users.name') }}}</th>
                            <th style="width: 15%;" class="text-center">{{{ trans('users.email') }}}</th>
                            <th class="text-center">{{{ trans('users.company') }}}</th>
                            <th class="text-center">{{{ trans('users.companyReferenceNumber') }}}</th>
                            <th class="text-center">{{{ trans('users.select') }}}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ trans('forms.close') }}</button>

                <h4 class="pull-right">&nbsp</h4>

                <input type="button" data-action="select-users-submit" class="btn btn-primary pull-right" value="{{trans('forms.save')}}"/>

            </div>
        </div>
    </div>
</div>

<script>
    $("#select-users-table thead th input[type=text]").on( 'keyup change', function () {
        selectUsersTable
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
    });

    var selectUsersTable = $('#select-users-table').DataTable({
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
        "t"+
        "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        "autoWidth" : false,
        scrollCollapse: true,
        "iDisplayLength":10,
        bServerSide:true,
        "sAjaxSource":"{{{ $dataSource }}}",
        "aoColumnDefs": [
            {
                "aTargets": [ 0 ],
                "mData": function ( source, type, val ) {
                    var displayData = source['indexNo'];
                    return displayData;
                },
                "sClass": "text-middle text-center"
            },
            {
                "aTargets": [ 1 ],
                "mData": function ( source, type, val ) {
                    var displayData = source['name'];
                    return displayData;
                },
                "sClass": "text-middle"
            },
            {
                "aTargets": [ 2 ],
                "mData": function ( source, type, val ) {
                    var displayData = source['email'];
                    return displayData;
                },
                "sClass": "text-middle text-center"
            },
            {
                "aTargets": [ 3 ],
                "mData": function ( source, type, val ) {
                    var displayData = source['companyName'];
                    return displayData;
                },
                "sClass": "text-middle text-left occupy-min"
            },
            {
                "aTargets": [ 4 ],
                "mData": function ( source, type, val ) {
                    var displayData = '<span style="font-family: monospace;">'+source['companyReferenceNumber']+'</span>';
                    return displayData;
                },
                "sClass": "text-middle text-center occupy-min"
            },
            {
                "aTargets": [ 5 ],
                "mData": function ( source, type, val ) {
                    var displayData = '<input type="checkbox" class="select-user" value="' + source['id'] + '">';
                    return displayData;
                },
                "sClass": "text-middle text-center occupy-min"
            }
        ]
    });

    selectUsersTable.on( 'draw.dt', function () {
        vue.checkSelected();
    } );

    $(document).on('change', '.select-user', function(){
        if($(this).prop('checked')){
            vue.push($(this).val());
        }else{
            vue.remove($(this).val());
        }
    });

    var vue = new Vue({
        el: '#selectUsersModal',

        data: {
            users: []
        },

        methods: {
            checkSelected: function(){
                var checkboxes = $('.select-user');
                checkboxes.each(function(){
                    if(vue.isSelected($(this).val())){
                        $(this).prop('checked', true);
                    }
                });
            },
            isSelected: function(userId){
                return (this.users.indexOf(userId) > -1);
            },
            push: function(userId){
                this.users.push(userId);
            },
            remove: function(userId){
                var index = this.users.indexOf(userId);
                if(index > -1){
                    this.users.splice(index, 1);
                }
            },
            selectUsers: function(){
                $.ajax({
                    url: '{{{ $submitUrl }}}',
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        users: vue.users
                    },
                    success: function (data) {
                        if (data['success']) {
                            location.reload();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }
        }
    });

    $(document).on('click', '[data-action=select-users-submit]', function(){
        vue.selectUsers();
    });
</script>