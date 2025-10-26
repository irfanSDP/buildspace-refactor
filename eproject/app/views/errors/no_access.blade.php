<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="en" />
        <meta name="robots" content="noindex, nofollow" />
        <title>{{{ $title }}}</title>
        <style type="text/css" media="screen">
            * {box-sizing: border-box;padding: 0;margin: 0;}
            html{height:100%;}
            body {background:rgba(255, 0, 36, 0.7);font-family: Roboto,Arial,Helvetica,sans-serif;color:#fff;-webkit-font-smoothing: antialiased;height: 100%;}
            .block{width: 80%;margin: auto;position: absolute;top: 10%;left: 0;right: 0;bottom: 0;text-align:center;line-height:1.4;}
            .block h1 {font-size: 80px;font-weight: 700;margin: 0;text-transform: uppercase;letter-spacing: 4px;}
            .block h2 {font-size: 18px;font-weight: 400;text-transform: uppercase;margin-top: 20px;margin-bottom: 15px;}
        </style>
    </head>
    <body>
        <div class="block">
            <h1><?php echo $title ?></h1>
            <h2><?php echo $content?></h2>
        </div>
    </body>
</html>