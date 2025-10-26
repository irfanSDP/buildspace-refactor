<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 ie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 ie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 ie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="<?php echo image_path("favicon.png")?>" type="image/x-icon" />
    <?php include_stylesheets() ?>
</head>
<body>
    <div class="error-container">
    <?php echo $sf_data->getRaw('sf_content')?>
    </div>
</body>
</html>
