<?php

$isDebugMode = getenv('DEBUG_MODE') === 'true';

// this check prevents access to debug front controllers that are deployed by accident to production servers.
// feel free to remove this, extend it or make something more sophisticated.
if ($isDebugMode === false && !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', ''))) {
    die('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

require_once(__DIR__.'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('editor', 'dev', true);
sfContext::createInstance($configuration)->dispatch();
