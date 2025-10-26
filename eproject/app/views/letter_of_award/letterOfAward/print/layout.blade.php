<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ trans('letterOfAward.letterOfAward') }}</title>
    <style>
        .parent-clause-numbering {
            vertical-align: text-top;
            font-size: {{{ $printSettings['clause_font_size'] }}}px;
        }

        .contents {
            padding-left: 10px;
            font-size: {{{ $printSettings['clause_font_size'] }}}px;
        }

        .no-left-padding {
            padding-left: 0;
        }

        .standard-font-size {
            font-size: 12px;
        }

        .signature-padding {
            padding-left: 20px;
        }
        
        .new-page {
            page-break-before: always;
        }

        .bolded {
            font-weight: bold;
        }

        .root-clause-spacing {
            padding-top: 2.5em;
            padding-bottom: 10px;
        }

        .child-clause-spacing {
            padding-bottom: 10px;
        }
    </style>
</head>

<body>
    <table>
    
    <?php $curentClauseNumber = 1; ?>
    <?php $numberingString = ''; ?>
    @foreach ($clauses as $clause)
        <?php
            renderClauses($clause, $curentClauseNumber, $numberingString);
        
            if($clause['displayNumbering']) {
                $curentClauseNumber++;
            }
        ?>
    @endforeach

    <?php function renderClauses($clause, $curentClauseNumber, $numberingString) { ?>
        <?php $isRootClause = $clause['parentId'] === ''; ?>
        <?php $hasChildren = $clause['children'] !== ''; ?>

        @if ($isRootClause)
            <?php $numberingString = $curentClauseNumber; ?>
        @else
            <?php $numberingString .= '.' . $curentClauseNumber; ?>
        @endif

        <tr>
            @if($clause['displayNumbering'])
                <?php $trailingNumber = $isRootClause ? '.0' : ''; ?>
                <td class="parent-clause-numbering {{{ $isRootClause ? 'bolded root-clause-spacing' : 'child-clause-spacing' }}}">{{{ $numberingString . $trailingNumber }}}</td>
                <td class="contents {{{ $isRootClause ? 'root-clause-spacing' : 'child-clause-spacing' }}}">{{ $clause['contents'] }}</td>
            @else
                <td class="contents no-left-padding" colspan="2">{{ $clause['contents'] }}</td>
            @endif
        </tr>
        <?php
            if($hasChildren) {
                $childClauseNumber = 1;

                foreach($clause['children'] as $childClause) {
                    renderClauses($childClause, $childClauseNumber, $numberingString);

                    if($childClause['displayNumbering']) {
                        $childClauseNumber++;
                    }
                }
            }
        ?>
    <?php } ?>

    </table>

    <div class="standard-font-size new-page">
        {{ $signatory }}
    </div>
    <br>
    <br>
    <br>
    <div>
        <table class="standard-font-size">
            <tr>
                <td>{{ trans('letterOfAward.signature') }}: </td>
                <td>_______________________</td>
                <td class="signature-padding">{{ trans('letterOfAward.signature') }} ({{ trans('letterOfAward.witness') }}): </td>
                <td>_______________________</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>{{ trans('letterOfAward.name') }}: </td>
                <td>_______________________</td>
                <td class="signature-padding">{{ trans('letterOfAward.name') }}: </td>
                <td>_______________________</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>{{ trans('letterOfAward.designation') }}: </td>
                <td>_______________________</td>
                <td class="signature-padding">{{ trans('letterOfAward.designation') }}: </td>
                <td>_______________________</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>{{ trans('letterOfAward.date') }}: </td>
                <td>_______________________</td>
                <td class="signature-padding">{{ trans('letterOfAward.date') }}: </td>
                <td>_______________________</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>{{ trans('letterOfAward.companyStamp') }}:</td>
                <td>_______________________</td>
            </tr>
        </table>
    </div>
</body>
</html>