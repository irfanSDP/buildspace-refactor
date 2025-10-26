<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path() . '/commands',
	app_path() . '/controllers',
	app_path() . '/models',
	app_path() . '/database/seeds',

));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

// $logFile = 'log-' . php_sapi_name() . '-' . posix_getpwuid(posix_geteuid())['name'] . '.txt';

// Log::useDailyFiles(storage_path() . '/logs/' . $logFile);

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

// Log invalid page
App::missing(function ($exception)
{
	Log::error('(' . Request::getClientIp() . ') - ' . Request::fullUrl());

	return Response::view('errors.404', array(), 404);
});

App::error(function (Illuminate\Database\Eloquent\ModelNotFoundException $e)
{
	return Response::view('errors.404', array(), 404);
});

App::error(function (Laracasts\Validation\FormValidationException $e)
{
	// will return failed form validation's error using json
	// if current request is an ajax call
	if ( Request::ajax() )
	{
		// 400 being the HTTP code for an invalid request.
		return Response::json(array(
			'success' => false,
			'errors'  => $e->getErrors(),
		), 400);
	}

	Flash::error('Form Validation Error');

	return Redirect::back()->withInput()->withErrors($e->getErrors());
});

App::error(function (Illuminate\Session\TokenMismatchException $e)
{
    Flash::error(trans('forms.sessionExpiredError'));

    return Redirect::back()->withInput();
});

App::error(function (PCK\Exceptions\InvalidAccessLevelException $e)
{
	$route = App::make('Illuminate\Routing\Router');

	Flash::error($e->getMessage());

	if ( $route->input('projectId') )
	{
		return Redirect::route('projects.show', $route->input('projectId')->id);
	}

	return Redirect::route('home.index');
});

App::error(function (PCK\Exceptions\InvalidMessagingTurns $e)
{
	$route = App::make('Illuminate\Routing\Router');

	Flash::error($e->getMessage());

	return Redirect::route('projects.show', $route->input('projectId')->id);
});

App::error(function (PCK\Exceptions\MessagingFlowHasEnded $e)
{
	$route = App::make('Illuminate\Routing\Router');

	Flash::error($e->getMessage());

	return Redirect::route('projects.show', $route->input('projectId')->id);
});

App::error(function (PCK\Exceptions\InvalidRecordException $e)
{
	Flash::error($e->getMessage());

	return Redirect::route('home.index');
});

App::error(function (Exception $exception, $code)
{
	Log::error($exception);
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function ()
{
	return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path() . '/filters.php';

/*
|--------------------------------------------------------------------------
| Require The Custom Validation File
|--------------------------------------------------------------------------
|
*/

require app_path() . '/custom_validations.php';

/*
|--------------------------------------------------------------------------
| Require The Events File
|--------------------------------------------------------------------------
|
*/

require app_path() . '/events.php';

/*
|--------------------------------------------------------------------------
| Custom Form Validator For User
|--------------------------------------------------------------------------
|
*/

App::bind('confide.user_validator', 'PCK\Forms\UserFormValidator');

/*
|--------------------------------------------------------------------------
| Custom Error global
|--------------------------------------------------------------------------
|
*/

$monolog = Log::getMonolog();
$monolog->setHandlers([]); // clear existing handlers (optional)
$monolog->pushHandler(
    new StreamHandler(storage_path().'/logs/laravel-error.log', Logger::ERROR)
);


App::before(function ($request) {
    $sspPath = getenv('SAMLAUTH_PATH') ?: base_path('../samlauth');
    Config::set('laravel-saml::saml.sp_path', $sspPath);
});

