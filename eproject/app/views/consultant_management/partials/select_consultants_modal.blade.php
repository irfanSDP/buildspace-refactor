<div class="modal fade" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="selectConsultantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-users"></i> {{ trans('general.consultantsList') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body no-padding">
                <div id="selectConsultantTableContainer"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="select_consultant_modal-btn"><i class="fa fa-check-square"></i> {{ trans('forms.select') }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>