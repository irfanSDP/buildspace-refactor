<?php
    $userGuard = $sf_user->getGuardUser();
    if(isset($userGuard))
    {
        $sf_user->setCulture($userGuard->Profile->language);
    }
?>
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
    <?php include_javascripts() ?>
</head>
<body class="nihilo">
    <div id="easingNode"></div>
    <div id="loadingOverlay" class="pageOverlay">
        <table class="loadingMessage">
            <tr>
                <td style="width:26px;"><span id="preloaderContainer"></span></td>
                <td><h1><?php echo $userGuard->Profile->name ?><br /><i><?php echo $userGuard->username?></i></h1></td>
            </tr>
        </table>
    </div>
    <script type="text/javascript">
        //<![CDATA[
        var relativeUrlRoot = '<?php echo $sf_request->getRelativeUrlRoot()?>';
        dojoConfig = {
            async: true,
            has: {
                "dojo-firebug": <?php echo (sfConfig::get('sf_environment') == 'dev') ? 'true' : 'false'; ?>,
                "dojo-debug-messages": <?php echo (sfConfig::get('sf_environment') == 'dev') ? 'true' : 'false'; ?>
            },
            parseOnLoad: true,
            cacheBust: <?php echo (sfConfig::get('sf_environment') == 'dev') ? 1 : 0; ?>,
            packages: [{
                name: 'buildspace',
                location: '<?php echo (sfConfig::get('sf_environment') == 'dev') ? '../../../js/dojotoolkit/buildspace' : '../../../js/release/buildspace'; ?>',
                main: 'buildspace'
            },{
                name: "images",
                location: "../../../images"
            },{
                name: 'jquery',
                location: '../../../js/jquery',
                main: 'jquery.min'
            },{
                name: 'jqueryui',
                location: '../../../js/jquery',
                main: 'jquery-ui.min'
            }]
        };
        //]]>
    </script>
    <?php if (sfConfig::get('sf_environment') == 'dev'): ?>
        <script type="text/javascript" src="<?php echo javascript_path('/js/dojotoolkit/dojo/dojo.js.uncompressed.js')?>"></script>
    <?php else: ?>
        <script type="text/javascript" src="<?php echo javascript_path('/js/dojotoolkit/dojo/dojo.js')?>"></script>
    <?php endif; ?>
    <script type="text/javascript" src="<?php echo javascript_path('/js/preloader.js')?>"></script>
    <?php echo $sf_data->getRaw('sf_content')?>
</body>
</html>
