<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Open Tender Print</title>
    <style>
        html {
            font-family: serif; /* 1 */
            -ms-text-size-adjust: 100%; /* 2 */
            -webkit-text-size-adjust: 100%; /* 2 */
        }
        body {
            font-size: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .border-dashed-underline {
            border-bottom: 1px dashed black;
        }
        .extra-large-font {
            font-size: 10px;
        }
        .border-dotted-underline {
            border-bottom: 1px dotted black;
        }
        .horizontal-header {
            width: 1px;
            padding-right: 20px;
        }
        .record-title {
            text-align: center;
            background-color: #696969;
            color: white;
        }
        .no-break {
            -webkit-column-break-inside: avoid; /* Chrome, Safari, Opera */
            page-break-inside: avoid; /* Firefox */
            break-inside: avoid; /* IE 10+ */
        }
        .pull-to-right {
            text-align: right;
        }
        .pull-to-left {
            text-align: left;
        }
        .pull-to-top {
            vertical-align: top;
        }
        .center {
            text-align: center;
        }
        .solid-borders {
            border: 1px solid black;
        }
        .solid-border-bottom {
            border-bottom: 1px solid black !important;
        }
        td .record-row {
            padding: 6px 5px;
            border-bottom: 1px solid black;
            border-left: 1px solid black;
            border-right: 1px solid black;
        }
        .remarks-cell {
            width: 1px;
            white-space: nowrap;
        }
        .extra-spacing {
            padding: 7px 0 0 0;
        }
        .record-name {
            white-space: nowrap;
        }
        .logo-cell {
            vertical-align: top;
            height: 100%;
            width: 200px;
        }
        .logo {
            height: 50px;
            width: 200px;
        }
    </style>
</head>
<body>
    <?php
        $subsidiary = ($project->subsidiary)? $project->subsidiary->name : false;
    ?>
    <!-- Header -->
    <table class=no-break>
        <tr>
            <td>
                <table>
                    <tr>
                        <td class="horizontal-header pull-to-top"><strong>Employer:</strong></td>
                        <td class="border-dotted-underline"><strong>{{{ ($subsidiary)?: $project->businessUnit->name }}}</strong></td>
                    </tr>
                    <tr>
                        <td class="horizontal-header pull-to-top"><strong>Project:</strong></td>
                        <td class="border-dotted-underline"><strong>
                            {{{ $project->title }}}
                            </strong>
                            <!-- blank line -->
                            <br/>
                            <br/>
                            <!-- blank line end -->
                        </td>
                    </tr>
                </table>
            </td>
            <td class="logo-cell">
                <!-- Company logo -->
                @if(!empty(trim($companyLogoSrc)) && file_exists($companyLogoSrc))
                    <img src="{{{ $companyLogoSrc }}}" class="logo">
                @endif
                <!-- Company logo end -->
            </td>
        </tr>
    </table>
    <!-- Header end -->

    <!-- Record of Tender Opening -->
    <table class="no-break">
        <tr>
            <td>
                <!-- Title -->
                <table class="record-title">
                    <tr>
                        <th class="extra-large-font">RECORD OF TENDER OPENING</th>
                    </tr>
                </table>
                <!-- Title end -->
            </td>
        </tr>
        <tr>
            <td>
                <!-- Record -->
                <table class="no-break solid-borders">
                    <!-- table head -->
                    <tr>
                        <th rowspan="2" class="center solid-borders">
                            ITEM
                        </th>
                        <th rowspan="2" class="center solid-borders">
                            TENDERER NAME
                        </th>
                        <?php $isFirstClause = true; ?>
                        @foreach($includedTenderAlternatives as $key => $tenderAlternative)
                            <?php
                            $p = ($key - 1) % 26;
                            $key = intval(($key - $p) / 26);
                            $alphabet = chr(65 + $p);
                            $displayText = $isFirstClause ? 'Base Tender' : 'Tender Alternative ' . $alphabet;
                            ?>
                            <th colspan="2" class="center solid-borders">
                                {{{ $displayText }}}
                            </th>
                            <?php $isFirstClause = false; ?>
                        @endforeach
                        <th rowspan="2" class="center solid-borders">
                            ENST <br/>MONEY <br/>(Y/N)
                        </th>
                        <th rowspan="2" class="center solid-borders">
                            REMARKS
                        </th>
                    </tr>
                    <tr>
                        @foreach($includedTenderAlternatives as $key => $tenderAlternative)
                            <th class="center solid-borders">
                               Tender Amount <br/>({{{ $project->modified_currency_code }}})
                            </th>
                            <th class="center solid-borders">
                                Completion Period <br/>({{{ $project->completion_period_metric }}})
                            </th>
                        @endforeach
                    </tr>
                    <!-- table head end -->

                    <!-- table body -->
                    <?php
                        $companyCount = 0;
                        $companyCountLimit = 10;
                        $totalColumns = 0;

                        $bsProjectMainInformation = $tender->project->getBsProjectMainInformation();

                        $bsTenderAlternativeIds = ($bsProjectMainInformation) ? $bsProjectMainInformation->projectStructure->tenderAlternatives()->lists('id') : [];

                        $companyTenderTenderAlternatives = [];

                        if(!empty($bsTenderAlternativeIds))
                        {
                            $companyTenderIds = [];
                            foreach($tender->selectedFinalContractors as $company)
                            {
                                $companyTenderIds[] = $company->pivot->id;
                            }

                            if(!empty($companyTenderIds))
                            {
                                $records = \PCK\Tenders\CompanyTenderTenderAlternative::whereIn('company_tender_id', $companyTenderIds)->whereIn('tender_alternative_id', $bsTenderAlternativeIds)->get()->toArray();

                                foreach($records as $record)
                                {
                                    if(!array_key_exists($record['company_tender_id'], $companyTenderTenderAlternatives))
                                    {
                                        $companyTenderTenderAlternatives[$record['company_tender_id']] = [];
                                    }

                                    $companyTenderTenderAlternatives[$record['company_tender_id']][$record['tender_alternative_id']] = $record;
                                }
                            }
                        }
                    ?>
                    <!-- rows with data -->
                    @foreach($tender->submittedTenderRateContractors as $company)
                        <?php
                        $isNotShortListed = !in_array($company->id, $shortlistedTendererIds);

                        if($isTechnicalAssessmentFormApproved && $isNotShortListed)
                        {
                            continue;
                        }

                        $companyCount++;

                        $tableRows = [];

                        foreach($company->tenderAlternativeData as $k => $records)
                        {
                            foreach($records as $tenderAlternative)
                            {
                                if(array_key_exists($company->pivot->id, $companyTenderTenderAlternatives) && array_key_exists($tenderAlternative['tender_alternative_id'], $companyTenderTenderAlternatives[$company->pivot->id]))
                                {
                                    $data = $companyTenderTenderAlternatives[$company->pivot->id][$tenderAlternative['tender_alternative_id']];

                                    $earnestMoney               = $data['earnest_money'];
                                    $remarks                    = $data['remarks'] ?? "";
                                    $contractorDiscount         = $data['discounted_amount'];
                                    $contractorCompletionPeriod = $data['completion_period'] + 0;
                                    $contractorAdjustment       = ((float)$data['contractor_adjustment_percentage']) ? $data['contractor_adjustment_percentage'] : $data['contractor_adjustment_amount'];
                                }
                                else
                                {
                                    $earnestMoney               = $company->pivot->earnest_money;
                                    $remarks                    = $company->pivot->remarks ?? "";
                                    $contractorDiscount         = $company->pivot->discounted_amount;
                                    $contractorCompletionPeriod = $company->pivot->completion_period + 0;
                                    $contractorAdjustment       = ((float)$company->pivot->contractor_adjustment_percentage) ? $company->pivot->contractor_adjustment_percentage : $company->pivot->contractor_adjustment_amount;
                                }

                                if(!array_key_exists($tenderAlternative['tender_alternative_id'], $tableRows))
                                {
                                    $tableRows[$tenderAlternative['tender_alternative_id']] = [
                                        'title'         => $tenderAlternative['tender_alternative_title'],
                                        'earnest_money' => $earnestMoney,
                                        'remarks'       => $remarks,
                                        'columns'       => []
                                    ];

                                    for($x=0;$x<count($company->tenderAlternativeData);$x++)
                                    {
                                        $tableRows[$tenderAlternative['tender_alternative_id']]['columns'][$x] = [
                                            'amount' => 0,
                                            'period' => 0,
                                            
                                        ];
                                    }
                                }

                                if(array_key_exists($k, $tableRows[$tenderAlternative['tender_alternative_id']]['columns']))
                                {
                                    $tableRows[$tenderAlternative['tender_alternative_id']]['columns'][$k] = [
                                        'amount' => $tenderAlternative['amount'],
                                        'period' => $tenderAlternative['period']
                                    ];
                                }
                            }
                        }
                        ?>
                        @foreach($tableRows as $row)
                            <tr>
                                <td class="center record-row">{{{ $companyCount }}}</td>
                                <td class="pull-to-left record-row record-name extra-large-font">{{{ $company->name }}} @if($row['title'])<br/><strong>{{$row['title']}}</strong>@endif</td>
                                @foreach($row['columns'] as $column)
                                <?php
                                    $pullRight = false;
                                    $amount = '-';

                                    if($column['amount'] != 0)
                                    {
                                        $pullRight = true;
                                        $amount = number_format($column['amount'], 2, ".", ",");
                                    }

                                    $totalColumns += 2;
                                ?>
                                <td class="{{{ $pullRight ? 'pull-to-right' : 'center' }}} record-row extra-large-font">{{{ $amount }}}</td>
                                <td class="center record-row extra-large-font">{{{ $column['period'] }}}</td>
                                @endforeach

                                <td class="center record-row">{{{ ($row['earnest_money']) ? 'Y' : 'N' }}}</td>
                                <td class="pull-to-left record-row remarks-cell">{{{ $row['remarks'] }}}</td>
                                <?php $totalColumns += 4;?>
                            </tr>
                            
                        @endforeach
                        
                    @endforeach
                    <!-- rows with data end-->

                    <!-- empty rows -->
                    @for(;$companyCount < $companyCountLimit;)
                        <?php $companyCount++ ?>
                        <?php $isLastRow = ($companyCountLimit === $companyCount); ?>
                        <?php $bottomBorderSolid = $isLastRow ? 'solid-border-bottom' : ''; ?>
                        <tr>
                            <td class="center record-row">{{{ $companyCount }}}</td>
                            <td class="pull-to-left record-row"></td>
                            @foreach($includedTenderAlternatives as $tenderAlternative)
                                <td class="pull-to-right record-row {{{ $bottomBorderSolid }}}"></td>
                                <td class="center record-row {{{ $bottomBorderSolid }}}"></td>
                                <?php $isFirstTenderAlternative = false; ?>
                            @endforeach
                            <td class="center record-row {{{ $bottomBorderSolid }}}"></td>
                            <td class="pull-to-left record-row {{{ $bottomBorderSolid }}}"></td>
                        </tr>
                    @endfor
                    <?php
                    //no tenderer with submitted rate
                    if($totalColumns == 0)
                    {
                        $totalColumns = count($includedTenderAlternatives) + 6;
                    }
                    ?>
                    <!-- empty rows end-->
                    @foreach($pteBudgetRecords as $pteBudgetRecord)
                    <tr>
                        <td class="center record-row solid-borders extra-large-font" colspan="2">PTE @if(strlen($pteBudgetRecord['title']) > 0)<strong>{{$pteBudgetRecord['title']}}</strong>@endif</td>
                        <td class="left record-row solid-borders extra-large-font" style="padding-left:28px;" colspan="{{($totalColumns-2)}}">{{{ $project->modified_currency_code }}} {{{ number_format($pteBudgetRecord['total'], 2, '.', ',') }}}</td>
                    </tr>
                    @endforeach
                    <!-- table body end -->
                </table>
                <!-- Record end -->
            </td>
        </tr>
    </table>
    <!-- Record of Tender Opening end -->

    <table>
        <tr>
            <th class="pull-to-left pull-to-top" style="width:20%">{{{ $tender->current_tender_name }}}</th>
            <td style="width:20%">&nbsp;</td>
            <td style="width:60%">
                <!-- Date and period -->
                <table class="no-break">
                    <tr>
                        <th class="extra-spacing pull-to-right">
                            Date of Tender Calling :
                        </th>
                        <td class="extra-spacing pull-to-left">
                            &nbsp;
                            {{{ \Carbon\Carbon::parse($tender->project->getProjectTimeZoneTime($tender->tender_starting_date))->format(\Config::get('dates.standard')) }}}
                        </td>

                        <th class="extra-spacing pull-to-right">
                            Tender Valid Until :
                        </th>
                        <td class="extra-spacing pull-to-left">
                            &nbsp;
                            {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($tender->validUntil()))->format(\Config::get('dates.standard')) }}}
                        </td>
                    </tr>
                    <tr>
                        <th class="extra-spacing pull-to-right">
                            Date of Tender Closing :
                        </th>
                        <td class="extra-spacing pull-to-left">
                            &nbsp;
                            {{{ \Carbon\Carbon::parse($tender->project->getProjectTimeZoneTime($tender->tender_closing_date))->format(\Config::get('dates.standard')) }}}
                        </td>

                        <th class="extra-spacing">
                            &nbsp;
                        </th>
                        <td class="extra-spacing">
                            &nbsp;
                        </td>
                    </tr>
                </table>
                <!-- Date and period end -->
            </td>
        </tr>
    </table>

    <!-- Verifiers etc. -->
    <em>The tenders as listed above were opened in our presence on this day.</em>

    <!-- fill up the gap -->
    <div>
        &nbsp;
    </div>
    <!-- fill up the gap end -->

    <table class="no-break">
        <tr>
            <!-- Each person for one td -->
            <?php
                $maxColumns = 3;
                $columnNumber = 0;
            ?>
            @foreach($selectedVerifiers as $verifier)
                <?php
                    $columnNumber++;
                    if($columnNumber > $maxColumns)
                    {
                        break;
                    }
                ?>
                <td style="width: 33%">
                    <table>
                        <tr>
                            <td class="horizontal-header">Name: </td>
                            <td>{{{ $verifier->name }}}</td>
                        </tr>
                        <tr>
                            <td class="horizontal-header">Company: </td>
                            <td>{{{ ($verifier->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER) && $subsidiary) ? $subsidiary : $verifier->company->name }}}</td>
                        </tr>
                        <tr>
                            <td class="horizontal-header">Date: </td>
                            <td>{{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($verifier->log->created_at))->format(Config::get('dates.created_and_updated_at_formatting')) }}}</td>
                        </tr>
                    </table>
                </td>
            @endforeach
            @for(;$columnNumber < $maxColumns; $columnNumber++)
                <td style="width: 33%">
                </td>
            @endfor
        </tr>
    </table>
    <!-- Verifiers etc. end -->

    <!-- Declaration -->
    <?php
        $blankSpaceLength = 60;
        $blankSpace = '';
        for($counter = 0; $counter < $blankSpaceLength; $counter++)
        {
            $blankSpace .= '_';
        }

        $halfSizedBlankSpace = '';
        for($counter = 0; $counter < ($blankSpaceLength/2); $counter++)
        {
            $halfSizedBlankSpace .= '_';
        }
    ?>
    <table class="no-break">
        <tr>
            <td>
                <table>
                    <tr>
                        <td class="extra-spacing">
                            <strong>1. We, </strong>{{{ $blankSpace }}} <strong>shall prepare and submit a Tender Report to the Employer on or before </strong>{{{ $halfSizedBlankSpace }}}<strong>.</strong>
                        </td>
                        <td class="extra-spacing">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td class="extra-spacing">
                            <strong>2. We, </strong>{{{ $blankSpace }}}<strong> confirm to have taken custody of all Earnest Money for safe keeping.</strong>
                        </td>
                        <td class="extra-spacing">
                            <table>
                                <tr>
                                    <td class="pull-to-right"><strong>Signature:</strong>&nbsp;</td>
                                    <td style="width: 150px; border-bottom: 1px solid black"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Declaration end -->
</body>