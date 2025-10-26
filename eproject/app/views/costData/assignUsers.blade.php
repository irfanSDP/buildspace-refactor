@extends('templates.assignUsers')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('costData', trans('costData.costData'), array($costData->id)) }}</li>
        <li>{{{ trans('users.assignUsers') }}}</li>
    </ol>
@endsection

@section('assign-users-table-html')
<?php $hasEditorOption = isset($hasEditorOption) ? $hasEditorOption : true ?>
<table class="table  table-hover" data-type="assigned-users-table">
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
        @if($hasEditorOption)
            <th>&nbsp;</th>
        @endif
        <th>&nbsp;</th>
    </tr>
    <tr>
        <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
        <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
        <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
        <th class="text-middle text-left text-nowrap squeeze">{{{ trans('users.company') }}}</th>
        @if($hasEditorOption)
            <th class="text-middle text-center text-nowrap squeeze">{{{ trans('forms.editor') }}}</th>
        @endif
        <th class="text-middle text-center text-nowrap squeeze">{{{ trans('notifications.resendNotification') }}}</th>
    </tr>
    </thead>
</table>
@endsection

@section('assign-users-table-js')
$('[data-type=assigned-users-table]').DataTable({
    "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
    "t"+
    "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
    "autoWidth" : false,
    scrollCollapse: true,
    "iDisplayLength":10,
    bServerSide:true,
    "sAjaxSource":"{{{ $assignedUsersRoute }}}",
    "aoColumnDefs": [
        {
            "aTargets": [ 0 ],
            "mData": function ( source, type, val ) {
                return source['indexNo'];
            },
            "sClass": "text-middle text-center text-nowrap squeeze"
        },
        {
            "aTargets": [ 1 ],
            "mData": function ( source, type, val ) {
                var displayData = source['name']
                    + '<div class="pull-right">'
                    + '<button type="button" data-action="revoke" data-url="' + source['route:revoke'] + '" type="tooltip" title="{{{ trans('users.unAssignUsers') }}}" class="btn btn-xs btn-danger">'
                    + '<i class="fa fa-times"></i>'
                    + '</div>';
                return displayData;
            },
            "sClass": "text-middle text-left text-nowrap"
        },
        {
            "aTargets": [ 2 ],
            "mData": function ( source, type, val ) {
                return source['email'];
            },
            "sClass": "text-middle text-center text-nowrap squeeze"
        },
        {
            "aTargets": [ 3 ],
            "mData": function ( source, type, val ) {
                return source['companyName'];
            },
            "sClass": "text-middle text-left text-nowrap squeeze"
        }
        @if($hasEditorOption)
        , {
            "aTargets": [ 4 ],
            "mData": function ( source, type, val ) {
                var checked = source['isEditor'] ? 'checked' : '';
                return '<input type="checkbox" data-action="set-as-verifier" data-url="' + source['route:setEditor'] + '" ' + checked + '/>';
            },
            "sClass": "text-middle text-center text-nowrap squeeze"
        }
        @endif
        ,{
            "aTargets": [ 5 ],
            "mData": function ( source, type, val ) {
                return '<button type="button" class="btn btn-xs btn-warning" data-action="resend-notification" data-url="'+source['route:sendNotification']+'"><i class="fa fa-envelope txt-color-white"></i></button>';
            },
            "sClass": "text-middle text-center text-nowrap squeeze"
        }
    ]
});

$('[data-type=assigned-users-table]').on('click', '[data-action=resend-notification]', function(){
    $.post($(this).data('url'), {_token:"{{ csrf_token() }}"})
        .done(function(data){
            if(data.success){
                SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('notifications.notificationResent') }}");
            }
            else
            {
                SmallErrorBox.refreshAndRetry();
            }
        })
        .fail(function(data){
            SmallErrorBox.refreshAndRetry();
        });

});
@endsection