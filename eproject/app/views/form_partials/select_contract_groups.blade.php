<?php $label = $label ?? trans('forms.directedTo') ; ?>
<?php $checkboxName = $checkboxName ?? 'contract_groups' ; ?>
<?php $selectedGroupIds = $selectedGroupIds ?? array() ; ?>
<?php $defaultChecked = $defaultChecked ?? true ; ?>
<?php $checkedProperty = $defaultChecked ? 'checked' : null ; ?>

@if(!isset($hideLabel) || !$hideLabel)
<label class="label">{{{ $label }}} <span class="required">*</span>:</label>
@endif

@if(count($contractGroups) < 1)
    <div class="well padded txt-color-orangeDark">
        {{ trans('contractGroups.noAssignedGroups') }}
    </div>
@else
    <div class="table-responsive">
        <table class="table  table-condensed table-hover smart-form has-tickbox">
            <thead>
            <tr>
                <th>
                    <label class="checkbox">
                        <input type="checkbox" name="checkbox-inline" class="checkall" id="share-select_all_groups" {{{ $checkedProperty}}}>
                        <i></i> &nbsp;
                    </label>
                </th>
                <th>{{ trans('contractGroups.group') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($contractGroups as $contractGroup)
                <tr>
                    <td>
                        <label class="checkbox">
                            <input type="checkbox" name="{{{ $checkboxName }}}[]"
                                   class="checkbox-select_group"
                                   id="{{{$contractGroup->id}}}-checkbox_group_share_folder"
                                   value="{{{$contractGroup->id}}}" {{{ in_array($contractGroup->id, $selectedGroupIds) ? 'checked' : $checkedProperty }}}>
                            <i></i>
                        </label>
                    </td>
                    <td>
                        @if($contractGroup->group == PCK\ContractGroups\Types\Role::PROJECT_OWNER)
                            {{{ \PCK\Helpers\StringOperations::shorten($project->subsidiary->fullName, 35) }}}
                        @elseif(($company = $project->getCompanyByGroup($contractGroup->group)))
                            {{{ \PCK\Helpers\StringOperations::shorten($company->name, 35) }}}
                        @else
                            {{{ $project->getRoleName($contractGroup->group) }}}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
{{ $errors->first($checkboxName, '<em class="invalid">:message</em>') }}
