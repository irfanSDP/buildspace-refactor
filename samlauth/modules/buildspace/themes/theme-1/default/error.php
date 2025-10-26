<?php
/**
 * Do not allow to frame simpleSAMLphp pages from another location.
 * This prevents clickjacking attacks in modern browsers.
 *
 * If you don't want any framing at all you can even change this to
 * 'DENY', or comment it out if you actually want to allow foreign
 * sites to put simpleSAMLphp in a frame. The latter is however
 * probably not a good security practice.
 */
header('X-Frame-Options: DENY');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
    <title><?php echo $this->configuration->getString('application_title')?></title>

    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="googlebot" content="noarchive, nofollow" />
	<link rel="icon" type="image/png" href="/<?php echo $this->data['baseurlpath']; ?>resources/buildspacetheme1/favicon.png"/>
    <link rel="stylesheet" type="text/css" href="/<?php echo $this->data['baseurlpath']; ?>resources/buildspacetheme1/css/main.min.css">
</head>
<body>
    <div class="content">
        <h1><?php echo $this->t($this->data['dictTitle']); ?></h1>
        <p><?php
            echo htmlspecialchars($this->t($this->data['dictDescr'], $this->data['parameters']));?></p>
        <p>
            <?php echo $this->t('report_trackid'); ?>
            <?php echo $this->data['error']['trackId']; ?>
        </p>
    </div>
    <div class="clear"></div>
    <div class="footer-bar">
        <div class="footer content clearfix">
            <ul id="footer-list">
                <li>BuildSpace eProject © <?php echo date('Y')?>. All rights reserved.</li>
            </ul>
        </div>
    </div>
</body>
</html>
