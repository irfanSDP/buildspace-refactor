<div class="modal fade" id="zoneEditorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title"></h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">{{ trans('eBiddingZone.name') }}</label>
                    <input type="text" class="form-control" id="input-zone-name" />
                    <em id="level-zone-name-error" style="color:#F00;"></em>
                </div>
                <div class="form-group">
                    <label class="control-label">{{ trans('eBiddingZone.description') }}</label>
                    <input type="text" class="form-control" id="input-zone-description" />
                </div>
                <div class="form-group">
                    <label class="control-label">{{ trans('eBiddingZone.upperLimit') }}</label>
                    <input type="number" class="form-control" step="0.01" min="0.01" id="input-zone-upper-limit" />
                    <em id="input-zone-upper-limit-error" style="color:#F00;"></em>
                </div>
                <div class="form-group">
                    <label class="control-label">{{ trans('eBiddingZone.colour') }}</label>
                    <input type="color" class="form-control color-picker" maxlength="7" id="input-zone-colour" />
                    <em id="input-zone-colour-error" style="color:#F00;"></em>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary submit-button" data-url=""><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
            </div>
        </div>
    </div>
</div>