<div class="modal scrollable-modal" id="assignSubsidiariesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-cubes"></i>
                    {{ trans('modulePermissions.assignSubsidiaries') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div class="form-inline padded-bottom">
                  <div class="form-group">
                    <label>{{ trans('general.name') }}</label>
                    <input type="text" class="form-control" value="@{{ userName }}" readonly>
                    <span style="padding:2px;">&nbsp;</span>
                    <input type="button" data-action="assignSubsidiaries" class="btn btn-primary pull-right" value="{{ trans('forms.save') }}"/>
                  </div>
                </div>
                <br />
                <div id="example-table"></div>
            </div>
        </div>
    </div>
</div>