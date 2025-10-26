<div class="modal fade" id="editorModal" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="editorLabel">
                    <!-- Title -->
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">

                <div class="form-group col-6">
                    <label for="subsidiary-name-input" class="control-label">{{{ trans('subsidiaries.name') }}}:</label>
                    <input id="subsidiary-name-input" v-model="name" v-on="keyup: generateIdentifier" class="form-control" placeholder="{{{ trans('subsidiaries.subsidiary') }}}"/>
                    <em id="subsidiary-name-error" class="color-bootstrap-danger"></em>
                </div>

                <div class="form-group col-6">
                    <label for="subsidiary-identifier-input" class="control-label">{{{ trans('subsidiaries.identifier') }}}:</label><i class="fa fa-question-circle pull-right" data-toggle="tooltip" data-placement="left" title="{{{ trans('subsidiaries.identifierTooltip') }}}"></i>
                    <input id="subsidiary-identifier-input" v-model="identifier" class="form-control" placeholder="{{{ trans('subsidiaries.identifier') }}}"/>
                    <em id="subsidiary-identifier-error" class="color-bootstrap-danger"></em>
                </div>

                <div class="form-group col-6">
                    <label for="parent_id" class="control-label">{{{ trans('subsidiaries.parent') }}}:</label>
                    <br/>
                    <div class="fill-horizontal">
                        <select class="form-control" name="parent_id" style="width: 100%;">
                            <option value="">None</option>
                            @foreach ($parentSubsidiaryOptions as $subsidiary)
                                <option value="{{{ $subsidiary->id }}}">{{{ $subsidiary->fullName  }}}</option>
                            @endforeach
                        </select>
                        <em data-id="parent_id-error" class="color-bootstrap-danger"></em>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="submit-button" data-id=""><i class="fa fa-save" aria-hidden="true"></i> {{{ trans('forms.save') }}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->