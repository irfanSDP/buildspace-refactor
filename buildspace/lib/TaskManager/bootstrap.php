<?php

$asyncDir = realpath(dirname(__FILE__).'/..');

require_once dirname(__FILE__) . '/../../config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::hasActive() ? ProjectConfiguration::getActive() : new ProjectConfiguration(realpath($asyncDir.'/..'));

// autoloader
$autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
$autoload->loadConfiguration(sfFinder::type('file')->name('autoload.yml')->in(array(
sfConfig::get('sf_symfony_lib_dir').'/config/config',
sfConfig::get('sf_config_dir'),
)));
$autoload->register();
