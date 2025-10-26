<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ trans('general.letterOfAppointment') }}</title>
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
            font-size: 15px;
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
    <table>{{$clauseHtml}}</table>

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