<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <div class="table-controls smart-form">
            @if($editableCostEstimate)
            <a class="btn btn-success btn-xs disabled" href="javascript:void(0);" id="cost_estimate-add_row-btn"><i class="fa fa-plus-circle"></i> {{ trans('requestForVariation.addRow') }}</a>
            <a class="btn btn-danger btn-xs disabled" href="javascript:void(0);" id="cost_estimate-del_row-btn"><i class="fa fa-times-circle"></i> {{ trans('requestForVariation.deleteRow') }}</a>
            <button class="btn btn-warning btn-xs" id="cost_estimate-import-btn" data-toggle="modal" data-target="#costEstimatesImportModal"><i class="fa fa-upload"></i> {{ trans('files.import') }}</button>
            @endif
            <div class="pull-right">
                <label class="label">{{ trans('requestForVariation.estimateCostOfProposedVariationWork') }}</label>
                @if((int)$requestForVariation->nett_omission_addition < 0)
                <span style="color:red;" class="rfv_nett_omission_addition-txt">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' (' . number_format(abs($requestForVariation->nett_omission_addition), 2, '.', ',')}}})</span>
                @else
                <span class="rfv_nett_omission_addition-txt">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' ' . number_format($requestForVariation->nett_omission_addition, 2, '.', ',') }}}</span>
                @endif
            </div>
        </div>
    </section>
</div>
<hr class="simple"/>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <div id="cost_estimate-table"></div>
    </section>
</div>
@if($editableCostEstimate)
<div class="modal fade" id="costEstimateDeleteModal" tabindex="-1" aria-labelledby="costEstimateDeleteModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{trans('requestForVariation.deleteCostEstimateItem')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
            </div>
            <div class="modal-body">
                <p>{{trans('requestForVariation.areYouSureToDelete')}}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('requestForVariation.cancel')}}</button>
                <button type="button" class="btn btn-primary btn-ok" data-record-id="">{{trans('requestForVariation.delete')}}</button>
            </div>
        </div>
    </div>
</div>
@endif
