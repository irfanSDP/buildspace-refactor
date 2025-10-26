<?php

// ------------------------------------------------------------
// ProjectConfiguration bootstrap (Symfony 1.x, Laragon/Docker)
// ------------------------------------------------------------

// --- project root ---
$root = dirname(__DIR__);

// --- path to SimpleSAMLphp (samlauth) ---
$sspBase = (DIRECTORY_SEPARATOR === '\\')
    ? 'C:/laragon/www/samlauth'
    : '/usr/local/etc/buildspace/samlauth';

// *** Force SSP to use its own dirs (BEFORE requiring SSP autoloader) ***
// putenv('SIMPLESAMLPHP_BASE_DIR='   . $sspBase);
// putenv('SIMPLESAMLPHP_CONFIG_DIR=' . $sspBase . '/config');
// putenv('SIMPLESAMLPHP_LOG_DIR='    . $sspBase . '/log');
// putenv('SIMPLESAMLPHP_DATA_DIR='   . $sspBase . '/data');

if (!defined('SIMPLESAMLPHP_CONFIG_DIR')) define('SIMPLESAMLPHP_CONFIG_DIR', $sspBase . '/config');
if (!defined('SIMPLESAMLPHP_LOG_DIR'))    define('SIMPLESAMLPHP_LOG_DIR',    $sspBase . '/log');
if (!defined('SIMPLESAMLPHP_DATA_DIR'))   define('SIMPLESAMLPHP_DATA_DIR',   $sspBase . '/data');

// --- Composer + .env (optional but recommended) ---
$composerAutoload = $root . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    if (class_exists(\Dotenv\Dotenv::class)) {
        \Dotenv\Dotenv::createImmutable($root)->safeLoad();
    }
}

// --- resolve symfony/lib path ---
$symfonyLibDir = getenv('SYMFONY_LIB_DIR');
if (!$symfonyLibDir) {
    $symfonyLibDir = (DIRECTORY_SEPARATOR === '\\')
        ? 'C:/laragon/www/symfony/lib'
        : '/usr/local/etc/buildspace/symfony/lib';
}
$sfCoreAutoload = rtrim($symfonyLibDir, '/\\') . '/autoload/sfCoreAutoload.class.php';
if (!file_exists($sfCoreAutoload)) {
    trigger_error(
        "sfCoreAutoload not found. Looked at: {$sfCoreAutoload}\n" .
        "Set SYMFONY_LIB_DIR to your symfony/lib directory (e.g. C:/laragon/www/symfony/lib).",
        E_USER_ERROR
    );
}

// --- SimpleSAMLphp autoloader ---
$samlauthAutoload = getenv('SAMLAUTH_AUTOLOAD');
if (!$samlauthAutoload) {
    $samlauthAutoload = $sspBase . '/lib/_autoload.php';
}
if (!file_exists($samlauthAutoload)) {
    trigger_error(
        "_autoload.php for samlauth not found. Looked at: {$samlauthAutoload}\n" .
        "Set SAMLAUTH_AUTOLOAD to your samlauth/lib/_autoload.php.",
        E_USER_ERROR
    );
}

// --- require core autoloaders ---
require_once $sfCoreAutoload;
require_once $samlauthAutoload;

sfCoreAutoload::register();

// Optional: explicit IdP endpoints so the plugin never builds /saml2 relative URLs
sfConfig::set('app_saml_idp_sso',      'https://auth.buildspace.local/saml2/idp/SSOService.php');
sfConfig::set('app_saml_idp_slo',      'https://auth.buildspace.local/saml2/idp/SingleLogoutService.php');
sfConfig::set('app_saml_idp_metadata', 'https://auth.buildspace.local/saml2/idp/metadata.php');

sfConfig::set('app_sfSAMLPlugin_idp_sso',      'https://auth.buildspace.local/saml2/idp/SSOService.php');
sfConfig::set('app_sfSAMLPlugin_idp_slo',      'https://auth.buildspace.local/saml2/idp/SingleLogoutService.php');
sfConfig::set('app_sfSAMLPlugin_idp_metadata', 'https://auth.buildspace.local/saml2/idp/metadata.php');

// Optional SP info
sfConfig::set('app_saml_sp_name', 'buildspace-sp');
sfConfig::set('app_saml_acs_url', 'https://bq.buildspace.local/saml/acs');

// --- OPTIONAL: Doctrine 1 via Composer ---
$vendorDoctrine = $root . '/vendor/lexpress/doctrine1/lib/Doctrine.php';
if (file_exists($vendorDoctrine)) {
    require_once $vendorDoctrine;
}

// --- local libs autoloaders (existing) ---
require_once $root . '/lib/autoloader/SplClassLoader.php';
require_once $root . '/lib/vendor/GuzzleHttp/functions.php';
require_once $root . '/lib/vendor/GuzzleHttp/Psr7/functions.php';
require_once $root . '/lib/vendor/GuzzleHttp/Promise/functions.php';

$classLoader = new SplClassLoader('GuzzleHttp', $root . '/lib/vendor');
$classLoader->register();

$classLoader = new SplClassLoader('Psr', $root . '/lib/vendor');
$classLoader->register();

class ProjectConfiguration extends sfProjectConfiguration
{
    public function setup()
    {
        $this->enablePlugins(
            'sfDoctrinePlugin',
            'sfDoctrineGuardPlugin',
            'sfSAMLPlugin',
            'sfDoctrineActAsSignablePlugin',
            'sfThumbnailPlugin',
            'sfPhpExcelPlugin'
        );

        sfConfig::set('sf_profile_image_dir', sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR);
        sfConfig::set('sf_upload_dir',        sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads');
        sfConfig::set('sf_sys_temp_dir',      sys_get_temp_dir() . DIRECTORY_SEPARATOR);
        sfConfig::set('sf_upload_dir_company_directories', sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'company_directories');

        $dm = Doctrine_Manager::getInstance();
        $dm->bindComponent('sfGuardUser', 'main_conn');
        $dm->bindComponent('sfGuardGroup', 'main_conn');
        $dm->bindComponent('sfGuardPermission', 'main_conn');
        $dm->bindComponent('sfGuardRememberKey', 'main_conn');
        $dm->bindComponent('sfGuardUserGroup', 'main_conn');
        $dm->bindComponent('sfGuardUserPermission', 'main_conn');
        $dm->bindComponent('sfGuardGroupPermission', 'main_conn');

        // (We no longer include Doctrine.compiled.php on PHP 7.4)

        $this->dispatcher->connect('doctrine.configure', array('ProjectConfiguration', 'configureDoctrine'));
    }

    public static function configureDoctrine(sfEvent $event)
    {
        $manager = $event->getSubject();
        $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
    }
}
