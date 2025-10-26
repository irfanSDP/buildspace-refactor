<div class="modal scrollable-modal" id="costEstimatesImportModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-upload"></i>
                    {{ trans('requestForVariation.importCostEstimate') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('forms.close') }}"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">
                {{ Form::open(array('route' => array('requestForVariation.cost.estimate.import', $project->id, $requestForVariation->id),'method' => 'POST', 'id' => 'cost-estimate-import-form', 'class' => 'smart-form', 'files' => true )) }}
                    <fieldset>
                        <div class="row">
                            <div class="col col-xs-12 col-md-12 col-lg-12">
                                <section>
                                    <label class="label">{{ trans('requestForVariation.costEstimateFile') }} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('cost_estimates') ? 'state-error' : null }}}">
                                        {{ Form::file('cost_estimates', array('style' => 'height:100%')) }}
                                    </label>
                                    {{ $errors->first('cost_estimates', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <div class="row">
                            <div class="col col-xs-12 col-md-12 col-lg-12">
                                <section>
                                    <label class="checkbox">
                                        {{ Form::checkbox('remove_previous_data') }}
                                         <i></i> {{ trans('forms.removePreviousData') }}
                                    </label>
                                </section>
                            </div>
                        </div>
                    </fieldset>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-action="form-submit" data-target-id="cost-estimate-import-form"><i class="fa fa-upload"></i> {{ trans('files.import')}}</button>
            </div>
        </div>
    </div>
</div>