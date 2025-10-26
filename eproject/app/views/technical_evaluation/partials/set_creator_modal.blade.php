<div class="modal fade" id="creatorModal" tabindex="-1" role="dialog" aria-labelledby="creatorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="creatorLabel">
                    {{{ trans('technicalEvaluation.createNewSet') }}}
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <div class="row" v-if="!templateNameIsHidden">
                    <div class="form-group col-md-6">
                        <label class="control-label">{{{ trans('general.template') }}}:</label>
                        <label class="fill-horizontal">
                            @{{ templateName }}
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="set-work_category_id-input" class="control-label">{{{ trans('technicalEvaluation.workCategory') }}}:</label>
                        <label class="fill-horizontal">
                            <select id="set-work_category_id-input" class="select2" v-model="work_category_id" style="width: 100%;" required>
                                <?php $i = 0;?>
                                @foreach($workCategories as $workCategory)
                                    <option value="{{{ $workCategory->id }}}" {{{ (++$i == 1) ? 'selected' : '' }}}>
                                        {{{ $workCategory->name }}}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="form-group col-md-6 set-existing">
                        <label for="set-contract_limit_id-input" class="control-label">{{{ trans('technicalEvaluation.contractLimit') }}}:</label>
                        <label class="fill-horizontal">
                            <select id="set-contract_limit_id-input" class="fill-horizontal select2"  style="width: 100%;" v-model="contract_limit_id">
                                <option value="" selected>
                                    {{{ trans('technicalEvaluation.none') }}}
                                </option>
                                @foreach($contractLimits as $contractLimit)
                                    <option value="{{{ $contractLimit->id }}}">
                                        {{{ $contractLimit->limit }}}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="form-group col-md-6 set-new">
                        <label for="set-contract_limit-input" class="control-label">{{{ trans('technicalEvaluation.newContractLimit') }}}:</label>
                        <input id="set-contract_limit-input" v-model="contract_limit" class="form-control" placeholder="{{{ trans('technicalEvaluation.contractLimit') }}}"/>
                    </div>

                    <div class="form-group col-md-12" v-if="generalError">
                        <em id="create-general-error" class="color-bootstrap-danger">@{{ generalError }}</em>
                    </div>
                    <div class="form-group col-md-12" v-if="templateError">
                        <em class="color-bootstrap-danger">@{{ templateError }}</em>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-warning set-existing" id="set-new-contract_limit-button">{{{ trans('technicalEvaluation.setNewContractLimit') }}}</button>
                <button class="btn btn-warning set-new" id="set-existing-contract_limit-button">{{{ trans('technicalEvaluation.setExistingContractLimit') }}}</button>
                <button class="btn btn-primary submit-button"><i class='fa fa-save'></i> {{{ trans('forms.save') }}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->