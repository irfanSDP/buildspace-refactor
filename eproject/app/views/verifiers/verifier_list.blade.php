<?php $modalId = 'verifierListModal'; ?>
@if(count($verifiers) > 0)
    <div id="verifierList">
        <fieldset>
            <div class="verifiers">
                <label class="label">{{{ trans('verifiers.verifiers') }}}:</label>
                @foreach($verifiers as $userVerifier)
                    <?php
                    $names[] = $userVerifier->name;
                    ?>
                    <input type="hidden" name="verifiers[]" value="{{$userVerifier->id}}">
                @endforeach
                {{{ implode(', ', $names) }}}
                <?php $tenderStage = $tender->getTenderStage(); ?>
                @if ($tenderStage == PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER)
                    <?php $submitter = $tender->recommendationOfTendererInformation->updatedBy ?>
                @endif
                @if ($tenderStage == PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER)
                    <?php $submitter = $tender->listOfTendererInformation->updatedBy ?>
                @endif
                @if ($tenderStage == PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER)
                    <?php $submitter = $tender->callingTenderInformation->updatedBy ?>
                @endif
                &nbsp&nbsp&nbsp&nbsp&nbsp
                @if ($user->id == $submitter->id)
                    {{ Form::button(trans('verifiers.sendReminderEmail'), array('id' => 'btnResendEmail-tender-stage-' . $tender->getTenderStage(), 'class' => 'btn btn-primary btn-sm')) }}
                @endif
            </div>
        </fieldset>
    </div>
@endif