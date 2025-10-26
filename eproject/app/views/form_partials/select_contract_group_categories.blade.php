<?php $label = $label ?? trans('forms.directedTo') ; ?>
<?php $checkboxName = $checkboxName ?? 'groups' ; ?>
<?php $selectedGroupIds = $selectedGroupIds ?? array() ; ?>
<?php $defaultChecked = $defaultChecked ?? true ; ?>
<?php $checkedProperty = $defaultChecked ? 'checked' : null ; ?>

@if(!isset($hideLabel) || !$hideLabel)
<label class="label">{{{ $label }}} <span class="required">*</span>:</label>
@endif

@if(count($groups) < 1)
    <div class="well padded txt-color-orangeDark">
        {{ trans('contractGroupCategories.noAssignedUserGroups') }}
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
                <th>{{ trans('contractGroupCategories.userGroup') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($groups as $id => $group)
                <tr>
                    <td>
                        <label class="checkbox">
                            <input type="checkbox" name="{{{ $checkboxName }}}[]"
                                class="checkbox-select_group"
                                id="{{$id}}-checkbox_group_share_folder"
                                value="{{$id}}" {{{ in_array($id, $selectedGroupIds) ? 'checked' : $checkedProperty }}}>
                            <i></i>
                        </label>
                    </td>
                    <td>
                        {{ $group['name'] }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
{{ $errors->first($checkboxName, '<em class="invalid">:message</em>') }}
