<?php

/**
 * This page shows a username/password login form, and passes information from it
 * to the sspmod_core_Auth_UserPassBase class, which is a generic class for
 * username/password authentication.
 *
 * @author Olav Morken, UNINETT AS.
 * @package SimpleSAMLphp
 */

// Retrieve the authentication state
if (!array_key_exists('AuthState', $_REQUEST)) {
	throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
}
$authStateId = $_REQUEST['AuthState'];
$state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_core_Auth_UserPassBase::STAGEID);

$source = SimpleSAML_Auth_Source::getById($state[sspmod_core_Auth_UserPassBase::AUTHID]);
if ($source === NULL) {
	throw new Exception('Could not find authentication source with id ' . $state[sspmod_core_Auth_UserPassBase::AUTHID]);
}


if (array_key_exists('username', $_REQUEST)) {
	$username = $_REQUEST['username'];
} elseif ($source->getRememberUsernameEnabled() && array_key_exists($source->getAuthId() . '-username', $_COOKIE)) {
	$username = $_COOKIE[$source->getAuthId() . '-username'];
} elseif (isset($state['core:username'])) {
	$username = (string)$state['core:username'];
} else {
	$username = '';
}

if (array_key_exists('password', $_REQUEST)) {
	$password = $_REQUEST['password'];
} else {
	$password = '';
}

$errorCode = NULL;
$errorParams = NULL;

if (!empty($_REQUEST['username']) || !empty($password)) {
	// Either username or password set - attempt to log in

	if (array_key_exists('forcedUsername', $state)) {
		$username = $state['forcedUsername'];
	}

	if ($source->getRememberUsernameEnabled()) {
		$sessionHandler = \SimpleSAML\SessionHandler::getSessionHandler();
		$params = $sessionHandler->getCookieParams();
		$params['expire'] = time();
		$params['expire'] += (isset($_REQUEST['remember_username']) && $_REQUEST['remember_username'] == 'Yes' ? 31536000 : -300);
        \SimpleSAML\Utils\HTTP::setCookie($source->getAuthId() . '-username', $username, $params, FALSE);
	}

    if ($source->isRememberMeEnabled()) {
        if (array_key_exists('remember_me', $_REQUEST) && $_REQUEST['remember_me'] === 'Yes') {
            $state['RememberMe'] = TRUE;
            $authStateId = SimpleSAML_Auth_State::saveState($state, sspmod_core_Auth_UserPassBase::STAGEID);
        }
    }

	try {
		sspmod_core_Auth_UserPassBase::handleLogin($authStateId, $username, $password);
	} catch (SimpleSAML_Error_Error $e) {
		/* Login failed. Extract error code and parameters, to display the error. */
		$errorCode = $e->getErrorCode();
		$errorParams = $e->getParameters();
	}
}

$config = SimpleSAML_Configuration::getConfig();
$globalConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($globalConfig, 'core:loginuserpass.php');
$t->data['stateparams'] = array('AuthState' => $authStateId);
if (array_key_exists('forcedUsername', $state)) {
	$t->data['username'] = $state['forcedUsername'];
	$t->data['forceUsername'] = TRUE;
	$t->data['rememberUsernameEnabled'] = FALSE;
	$t->data['rememberUsernameChecked'] = FALSE;
    $t->data['rememberMeEnabled'] = $source->isRememberMeEnabled();
    $t->data['rememberMeChecked'] = $source->isRememberMeChecked();
} else {
	$t->data['username'] = $username;
	$t->data['forceUsername'] = FALSE;
	$t->data['rememberUsernameEnabled'] = $source->getRememberUsernameEnabled();
	$t->data['rememberUsernameChecked'] = $source->getRememberUsernameChecked();
    $t->data['rememberMeEnabled'] = $source->isRememberMeEnabled();
    $t->data['rememberMeChecked'] = $source->isRememberMeChecked();
	if (isset($_COOKIE[$source->getAuthId() . '-username'])) $t->data['rememberUsernameChecked'] = TRUE;
}
$t->data['links'] = $source->getLoginLinks();
$t->data['errorcode'] = $errorCode;
$t->data['errorcodes'] = SimpleSAML\Error\ErrorCodes::getAllErrorCodeMessages();
$t->data['errorparams'] = $errorParams;

/* Start: Company logo and login image */
$baseDir = realpath(__DIR__ . '/../../../..');

// Function to recursively find the directory which contains the known subpath
function findDirectoryWithSubpath($baseDir, $subpath) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
    foreach ($iterator as $file) {
        if ($file->isDir() && strpos($file->getPathname(), $subpath) !== false) {
            return dirname($file->getPathname());  // Return the directory that contains 'app/config/themes'
        }
    }
    return null; // Return null if no matching directory is found
}

// Function to get project directory name
function getProjectDirName($baseDir, $subpath) {
    $eprojectDir = findDirectoryWithSubpath($baseDir, $subpath);
    if (!empty($eprojectDir)) {
        $appDirectoryPath = dirname(dirname($eprojectDir));
        return basename(dirname($appDirectoryPath));
    }
    return 'eproject';
}

// Function to get file content if exists
function getLoginImgContent($filePath) {
    return file_exists($filePath) ? file_get_contents($filePath) : '';
}

// Function to get image URL
function getLoginImageUrl($settingsDirPath, $filename, $eprojectDirName, $config, $defaultPath) {
    $filePathSettings = $settingsDirPath . '/' . $filename . '-filepath.txt';
    $urlSettings = $settingsDirPath . '/' . $filename . '-url.txt';

    if (file_exists($filePathSettings) && file_exists($urlSettings)) {
        $filePath = getLoginImgContent($filePathSettings);
        $fullPath = __DIR__ . '/../../../../' . $eprojectDirName . '/public' . $filePath;
        if (file_exists($fullPath)) {
            $url = getLoginImgContent($urlSettings);
            return $config->getString('path_eproject') . $url . '?v=' . time();
        }
    }
    return '/' . $defaultPath . '?v=' . time();
}

$eprojectDirName = getProjectDirName($baseDir, 'app/config/themes');
$eprojectDirPath = __DIR__ . '/../../../../' . $eprojectDirName;
$settingsDirPath = $eprojectDirPath . '/app/config/themes';

$t->data['company_logo'] = getLoginImageUrl($settingsDirPath, 'logo1', $eprojectDirName, $config, $t->data['baseurlpath'] . 'resources/buildspacetheme1/images/company-logo.png');
$t->data['login_img'] = getLoginImageUrl($settingsDirPath, 'login_img', $eprojectDirName, $config, $t->data['baseurlpath'] . 'resources/buildspacetheme1/images/login_img.png');

/* End: Company logo and login image */

$t->data['theme_colour1'] = '';     // Theme colour 1 (main)
$t->data['theme_colour2'] = '';     // Theme colour 2 (secondary)

if (isset($state['SPMetadata'])) {
	$t->data['SPMetadata'] = $state['SPMetadata'];
} else {
	$t->data['SPMetadata'] = NULL;
}

$t->show();
exit();

