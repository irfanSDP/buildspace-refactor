<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>{{{ $allDetails['settings']->title_text }}}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style type="text/css">
        html {
            font-family: serif; /* 1 */
            -ms-text-size-adjust: 100%; /* 2 */
            -webkit-text-size-adjust: 100%; /* 2 */
        }
        body {
            font-size: {{{ $allDetails['settings']['font_size'] }}}px;
            margin: 20px;
        }
        ol {
            margin-left: 1em;
            padding-left: 0;
        }
        ol.clause {
            list-style-type: decimal;
        }
        ol.sub-clause {
            list-style-type: lower-alpha;
        }
        ol.lower-alpha{
            list-style-type: lower-alpha;
        }
        li {
            -webkit-column-break-inside: avoid; /* Chrome, Safari, Opera */
            page-break-inside: avoid; /* Firefox */
            break-inside: avoid; /* IE 10+ */
        }
        .no-break {
            -webkit-column-break-inside: avoid; /* Chrome, Safari, Opera */
            page-break-inside: avoid; /* Firefox */
            break-inside: avoid; /* IE 10+ */
        }
        table {
            width: 100%;
        }
        td {
            padding-top: 14px;
        }
        .amount-line {
            padding-top: 1px;
        }
        .blank-space {
            border-bottom: 1px solid black;
        }
        td.occupy-min {
            width: 1px;
            white-space: nowrap;
        }
        p {
            margin-top: 0;
            margin-bottom: 0;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Address -->
    {{ $allDetails['address']->address }}
    <!-- Address End -->

    <!-- Clauses -->
    <ol class="clause">
    @foreach($allDetails['clauses'] as $clauseIndex => $clause)
        @if($clause instanceof PCK\FormOfTender\Clause)
            <li>
            {{ $clause->clause }}
            </li>
            <br/>
                <ol class="sub-clause">
                @foreach($clause->children->sortBy('sequence_number') as $subClause)
                    <li>
                    {{ $subClause->clause }}
                    </li>
                    <br/>
                @endforeach
                </ol>
        @elseif($clause instanceof \PCK\FormOfTender\TenderAlternativesPosition)
            @if( count($tenderAlternatives) > 0 )
            <li>
                @if($clauseIndex > 0)
                    And further, the
                @else
                    The
                @endif
                undersigned agrees to complete the Works as follows:
            </li>

            <br/>
                <ol class="lower-alpha">
                <?php $isFirstClause = true; ?>
                @foreach($tenderAlternatives as $key => $records)
                    <?php
                    $p = ($key - 1) % 26;
                    $key = intval(($key - $p) / 26);
                    $alphabet = chr(65 + $p);
                    ?>
                    <li style="text-align: justify">
                        <?php $displayText = $isFirstClause ? 'Base Tender' : 'Tender Alternative ' . $alphabet; ?>
                        <span class="bold">{{{ $displayText }}}</span>
                        @foreach($records as $tenderAlternative)
                            <div style="padding:0 0 4px 12px;">
                            {{ $tenderAlternative['description'] }}
                            @include('tender_alternatives.partials.amount', array('currencySymbol' => $currencySymbol, 'amount' => $tenderAlternative['amount'], 'amountInText' => isset($tenderAlternative['amountInText']) ? $tenderAlternative['amountInText'] : null ))
                            </div>
                        @endforeach
                    </li>
                    <br/>
                    <?php $isFirstClause = false; ?>
                @endforeach
                </ol>

            @endif
        @endif
    @endforeach
    </ol>
    <!-- Clauses End -->

    <div class="no-break">

        <!-- Addenda Layout -->

        <ol id="addenda" start="{{{ $addendaStartNumber }}}" class="clause no-break">
            <li>
                The undersigned confirms that the following Tender Addendums has been taken into account for the preparations of this Tender:
                <br/>
                <table>
                    <tr>
                        <td style="width: 60%">Addenda No.</td>
                        <td style="width: 10%"></td>
                        <td style="width: 30%">Date.</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                    </tr>
                    <?php $addendaMinimumRows = 3; $rowCount = 0; ?>
                    @if(isset($addendumFolders))
                    @foreach($addendumFolders as $addendumFolder)
                        <?php $rowCount++; ?>
                        <tr>
                            <td class="blank-space">{{{ $addendumFolder->name }}}</td>
                            <td></td>
                            <td class="blank-space">{{{ \Carbon\Carbon::parse($addendumFolder->project->getProjectTimeZoneTime($addendumFolder->created_at))->format(\Config::get('dates.submission_date_formatting')) }}}</td>
                        </tr>
                    @endforeach
                    @endif
                    @for(; $rowCount < $addendaMinimumRows; $rowCount++)
                        <tr>
                            <td class="blank-space">&nbsp;</td>
                            <td></td>
                            <td class="blank-space"></td>
                        </tr>
                    @endfor

                </table>
            </li>
        </ol>
        <!-- Addenda Layout End -->

        <!-- Signature Layout -->

        <table class="no-break">
            <tr>
                <td style="width: 10%;"></td>
                <td>
                    <table>
                        <tr>
                            <td>Dated this _____________ day of _____________ year _____________</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>

                                <?php
                                $array = [
                                        'Signature of Tenderer',
                                        'Name in full',
                                        'NRIC No.',
                                        'In the capacity of'
                                ];
                                ?>
                                @foreach($array as $field)
                                    <table class="">
                                        <tr>
                                            <td class="occupy-min">
                                                {{{ $field }}}
                                            </td>
                                            <td class="blank-space"></td>
                                        </tr>
                                    </table>
                                @endforeach

                            </td>
                        </tr>
                        <tr>
                            <td>
                                Duly authorised to sign this Tender for and on behalf of:
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 45px; padding-bottom: 15px">
                                <table>
                                    <tr>
                                        <td class="occupy-min">
                                            Tenderer's Seal or Chop
                                        </td>
                                        <td class="blank-space"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                $array2 = [
                                        'Signature Witness',
                                        'Name in Full',
                                        'NRIC No.',
                                        'Occupation',
                                        'Address'
                                ];
                                ?>
                                @foreach($array2 as $field)
                                    <table>
                                        <tr>
                                            <td class="occupy-min">
                                                {{{ $field }}}
                                            </td>
                                            <td class="blank-space"></td>
                                        </tr>
                                    </table>
                                @endforeach
                                <table>
                                    <tr>
                                        <td style="width: 1px">&nbsp;</td>
                                        <td class="blank-space"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:10%"></td>
            </tr>

        </table>

        <!-- Signature Layout End -->

    </div>
</body>
</html>