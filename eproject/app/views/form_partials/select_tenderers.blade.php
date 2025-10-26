<?php $label = $label ?? trans('forms.directedTo') ; ?>
<?php $checkboxName = $checkboxName ?? 'tenderers' ; ?>
<?php $selectedRecipientIds = $selectedRecipientIds ?? array(); ?>
<?php $defaultChecked = $defaultChecked ?? true ; ?>
<?php $checkedProperty = $defaultChecked ? 'checked' : null ; ?>

@if(!isset($hideLabel) || !$hideLabel)
<label class="label">{{{ $label }}} <span class="required">*</span>:</label>
@endif

@if(count($usersGroupedByCompany))
    <div class="panel-body no-padding">
        <div class="panel-group smart-accordion-default" id="tenderer_company-accordion">
            <?php $tendererCompanyCount = 0;?>
            @foreach ($usersGroupedByCompany as $id => $company)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#tenderer_company-accordion" href="#tenderer_company-accordion_{{$id}}" aria-expanded="@if($tendererCompanyCount > 0) false @else true @endif" @if($tendererCompanyCount > 0) class="collapsed" @endif style="font-size:12px;">
                        <i class="fa fa-plus-circle text-success fa-sm pull-right"></i> <i class="fa fa-minus-circle text-danger fa-sm pull-right"></i> <strong>{{ $tendererCompanyCount + 1}}.</strong> {{{  \PCK\Helpers\StringOperations::shorten($company['name'], 50) }}}
                        </a>
                    </h4>
                </div>
            </div>

            <div id="tenderer_company-accordion_{{$id}}" class="panel-collapse collapse @if($tendererCompanyCount == 0) in @endif">
                <div class="panel-body no-padding">
                    <table class="table table-condensed table-hover smart-form has-tickbox">
                        <thead>
                            <tr>
                                <th>
                                    <label class="checkbox">
                                        <input type="checkbox" name="checkbox-inline" class="checkall" id="company-{{{ $id }}}-share-select_all_groups" {{{ $checkedProperty}}}>
                                        <i></i> &nbsp;
                                    </label>
                                </th>
                                <th style="border-right:0">{{{ trans('users.users') }}}</th>
                            </tr>
                        </thead>
                        <tbody data-id="company-{{{ $id }}}" data-type="expandable" data-default="hide">
                            @foreach ($company['users'] as $companyUser)
                                <tr>
                                    <td>
                                        <label class="checkbox">
                                            <input type="checkbox" name="{{{ $checkboxName }}}[]"
                                                class="checkbox-select_group"
                                                id="{{{$companyUser->id}}}-checkbox_group_share_folder"
                                                value="{{{$companyUser->id}}}" {{{ in_array($companyUser->id, $selectedRecipientIds) ? 'checked' : $checkedProperty }}}>
                                            <i></i>
                                        </label>
                                    </td>
                                    <td>
                                        {{{ \PCK\Helpers\StringOperations::shorten($companyUser->name, 80) }}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <?php $tendererCompanyCount++ ?>
            @endforeach
        </div>
    </div>
@else
    <div class="alert alert-warning text-center">
        <i class="fa-fw fa fa-info"></i>
        <strong>Info!</strong> {{ trans('contractGroups.noAssignedGroups') }}
    </div>
@endif

