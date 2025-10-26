@extends('layout.main')

<?php $editable = ( !$user->isSuperAdmin() && $user->hasCompanyProjectRole($project, array( \PCK\ContractGroups\Types\Role::PROJECT_OWNER, \PCK\ContractGroups\Types\Role::GROUP_CONTRACT)) and $user->isGroupAdmin()); ?>

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>Assign Company</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    @include('project_companies.partials.form.assign_company_form', array('project_status'=>$project->status_id, 'editable' => $editable))

    @include('project_companies.partials.updated_by_logs', array(
        'title' => trans('companies.viewUpdatedByLogs'),
        'logs' => $project->assignCompaniesLogs
    ))
    @include('templates.generic_table_modal', [
        'modalId'    => 'assignCompanyModal',
        'title'      => trans('companies.assignCompany'),
        'tableId'    => 'assignCompanyTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
    @include('templates.generic_table_modal', [
        'modalId'    => 'selectedCompaniesUsersModal',
        'title'      => trans('projects.usersOfSelectedCompanies'),
        'tableId'    => 'selectedCompaniesUsersTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
@endsection

@section('js')
<script>
    $(document).ready(function() {

        $('.company_list').select2({
            placeholder: "Select Company",
            theme: 'bootstrap'
        });

        var assignCompanyTable = null;

        $.ajaxSetup({
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
        });

        $('[data-action="assignCompany"]').on('click', function(e) {
            e.preventDefault();

            var groupId = $(this).data('group');
            var url     = $(this).data('url');

            $('#assignCompanyModal').data('group', groupId);
            $('#assignCompanyModal').data('url', url);
            $('#assignCompanyModal').modal('show');
        });

        $('#assignCompanyModal').on('shown.bs.modal', function(e) {
            var groupId = $(this).data('group');
            var url = $(this).data('url');
            var modal = $(this);

            var mainTable = new Tabulator('#assignCompanyTable', {
                height:450,
                layout:"fitColumns",
                ajaxURL: url,
                ajaxConfig: "GET",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                pagination: 'local',
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('companies.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true },
                    {title:"", field: 'hidden', width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                        var data = cell.getRow().getData();
                        
                        var selectButton = document.createElement('button');
                        selectButton.className = 'btn btn-xs btn-primary';
                        selectButton.innerHTML = "{{ trans('general.assign') }}";

                        selectButton.addEventListener('click', function(e) {
                            e.preventDefault();

                            $('[name="group_id[' + groupId + ']"]').val(data.id);
                            $('[data-id="group-' + groupId + '-company-name"]').html(data.name);
                            $('[data-id="group-' + groupId + '-unassign"]').show();

                            modal.modal('hide');
                        });

                        return selectButton;
                    }},
                ],
            });
        });

        $('[data-action="unassignCompany"]').on('click', function(e) {
            var groupId     = $(this).data('group');
            
            $('[name="group_id[' + groupId + ']"]').val(0);
            $('[data-id="group-' + groupId + '-company-name"]').html('');

            $(this).hide();
        });

        var selectedCompaniesUsersTable = null;

        $('#selectedCompaniesUsersModal').on('shown.bs.modal', function(e) {
            e.preventDefault();

            selectedCompaniesUsersTable = new Tabulator('#selectedCompaniesUsersTable', {
                height:500,
                layout:"fitColumns",
                ajaxURL: "{{ route('selected.companies.users.get', [$project->id]) }}",
                ajaxConfig: "GET",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                groupBy:function(data){
                    return "<span style='color:#000; margin-left:10px;'>[ " + data.contract_group + " ] </span><span style='color:#00F; margin-left:10px;'>" + data.company + "</span>";
                },
                groupHeader:function(value, count, data, group){
                    return value + "<span style='color:#d00; margin-left:10px;'>( " + count + " {{ trans('general.usersBracket') }} )</span>";
                },
                columns:[
                    {title:"{{ trans('users.name') }}", field:"user", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true },
                    {title:"{{ trans('users.email') }}", field:"email", minWidth: 200, hozAlign:"left", headerSort:false, headerFilter: true },
                ],
            });
        });
    });
</script>
@endsection