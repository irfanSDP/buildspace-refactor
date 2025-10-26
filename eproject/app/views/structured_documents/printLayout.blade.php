<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>{{{ $document->title }}}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style type="text/css">
        html {
            font-family: serif; /* 1 */
            -ms-text-size-adjust: 100%; /* 2 */
            -webkit-text-size-adjust: 100%; /* 2 */
        }
        body {
            font-size: {{{ $document->font_size }}}px;
            margin: 20px;
        }
        ol {
            margin-left: 1em;
            padding-left: 0;
        }
        ol.clause {
            list-style-type: none;
            padding-left: 0;
            margin-left: 0;
        }
        ol.sub-clause {
            list-style-type: none;
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
        .heading {
            text-align: justify;
        }
        /*.title { background-color: red}*/
    </style>
</head>
<body>

<ol class="clause">
    @foreach($document->clauses as $clause)
        @include('structured_documents.clausePrintLayout', array('clause' => $clause))
    @endforeach
</ol>

<div class="no-break">

</div>

</body>
</html>