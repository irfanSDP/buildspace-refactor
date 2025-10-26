<?php


require_once(__DIR__.'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('costdata', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
