<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <link rel="shortcut icon" href="<?php echo image_path("favicon.png")?>" type="image/x-icon" />
    <title>Buildspace - Project Management</title>

    <?php include_stylesheets() ?>
    <?php echo stylesheet_tag('gantt/ganttPrint', array('media'=>'print')) ?>
    <?php echo stylesheet_tag('../js/dojotoolkit/buildspace/resources/themes/Buildspace/icons.css') ?>

    <?php include_javascripts() ?>

    <?php if(sfConfig::get('sf_environment') == 'dev'):?>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/platform.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/date.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/i18nJs.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/ganttUtilities.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/ganttTask.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/ganttDrawerSVG.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/ganttGridEditor.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/Gantt/ganttMaster.js')?>"></script>
    <?php else: ?>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/platform.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/date.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/i18nJs.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/ganttUtilities.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/ganttTask.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/ganttDrawerSVG.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/ganttGridEditor.js')?>"></script>
        <script type="text/javascript" src="<?php echo javascript_path('/js/release/Gantt/ganttMaster.js')?>"></script>
    <?php endif; ?>

</head>
<body style="background-color: #fff;">
<?php echo $sf_data->getRaw('sf_content')?>
</body>
</html>