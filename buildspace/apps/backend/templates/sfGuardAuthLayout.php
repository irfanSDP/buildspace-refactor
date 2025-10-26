<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php echo include_title() ?>
    <link rel="shortcut icon" href="<?php echo image_path("favicon.png")?>" type="image/x-icon" />
    <?php include_stylesheets() ?>
</head>
<body style="overflow:hidden;cursor:default;">
<div id="pageContent">
    <div id="centerContent">
        <div class="contentRight">
            <div class="contentLeft">&nbsp;</div>
            <?php echo image_tag('buildspace-logo.png', array('id'=>'buildspaceLogo')) ?>
            <div class="content"><?php echo $sf_data->getRaw('sf_content')?></div>
        </div>
    </div>

    <div class="clear"></div>

    <div id="bottomRight">
        BuildSpace Pro &copy; <?php echo date('Y')?>. All rights reserved.
    </div>
</div>
</body>
</html>