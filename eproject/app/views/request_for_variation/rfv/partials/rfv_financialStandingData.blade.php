<?php $currencyCode = $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code); ?>
<table class="table table-bordered table-condensed table-striped table-hover smallFont">
    <thead>
        <tr>
            <th colspan="3">{{ trans('requestForVariation.financialStanding') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ trans('requestForVariation.originalContractSum') }}</td>
            <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['originalContractSum'], 2) }}}</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.lessContingency') }}</td>
            <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['contingencySum'], 2)  }}}</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.total') }}</td>
            <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['cncTotal'], 2)  }}}</td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.accumulativeApprovedRFV') }}</td>
            <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['accumulativeApprovedRfvAmount'], 2) }}}</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.proposedRFV') }}</td>
            <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['proposedRfvAmount'], 2) }}}</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.accumulativeApprovedRFV') }} + {{ trans('requestForVariation.proposedRFV') }}</td>
            <td>{{{ $currencyCode . ' ' . number_format($financialStandingData['addOmitTotal'], 2) }}}</td>
            <td>{{{ $financialStandingData['addOmitTotalPercentage'] }}}</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.accumulativeApprovedRFV') }} + {{ trans('requestForVariation.currentRfv') }}</td>
            <td>{{ $currencyCode . ' ' . $financialStandingData['accumulativeApprovePlusCurrentProposedRfv'] }}</td>
            <td>{{ $financialStandingData['accumulativeApprovePlusCurrentProposedRfvPercentage'] }}</td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.anticipatedContractSum') }}</td>
            <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['anticipatedContractSum'], 2) }}}</td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td>{{ trans('requestForVariation.balanceOfContingency') }}</td>
            @if ($financialStandingData['balanceOfContingency'] < 0)
                <td colspan="2" style="color:red;">{{{ $currencyCode . ' (' . number_format((float) abs($financialStandingData['balanceOfContingency']), 2, '.', ',') . ' )' }}}</td>
            @else
                <td colspan="2">{{{ $currencyCode . ' ' . number_format((float) abs($financialStandingData['balanceOfContingency']), 2, '.', ',') }}}</td>
            @endif
        </tr>
    </tbody>
</table>
