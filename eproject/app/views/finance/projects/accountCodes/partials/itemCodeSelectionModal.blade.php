<?php $modalId = isset($modalId) ? $modalId : 'itemCodeSelectionModal' ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{ trans('accountCodes.itemCodes') }}
                </h4>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom: 10px;">
                    <div class="col col-sm-6">
                        <select id="accountGroupFilter" class="select2" style="width:100%" data-action="filter">
                            @foreach($accountGroups as $accountGroup)
                                <option value="{{{ $accountGroup->id }}}">{{{ $accountGroup->name }}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-sm-12">
                        <div id="itemCodeSelectionTable"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
                <button id="btnSaveSelectedAccountCodes" class="btn btn-primary" disabled>{{ trans('forms.save') }}</button>
            </div>
        </div>
    </div>
</div>