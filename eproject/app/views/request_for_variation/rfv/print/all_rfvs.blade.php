<!DOCTYPE html>
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
                <td class="text-bottom"><h2>{{ trans('requestForVariation.listOfAllRfvs') }}</h2></td>
            </tr>
            <tr>
                <td class="text-bottom text-bold">{{ trans('general.forInternalCirculationOnly') }}</td>
            </tr>
        </table>
        <hr>
        <p>&nbsp;</p>
        <div>
            <table class="table-width-full">
                <thead>
                    <tr>
                        <th>{{ trans('requestForVariation.overallCostEstimateForRFV') }}</th>
                        <th>{{ trans('requestForVariation.proposedCostEstimateForRFV') }}</th>
                        <th>{{ trans('requestForVariation.approvedRFVAmount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' ' . $rfvOverallAmountByUser }}</td>
                        <td class="text-center">{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' ' . $rfvProposedCostEstimate }}</td>
                        <td class="text-center">{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' ' . $accumulativeApprovedRfvAmountByUser }}</td>
                    </tr>
                </tbody>
            </table>
            <p>&nbsp;</p>
            <table class="table-width-full">
                <thead>
                    <tr>
                        <th style="width:10%;" class="border-left-white">{{ trans('requestForVariation.rfvNumber') }}</th>
                        <th style="width:10%;" class="border-left-white">{{ trans('requestForVariation.aiNumber') }}</th>
                        <th style="width:35%;" class="border-left-white">{{ trans('requestForVariation.description') }}</th>
                        <th style="width:25%;" class="border-left-white">{{ trans('requestForVariation.categoryOfRfv') }}</th>
                        <th style="width:10%;" class="border-left-white">{{ trans('requestForVariation.nettOmissionAddition') }}</th>
                        <th style="width:10%;" class="border-left-white">{{ trans('requestForVariation.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requestForVariations as $rfv)
                      <tr>
                        <td class="text-center border-left-white">{{ $rfv['rfvNumber'] }}</td>
                        <td class="text-center border-left-white">{{ $rfv['aiNumber'] }}</td>
                        <td class="text-center border-left-white">{{ $rfv['description'] }}</td>
                        <td class="text-center border-left-white">{{ $rfv['rfvCategory'] }}</td>
                        <td class="text-right border-left-white">{{ $rfv['nettOmissionAddition'] }}</td>
                        <td class="text-center border-left-white">{{ $rfv['statusText'] }}</td>
                      </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </body>
</html>