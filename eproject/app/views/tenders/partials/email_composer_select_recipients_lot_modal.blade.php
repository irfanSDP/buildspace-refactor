<div class="modal fade scrollable-modal" id="emailComposerSelectRecipientLOTModal" tabindex="-1" role="dialog" aria-labelledby="emailComposerSelectRecipientLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
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
                <?php $selectedContractors = $tender->listOfTendererInformation->selectedContractors;?>
                @if ($selectedContractors->isEmpty())
                   <div class="well padded txt-color-orangeDark">
                        {{ trans('tenders.noContractorsAssigned') }}
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table  table-hover" id="selected-recipients-table-stage-{{{PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER}}}">
                            <thead>
                                <tr>
                                    <th class="squeeze">
                                        {{ Form::checkbox('','', !empty($tender->listOfTendererInformation->selectedContractorsAddedByGCD) , array('id' => 'select-all-recipients-stage-' . PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER)) }}
                                    </th>
                                    <th style="vertical-align: middle;">{{ trans('tenders.contractors') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedContractors as $contractor)
                                    <?php $addedByGCD = $contractor->pivot->added_by_gcd ? '"color: blue;"' : null; ?>
                                    <tr>
                                        <td>
                                            {{ Form::checkbox($contractor->name, $contractor->id, $contractor->pivot->added_by_gcd) }}
                                        </td>
                                        <td style={{{$addedByGCD}}}>
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
                <button class="btn btn-primary" id="compose-email-send-button-stage-{{{PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER}}}" data-original-id="compose-email-send-button">{{ trans('general.send') }}</button>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#emailComposerPreviewModal">{{ trans('forms.back') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->