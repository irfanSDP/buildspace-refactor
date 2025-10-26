<div class="modal fade scrollable-modal" id="emailComposerSelectRecipientROTModal" tabindex="-1" role="dialog" aria-labelledby="emailComposerSelectRecipientLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="emailComposerSelectRecipientLabel">
                    {{ trans('forms.selectRecipients') }}
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <?php $selectedContractors = $tender->recommendationOfTendererInformation->selectedContractors; ?>
                @if ($selectedContractors->isEmpty())
                    <div class="well padded txt-color-orangeDark">
                        {{ trans('tenders.noContractorsAssigned') }}
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table  table-hover" id="selected-recipients-table-stage-{{{PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER}}}">
                            <thead>
                                <tr>
                                    <th class="squeeze">
                                        {{ Form::checkbox('','', true, array('id' => 'select-all-recipients-stage-' . PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER)) }}
                                    </th>
                                    <th style="vertical-align: middle;">{{ trans('tenders.contractors') }}</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach ($selectedContractors as $contractor)
                                    <tr>
                                        <td>
                                            {{ Form::checkbox($contractor->name, $contractor->id, true) }}
                                        </td>
                                        <td>
                                            {{{ $contractor->name }}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                @if ($selectedContractors->isEmpty())
                    <button class="btn btn-primary" id="compose-email-send-button-stage-{{{PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER}}}" data-original-id="compose-email-send-button" disabled>{{ trans('general.send') }}</button>
                @else
                    <button class="btn btn-primary" id="compose-email-send-button-stage-{{{PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER}}}" data-original-id="compose-email-send-button">{{ trans('general.send') }}</button>
                @endif
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#emailComposerPreviewModal">{{ trans('forms.back') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->