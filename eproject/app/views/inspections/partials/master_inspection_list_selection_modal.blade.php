<?php $modalId                         = isset($modalId) ? $modalId : 'masterInspectionListSelectionModal' ?>
<?php $breadcrumbId                    = isset($breadcrumbId) ? $breadcrumbId : 'inspectionListSelectionBreadcrumb'; ?>
<?php $inspectionListsTableId          = isset($inspectionListsTableId) ? $inspectionListsTableId : 'masterInspectionListSelectionTable'; ?>
<?php $inspectionListCategoriesTableId = isset($inspectionListCategoriesTableId) ? $inspectionListCategoriesTableId : 'masterInspectionListCategoriesSelectionTable'; ?>
<?php $saveButtonId                    = isset($saveButtonId) ? $saveButtonId : 'btnClonseSelectedMasterInspectionList'; ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{ trans('inspection.masterInspectionLists') }}
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col col-sm-12">
                        <ol id="{{ $breadcrumbId }}" class="breadcrumb bg-transparent border border-info"></ol>
                        <div id="{{ $inspectionListsTableId }}"></div>
                        <div id="{{ $inspectionListCategoriesTableId }}" hidden=""></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
                <button id="{{ $saveButtonId }}" class="btn btn-primary" style="display: none;" disabled>{{ trans('forms.save') }}</button>
            </div>
        </div>
    </div>
</div>