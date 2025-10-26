<?php $modalId    = isset($modalId) ? $modalId : 'logModal'; ?>
<?php $modalDialogClass = isset($modalDialogClass) ? $modalDialogClass : 'modal-lg'; ?>
<?php $title      = isset($title) ? $title : trans('general.log'); ?>
<?php $isStatic   = isset($isStatic) ? $isStatic : false; ?>
<?php $externalVendorGroups = isset($externalVendorGroups) ? $externalVendorGroups : []; ?>

<div class="modal fade" id="{{{ $modalId }}}" role="dialog" @if($isStatic) data-backdrop="static" data-keyboard="false" @endif aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog {{ $modalDialogClass }}">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('vendorManagement.vmVendorMigration') }}</h4>
                @if(!$isStatic)
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
                @endif
            </div>
            <div class="modal-body">
                <form class="smart-form">
                    <label class="label">{{{ trans('vendorManagement.vendorGroup') }}} <span class="required">*</span>:</label>
                    <select name="vendor_group" class="select2">
                        <option value="">{{ trans('general.selectAnOption') }}</option>
                        @foreach($externalVendorGroups as $vendorGroup)
                            <option value="{{ $vendorGroup->id }}">{{ $vendorGroup->name }}</option>
                        @endforeach
                    </select>
                    <em class="invalid" data-field="form_error-vendor_group"></em>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-md" data-action="actionSave">{{{ trans('forms.submit') }}}</button>
                <button class="btn btn-default btn-md" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>