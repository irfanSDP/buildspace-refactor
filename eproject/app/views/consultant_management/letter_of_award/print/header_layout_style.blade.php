<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style type="text/css">
        html {
            font-family: serif; /* 1 */
            -ms-text-size-adjust: 100%; /* 2 */
            -webkit-text-size-adjust: 100%; /* 2 */
        }

        body {
            padding-bottom: 0;
            margin-bottom: 0;
        }

        p {
            margin-top: 0;
            margin-bottom: 0;
        }

        #watermark{position: fixed;left: 0;right: 0;opacity: 0.15;font-family: Arial, Helvetica, sans-serif;font-size: 5em;width: 100%;text-align: center;justify-content: center;align-content: center;z-index: 1000;display: grid;}
    </style>
</head>
<body>
    <span style='font-size:{{$fontSize}}px;'>
        @if(isset($referenceNo))
        <p style="padding-bottom:4px;">Ref. {{{$referenceNo}}}<br/></p>
        @endif
        {{$letterhead}}
    </span>
    <hr>

    <div id="watermark">{{{$watermark}}}</div>
</body>
</html>