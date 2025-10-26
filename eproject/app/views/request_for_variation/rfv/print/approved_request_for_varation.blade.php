<!DOCTYPE html>
<?php use PCK\RequestForVariation\RequestForVariationActionLog; ?>
<html lang="en-us">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>{{ trans('requestForVariation.requestForVariation') . ' ' . trans('general.summary') }}</title>
        <style type='text/css'>
            * {
                box-sizing: border-box;
                margin: 0;
            }
            html {
                font-family: sans-serif; /* 1 */
                -ms-text-size-adjust: 100%; /* 2 */
                -webkit-text-size-adjust: 100%; /* 2 */
            }
            body {
                font-size: 14px;
            }
            .header-table {
                width: 100%;
            }
            .text-bold {
                font-weight: bold;
            }
            .text-center {
                text-align: center;
            }
            .text-left {
                text-align: left;
            }
            .text-right {
                text-align: right;
            }
            .text-bottom {
                vertical-align: bottom;
            }
            .text-underline {
                text-decoration: underline;
            }
            .margin-bottom-5px {
                margin-bottom: 5px;
            }
            .logo-cell {
                vertical-align: top;
                height: 100%;
                width: 200px;
            }
            .logo {
                width: 200px;
                height: 125px;
            }
            .label-cell {
                width: 180px;
                font-weight: bold;
                vertical-align: top;
            }
            .bordered-cell {
                border: 1px solid rgba(212, 212, 212, .75);
                padding: 2px;
            }
            .new-page {
                page-break-before: always;
            }
            tr {
                page-break-inside: avoid;
            }
            .table-width-full {
                width: 100%;
                border: 1px solid rgba(212, 212, 212, .75);
                border-collapse: collapse;
            }
            .table-width-full thead {
                border: 1px solid rgba(212, 212, 212, .75);
                background-color: #8DEAFF;
            }
            .table-width-full tbody tr:nth-child(even) {
                background-color: #F4F4F4;
            }
            .border-collapse {
                border-collapse: collapse;
            }
            .border-top-white {
                border-top: 1px solid #FFF;
            }
            .border-right-white {
                border-right: 1px solid #FFF;
            }
            .border-bottom-white {
                border-bottom: 1px solid #FFF;
            }
            .border-left-white {
                border-left: 1px solid #FFF;
            }
        </style>
    </head>
    <body>
        <?php $currencyCode = $requestForVariation->project->getModifiedCurrencyCodeAttribute($requestForVariation->project->modified_currency_code); ?>
        <table class="header-table">
            <tr>
                <td>&nbsp;</td>
                <td rowspan="4" class="logo-cell text-right">
                    @if(!empty(trim($companyLogoSrc)) && file_exists($companyLogoSrc))
                    <img src="{{{ $companyLogoSrc }}}" class="logo">
                    @endif
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="text-bottom"><h2>{{ trans('requestForVariation.requestForVariation') . ' ' . trans('general.summary') }}</h2></td>
            </tr>
            <tr>
                <td class="text-bottom text-bold">{{ trans('general.forInternalCirculationOnly') }}</td>
            </tr>
        </table>
        <hr>
        <table>
            <tr>
                <td class="label-cell">{{ trans('projects.project') }}</td>
                <td class="bordered-cell">{{{ $requestForVariation->project->title }}}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('requestForVariation.rfvNumber') }}</td>
                <td class="bordered-cell">{{{ $requestForVariation->rfv_number }}}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('requestForVariation.aiNumber') }}</td>
                <td class="bordered-cell">{{{ $requestForVariation->ai_number }}}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('requestForVariation.descriptionForProposedVariationWork') }}</td>
                <td class="bordered-cell">{{{ $requestForVariation->description ?? '-' }}}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('requestForVariation.reasonForVariation') }}</td>
                <td class="bordered-cell">{{{ $requestForVariation->reasons_for_variation ?? '-' }}}</td>
            </tr>
            <tr>
                <td class="label-cell">{{ trans('requestForVariation.categoryOfRfv') }}</td>
                <td class="bordered-cell">{{{ $requestForVariation->requestForVariationCategory->name }}}</td>
            </tr>
            <tr>
                <?php $style = (int)$requestForVariation->nett_omission_addition < 0 ? "color:red" : ''; ?>
                <td class="label-cell">{{ trans('requestForVariation.estimateCostOfProposedVariationWork') }}</td>
                <td class="bordered-cell" style="{{ $style }}"><span>{{{ $requestForVariation->project->getModifiedCurrencyCodeAttribute($requestForVariation->project->modified_currency_code) . ' ' . number_format($requestForVariation->nett_omission_addition, 2, '.', ',')}}}</span></td>
                </tr>
            <tr>
                <td class="label-cell">{{ trans('requestForVariation.timeImplication') }}</td>
                <td class="bordered-cell">{{{ $requestForVariation->time_implication ?? null }}}</td>
            </tr>
        </table>
        <br>
        <div>
            <h4 class="text-underline margin-bottom-5px">{{ trans('forms.uploadedFiles') }}</h4>
            <table class="table-width-full">
                <thead>
                    <tr>
                        <th style="width:50%;">{{ trans('requestForVariation.filename') }}</th>
                        <th>{{ trans('forms.uploader') }}</th>
                        <th style="width:25%;">{{ trans('general.date') . ' & ' . trans('general.time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($uploadedFiles))
                        <tr>
                            <td colspan="3" class="text-center">{{ trans('general.noFilesUploaded') }}</td>
                        </tr>
                    @else
                        @foreach($uploadedFiles as $uploadedFile)
                            <tr>
                                <td>{{{ $uploadedFile['fileName'] }}}</td>
                                <td class="text-center">{{{ $uploadedFile['uploader'] }}}</td>
                                <td class="text-center">{{{ $uploadedFile['upload_date'] }}}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <br>
        <div>
            <h4 class="text-underline margin-bottom-5px">{{ trans('requestForVariation.financialStanding') }}</h4>
            <table class="table-width-full">
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
                        <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['contingencySum'], 2) }}}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('requestForVariation.total') }}</td>
                        <td colspan="2">{{{ $currencyCode . ' ' . number_format($financialStandingData['cncTotal'], 2) }}}</td>
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
                        <td>{{{ $currencyCode . ' ' . $financialStandingData['accumulativeApprovePlusCurrentProposedRfv'] }}}</td>
                        <td>{{{ $financialStandingData['accumulativeApprovePlusCurrentProposedRfvPercentage'] }}}</td>
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
                        <?php $style = (int)$financialStandingData['balanceOfContingency'] < 0 ? "color:red" : ''; ?>
                        <td>{{ trans('requestForVariation.balanceOfContingency') }}</td>
                        <td colspan="2" style="{{ $style }}">{{{ $currencyCode . ' ' . number_format($financialStandingData['balanceOfContingency'], 2) }}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($requestForVariation->showKpiLimitTable())
        <br>
        <div>
            <h4 class="text-underline margin-bottom-5px">{{ trans('requestForVariation.kpiLimit') }}</h4>
            <table class="table-width-full">
                <thead>
                    <tr>
                        <th>{{ trans('requestForVariation.categoryOfRfv') }}</th>
                        <th>{{ trans('general.max') }} (%)</th>
                        <th>{{ trans('general.current') }} (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">{{{ $requestForVariation->requestForVariationCategory->name }}}</td>
                        <td class="text-center" style="width:20%;">{{{ $maxKpiLimit}}}</td>
                        <?php $style = ($currentKpiLimit > $maxKpiLimit) ? 'color:red' : null; ?>
                        <td class="text-center" style="{{ $style }}">{{{ number_format($currentKpiLimit, 2, '.', '') }}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        <br>
        <div>
            <h4 class="text-underline margin-bottom-5px">{{ trans('verifiers.verifiers') }}</h4>
            <table class="table-width-full">
                <thead>
                    <tr>
                        <th class="text-center" style="width:10%;">No.</th>
                        <th class="text-center">{{ trans('general.name') }}</th>
                        <th class="text-center" style="width:25%;">{{ trans('verifiers.verifiedAt') }}</th>
                        <th class="text-center" style="width:15%;">{{ trans('requestForVariation.status') }}</th>
                        <th class="text-center" style="width:20%;">{{ trans('general.remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($verifiers->count())
                        @foreach($approvalRecords as $idx => $record)
                        <tr>
                            <td class="text-center">{{{ $idx + 1 }}}</td>
                            <td class="text-center">{{{ $record->user->name }}}</td>
                            <td class="text-center">{{ Carbon\Carbon::parse($record->created_at)->format(\Config::get('dates.readable_timestamp')) }}</td>
                            <td class="text-center">@if($record->approved == RequestForVariationActionLog::ACTION_TYPE_RFV_APPROVED) {{ trans('requestForVariation.approved') }} @else {{ trans('requestForVariation.rejected') }} @endif</td>
                            <td>{{ nl2br($record->remarks) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="text-center" colspan="5">{{ trans('requestForVariation.noVerifierAssigned') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="new-page">
            <h4 class="text-underline margin-bottom-5px">{{ trans('requestForVariation.costEstimate') }}</h4>
            <table class="table-width-full">
                <thead>
                    <tr>
                        <th colspan="5" class="text-center border-bottom-white border-right-white border-collapse"></th>
                        <th colspan="3" class="text-center border-bottom-white border-left-white border-collapse">{{ trans('requestForVariation.budget') }}</th>
                    </tr>
                    <tr>
                        <th class="text-center border-top-white border-right-white border-bottom-white border-collapse" style="width:5%;">No.</th>
                        <th class="text-center border-top-white border-right-white border-bottom-white border-collapse">{{ trans('requestForVariation.billRef') }}</th>
                        <th class="text-center border-top-white border-right-white border-bottom-white border-collapse">{{ trans('requestForVariation.description') }}</th>
                        <th class="text-center border-top-white border-right-white border-bottom-white border-collapse" style="width:10%;">{{ trans('requestForVariation.type') }}</th>
                        <th class="text-center border-top-white border-right-white border-bottom-white border-collapse" style="width:7%;">{{ trans('requestForVariation.unit') }}</th>
    
                        <th class="text-center border-top-white border-left-white border-bottom-white border-collapse">{{ trans('requestForVariation.rate') }}</th>
                        <th class="text-center border-top-white border-left-white border-bottom-white border-collapse">{{ trans('requestForVariation.qty') }}</th>
                        <th class="text-center border-top-white border-left-white border-bottom-white border-collapse">{{ trans('requestForVariation.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($variationOrderItems as $key => $item)
                        <tr>
                            <?php
                                if($item['type'] == PCK\Buildspace\VariationOrderItem::TYPE_HEADER)
                                {
                                    $headerNumber = isset($item['level']) && is_numeric($item['level']) ? $item['level'] + 1 : 1;
                                    $typeText     = PCK\Buildspace\VariationOrderItem::TYPE_HEADER_TEXT . ' ' . $headerNumber;
                                }
                                else
                                {
                                    $typeText = PCK\Buildspace\VariationOrderItem::TYPE_WORK_ITEM_TEXT;
                                }
                            ?>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-center border-left-white">{{ $item['bill_ref'] }}</td>
                            <td class="text-left border-left-white">{{ $item['description'] }}</td>
                            <td class="text-center border-left-white">{{ $typeText }}</td>
                            <td class="text-center border-left-white">{{ $item['uom_symbol'] }}</td>
                            <td class="text-right border-left-white">{{ number_format($item['reference_rate'], 2, '.', ',') }}</td>
                            <td class="text-right border-left-white">{{ number_format($item['reference_quantity'], 2, '.', ',') }}</td>
                            <td class="text-right border-left-white">{{ number_format($item['reference_amount'], 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="8" class="text-right">{{ trans('requestForVariation.estimateCostOfProposedVariationWork') }}: {{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' ' . number_format($requestForVariation->nett_omission_addition, 2, '.', ',') }}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
</html>