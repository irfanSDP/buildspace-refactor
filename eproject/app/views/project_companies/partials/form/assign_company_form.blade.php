<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title">
            <i class="glyphicon glyphicon-user"></i> {{{ trans('companies.assignCompaniesToProject') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 mb-4">
        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#selectedCompaniesUsersModal"><i class="fas fa-users"></i> {{ trans('projects.usersOfSelectedCompanies') }}</button>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-lg-12">
        <div class="jarviswidget">
            <header><h2>{{{ trans('companies.assignCompaniesToProject') }}}</h2></header>
            <div class="no-padding">
                <div class="widget-body">
                {{ Form::open(array('class' => 'smart-form')) }}
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <?php $disabled = ( !$editable OR $project_status === PCK\Projects\Project::STATUS_TYPE_COMPLETED ); ?>
                            <tr>
                                <th colspan="2" style="width: 25%;" class="text-left">{{ trans('companies.group') }}</th>
                                <th style="width: auto;" class="text-left">{{ trans('companies.company') }}</th>
                                @if(!$disabled)
                                <th style="width: 160px;" class="text-center">{{ trans('general.actions') }}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $order = array(
                                \PCK\ContractGroups\Types\Role::PROJECT_OWNER,
                                \PCK\ContractGroups\Types\Role::GROUP_CONTRACT,
                                \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER,
                                \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_1,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_2,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_3,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_4,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_5,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_6,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_7,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_8,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_9,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_10,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_11,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_12,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_13,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_14,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_15,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_16,
                                \PCK\ContractGroups\Types\Role::CONSULTANT_17,
                                \PCK\ContractGroups\Types\Role::PROJECT_MANAGER,
                            );
                            $groups = new \Illuminate\Database\Eloquent\Collection($groups);
                            $groups = \PCK\Helpers\Sorter::sortCollectionWithDefinedOrder($groups, $order, 'group');
                            ?>
                            @foreach ($groups as $group)
                                <tr>
                                    <td class="text-middle">
                                        <div data-toggle="tooltip" data-placement="bottom" title="{{ trans('contractGroups.checkToGiveThisGroupAccessToTenderDocs') }}">
                                            <input type="radio" name="role_access_to_tender_document" value="{{{ $group->group }}}" {{{ ($project->getCallingTenderRole() == $group->group)? "checked" : "" }}} {{{ ($disabled || ($group->group == \PCK\ContractGroups\Types\Role::PROJECT_MANAGER)) ? 'disabled' : ''}}}/>
                                        </div>
                                    </td>
                                    <td class="text-middle">
                                        <div class="{{{ $errors->has('group_names.'.$group->group) ? 'has-error' : null }}}">
                                            <div class="well">
                                                <h4>{{{ Session::get('array_input')['group_names'][$group->group] ?? $project->getRoleName($group->group) }}}</h4>
                                            </div>
                                            <input type="hidden" value="{{{ Session::get('array_input')['group_names'][$group->group] ?? $project->getRoleName($group->group) }}}" name="group_names[{{{$group->group}}}]" placeholder="{{{ $group->name }}}" class="form-control text-center bold"/>
                                        </div>
                                        {{ $errors->first('group_names.'.$group->group, '<em class="invalid pull-right">:message</em>') }}
                                    </td>
                                    <td class="text-middle">
                                        @if ( $group->group == PCK\ContractGroups\Types\Role::PROJECT_OWNER )
                                            {{{ $project->subsidiary->fullName }}}

                                            {{ Form::hidden("group_id[{$group->id}]", $project->businessUnit->id) }}
                                        @elseif ( $user->hasCompanyProjectRole($project, $group->group) )
                                            {{{ $user->getAssignedCompany($project)->name }}}

                                            {{ Form::hidden("group_id[{$group->id}]", $user->getAssignedCompany($project)->id) }}
                                        @else
                                            @include('project_companies.partials.form.singleCompanySelect', array('project_status'=>$project->status_id, 'editable' => $editable, 'group' => $group))
                                        @endif
                                    </td>
                                    <?php
                                        $canAssignCompany = !$disabled;
                                        
                                        if($group->group == PCK\ContractGroups\Types\Role::PROJECT_OWNER)
                                        {
                                            $canAssignCompany = false;
                                        }

                                        if($user->hasCompanyProjectRole($project, $group->group))
                                        {
                                            $canAssignCompany = false;
                                        }
                                    ?>
                                    @if(!$disabled)
                                    <td class="text-middle text-center">
                                        @if($canAssignCompany)
                                        <button type="button" class="btn btn-primary" data-action="assignCompany" data-group="{{ $group->id }}" data-url="{{ route('assignable.companies.get', [$project->id, $group->id]) }}">{{ trans('general.assign') }}</button>
                                        <?php $unassigButtonDisplayStyle = array_key_exists($group->id, $selectedCompanies) ? 'inherit' : 'none'; ?>
                                        <button type="button" class="btn btn-danger" data-id="group-{{ $group->id }}-unassign" data-action="unassignCompany" data-group="{{ $group->id }}" style="display:{{ $unassigButtonDisplayStyle }};">{{ trans('general.unassign') }}</button>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <footer class="text-right mt-8">
                        @if ( $editable )
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        @endif
                        <a href="#" data-toggle="modal" data-target="#updatedByLogsModal" class="btn btn-success btn-md">
                            {{ trans('companies.viewUpdatedByLogs') }}
                        </a>
                        {{ link_to_route('projects.show', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                    </footer>
                {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>