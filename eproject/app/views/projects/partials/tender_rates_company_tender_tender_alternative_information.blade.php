<dl class="dl-horizontal no-margin">
    <dt>{{ trans('tenders.tenderAmount') }} :</dt>
    <dd>
        <strong>
            {{{ $project->modified_currency_code }}} {{{ number_format($tenderAlternative->tender_amount, 2, ".", ",") }}}
        </strong>
    </dd>
    <dd><hr/></dd>
</dl>
@if ( $tenderAlternative->discounted_amount )
    <?php $generatedTopHeader = true; ?>

    <dl class="dl-horizontal no-margin">
        <dt>{{ trans("tenders.discountPercentage") }}:</dt>
        <dd>{{{ $tenderAlternative->discounted_percentage }}} %</dd>
        <dd><hr/></dd>
    </dl>

    <dl class="dl-horizontal no-margin">
        <dt>{{ trans("tenders.discountAmount") }}:</dt>
        <dd>{{{ $project->modified_currency_code }}} {{{ number_format($tenderAlternative->discounted_amount, 2, ".", ",") }}}</dd>
        <dd><hr/></dd>
    </dl>
@endif

@if ( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
    <?php $generatedTopHeader = true; ?>

    <dl class="dl-horizontal no-margin">
        <dt>{{ trans("tenders.proposedCompletionPeriod") }}:</dt>
        <dd>{{{ $tenderAlternative->completion_period  + 0}}} {{{ $tender->project->completion_period_metric }}}</dd>
        <dd><hr/></dd>
    </dl>

    @if($tenderAlternative->contractor_adjustment_amount == 0)
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans("tenders.adjustmentPercentage") }}:</dt>
            <dd>{{{ $tenderAlternative->contractor_adjustment_percentage }}} %</dd>
            <dd><hr/></dd>
        </dl>
    @endif

    @if($tenderAlternative->contractor_adjustment_percentage == 0)
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans("tenders.adjustmentAmount") }}:</dt>
            <dd>{{{ $project->modified_currency_code }}} {{{ number_format($tenderAlternative->contractor_adjustment_amount, 2, ".", ",") }}}</dd>
            <dd><hr/></dd>
        </dl>
    @endif
@endif