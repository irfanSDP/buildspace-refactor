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
    <title>Buildspace - <?php echo get_slot("chartTitle", "Chart"); ?></title>
    <link rel="shortcut icon" href="<?php echo image_path("favicon.png")?>" type="image/x-icon" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
</head>
<body class="nihilo">
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
        gfxRenderer: "svg,silverlight,vml",
        cacheBust: <?php echo (sfConfig::get('sf_environment') == 'dev') ? 1 : 0; ?>
    };
    //]]>
</script>
<?php if (sfConfig::get('sf_environment') == 'dev'): ?>
    <script type="text/javascript" src="<?php echo javascript_path('/js/dojotoolkit/dojo/dojo.js.uncompressed.js')?>"></script>
<?php else: ?>
    <script type="text/javascript" src="<?php echo javascript_path('/js/dojotoolkit/dojo/dojo.js')?>"></script>
<?php endif; ?>
<?php echo $sf_data->getRaw('sf_content')?>
</body>
</html>