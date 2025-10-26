<div class="modal fade" id="remarkInputModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h6 class="modal-title"><i class="fa fa-pencil-alt"></i> {{trans('general.remarks')}}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <label for="remarks-input"></label>
                <input id="remarks-input" autofocus="autofocus" class="form-control text-indent" maxlength="100"
                @if(!$currentUser->hasCompanyProjectRole($project, PCK\Filters\OpenTenderFilters::editorRoles($project)) || !$currentUser->isEditor($project))
                disabled @endif/>
            </div>
            <div class="modal-footer">
                @if($currentUser->hasCompanyProjectRole($project, PCK\Filters\OpenTenderFilters::editorRoles($project)) && $currentUser->isEditor($project))
                    <button class="btn btn-primary" id="remarks-save-button"><i class="fa fa-save"></i> {{trans('forms.save')}}</button>
                @endif
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->