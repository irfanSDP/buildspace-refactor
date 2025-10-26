<?php
/**
 * An helper file for Laravel 4, to provide autocomplete information to your IDE
 * Generated for Laravel 4.2.19 on 2023-11-14.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 * @see https://github.com/barryvdh/laravel-ide-helper
 */

namespace {
    exit('This file should not be included, only analyzed by your IDE');

    class App extends \Illuminate\Support\Facades\App{

        /**
         * Bind the installation paths to the application.
         *
         * @param array $paths
         * @return void
         */
        public static function bindInstallPaths($paths){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::bindInstallPaths($paths);
        }

        /**
         * Get the application bootstrap file.
         *
         * @return string
         */
        public static function getBootstrapFile(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getBootstrapFile();
        }

        /**
         * Start the exception handling for the request.
         *
         * @return void
         */
        public static function startExceptionHandling(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::startExceptionHandling();
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Get or check the current application environment.
         *
         * @param mixed
         * @return string
         */
        public static function environment(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::environment();
        }

        /**
         * Determine if application is in local environment.
         *
         * @return bool
         */
        public static function isLocal(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::isLocal();
        }

        /**
         * Detect the application's current environment.
         *
         * @param array|string $envs
         * @return string
         */
        public static function detectEnvironment($envs){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::detectEnvironment($envs);
        }

        /**
         * Determine if we are running in the console.
         *
         * @return bool
         */
        public static function runningInConsole(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::runningInConsole();
        }

        /**
         * Determine if we are running unit tests.
         *
         * @return bool
         */
        public static function runningUnitTests(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::runningUnitTests();
        }

        /**
         * Force register a service provider with the application.
         *
         * @param \Illuminate\Support\ServiceProvider|string $provider
         * @param array $options
         * @return \Illuminate\Support\ServiceProvider
         */
        public static function forceRegister($provider, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::forceRegister($provider, $options);
        }

        /**
         * Register a service provider with the application.
         *
         * @param \Illuminate\Support\ServiceProvider|string $provider
         * @param array $options
         * @param bool $force
         * @return \Illuminate\Support\ServiceProvider
         */
        public static function register($provider, $options = array(), $force = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::register($provider, $options, $force);
        }

        /**
         * Get the registered service provider instance if it exists.
         *
         * @param \Illuminate\Support\ServiceProvider|string $provider
         * @return \Illuminate\Support\ServiceProvider|null
         */
        public static function getRegistered($provider){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getRegistered($provider);
        }

        /**
         * Resolve a service provider instance from the class name.
         *
         * @param string $provider
         * @return \Illuminate\Support\ServiceProvider
         */
        public static function resolveProviderClass($provider){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::resolveProviderClass($provider);
        }

        /**
         * Load and boot all of the remaining deferred providers.
         *
         * @return void
         */
        public static function loadDeferredProviders(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::loadDeferredProviders();
        }

        /**
         * Register a deferred provider and service.
         *
         * @param string $provider
         * @param string $service
         * @return void
         */
        public static function registerDeferredProvider($provider, $service = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::registerDeferredProvider($provider, $service);
        }

        /**
         * Resolve the given type from the container.
         *
         * (Overriding Container::make)
         *
         * @param string $abstract
         * @param array $parameters
         * @return mixed
         */
        public static function make($abstract, $parameters = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::make($abstract, $parameters);
        }

        /**
         * Determine if the given abstract type has been bound.
         *
         * (Overriding Container::bound)
         *
         * @param string $abstract
         * @return bool
         */
        public static function bound($abstract){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::bound($abstract);
        }

        /**
         * "Extend" an abstract type in the container.
         *
         * (Overriding Container::extend)
         *
         * @param string $abstract
         * @param \Closure $closure
         * @return void
         * @throws \InvalidArgumentException
         */
        public static function extend($abstract, $closure){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::extend($abstract, $closure);
        }

        /**
         * Register a "before" application filter.
         *
         * @param \Closure|string $callback
         * @return void
         */
        public static function before($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::before($callback);
        }

        /**
         * Register an "after" application filter.
         *
         * @param \Closure|string $callback
         * @return void
         */
        public static function after($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::after($callback);
        }

        /**
         * Register a "finish" application filter.
         *
         * @param \Closure|string $callback
         * @return void
         */
        public static function finish($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::finish($callback);
        }

        /**
         * Register a "shutdown" callback.
         *
         * @param callable $callback
         * @return void
         */
        public static function shutdown($callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::shutdown($callback);
        }

        /**
         * Register a function for determining when to use array sessions.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function useArraySessions($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::useArraySessions($callback);
        }

        /**
         * Determine if the application has booted.
         *
         * @return bool
         */
        public static function isBooted(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::isBooted();
        }

        /**
         * Boot the application's service providers.
         *
         * @return void
         */
        public static function boot(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::boot();
        }

        /**
         * Register a new boot listener.
         *
         * @param mixed $callback
         * @return void
         */
        public static function booting($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::booting($callback);
        }

        /**
         * Register a new "booted" listener.
         *
         * @param mixed $callback
         * @return void
         */
        public static function booted($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::booted($callback);
        }

        /**
         * Run the application and send the response.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return void
         */
        public static function run($request = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::run($request);
        }

        /**
         * Add a HttpKernel middleware onto the stack.
         *
         * @param string $class
         * @param array $parameters
         * @return $this
         */
        public static function middleware($class, $parameters = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::middleware($class, $parameters);
        }

        /**
         * Remove a custom middleware from the application.
         *
         * @param string $class
         * @return void
         */
        public static function forgetMiddleware($class){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::forgetMiddleware($class);
        }

        /**
         * Handle the given request and get the response.
         *
         * Provides compatibility with BrowserKit functional testing.
         *
         * @implements HttpKernelInterface::handle
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @param int $type
         * @param bool $catch
         * @return \Symfony\Component\HttpFoundation\Response
         * @throws \Exception
         */
        public static function handle($request, $type = 1, $catch = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::handle($request, $type, $catch);
        }

        /**
         * Handle the given request and get the response.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public static function dispatch($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::dispatch($request);
        }

        /**
         * Call the "finish" and "shutdown" callbacks assigned to the application.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @param \Symfony\Component\HttpFoundation\Response $response
         * @return void
         */
        public static function terminate($request, $response){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::terminate($request, $response);
        }

        /**
         * Call the "finish" callbacks assigned to the application.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @param \Symfony\Component\HttpFoundation\Response $response
         * @return void
         */
        public static function callFinishCallbacks($request, $response){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::callFinishCallbacks($request, $response);
        }

        /**
         * Prepare the request by injecting any services.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\Request
         */
        public static function prepareRequest($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::prepareRequest($request);
        }

        /**
         * Prepare the given value as a Response object.
         *
         * @param mixed $value
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public static function prepareResponse($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::prepareResponse($value);
        }

        /**
         * Determine if the application is ready for responses.
         *
         * @return bool
         */
        public static function readyForResponses(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::readyForResponses();
        }

        /**
         * Determine if the application is currently down for maintenance.
         *
         * @return bool
         */
        public static function isDownForMaintenance(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::isDownForMaintenance();
        }

        /**
         * Register a maintenance mode event listener.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function down($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::down($callback);
        }

        /**
         * Throw an HttpException with the given data.
         *
         * @param int $code
         * @param string $message
         * @param array $headers
         * @return void
         * @throws \Symfony\Component\HttpKernel\Exception\HttpException
         * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        public static function abort($code, $message = '', $headers = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::abort($code, $message, $headers);
        }

        /**
         * Register a 404 error handler.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function missing($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::missing($callback);
        }

        /**
         * Register an application error handler.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function error($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::error($callback);
        }

        /**
         * Register an error handler at the bottom of the stack.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function pushError($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::pushError($callback);
        }

        /**
         * Register an error handler for fatal errors.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function fatal($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::fatal($callback);
        }

        /**
         * Get the configuration loader instance.
         *
         * @return \Illuminate\Config\LoaderInterface
         */
        public static function getConfigLoader(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getConfigLoader();
        }

        /**
         * Get the environment variables loader instance.
         *
         * @return \Illuminate\Config\EnvironmentVariablesLoaderInterface
         */
        public static function getEnvironmentVariablesLoader(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getEnvironmentVariablesLoader();
        }

        /**
         * Get the service provider repository instance.
         *
         * @return \Illuminate\Foundation\ProviderRepository
         */
        public static function getProviderRepository(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getProviderRepository();
        }

        /**
         * Get the service providers that have been loaded.
         *
         * @return array
         */
        public static function getLoadedProviders(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getLoadedProviders();
        }

        /**
         * Set the application's deferred services.
         *
         * @param array $services
         * @return void
         */
        public static function setDeferredServices($services){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::setDeferredServices($services);
        }

        /**
         * Determine if the given service is a deferred service.
         *
         * @param string $service
         * @return bool
         */
        public static function isDeferredService($service){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::isDeferredService($service);
        }

        /**
         * Get or set the request class for the application.
         *
         * @param string $class
         * @return string
         */
        public static function requestClass($class = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::requestClass($class);
        }

        /**
         * Set the application request for the console environment.
         *
         * @return void
         */
        public static function setRequestForConsoleEnvironment(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::setRequestForConsoleEnvironment();
        }

        /**
         * Call a method on the default request class.
         *
         * @param string $method
         * @param array $parameters
         * @return mixed
         */
        public static function onRequest($method, $parameters = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::onRequest($method, $parameters);
        }

        /**
         * Get the current application locale.
         *
         * @return string
         */
        public static function getLocale(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getLocale();
        }

        /**
         * Set the current application locale.
         *
         * @param string $locale
         * @return void
         */
        public static function setLocale($locale){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::setLocale($locale);
        }

        /**
         * Register the core class aliases in the container.
         *
         * @return void
         */
        public static function registerCoreContainerAliases(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::registerCoreContainerAliases();
        }

        /**
         * Determine if the given abstract type has been resolved.
         *
         * @param string $abstract
         * @return bool
         */
        public static function resolved($abstract){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::resolved($abstract);
        }

        /**
         * Determine if a given string is an alias.
         *
         * @param string $name
         * @return bool
         */
        public static function isAlias($name){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::isAlias($name);
        }

        /**
         * Register a binding with the container.
         *
         * @param string|array $abstract
         * @param \Closure|string|null $concrete
         * @param bool $shared
         * @return void
         */
        public static function bind($abstract, $concrete = null, $shared = false){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::bind($abstract, $concrete, $shared);
        }

        /**
         * Register a binding if it hasn't already been registered.
         *
         * @param string $abstract
         * @param \Closure|string|null $concrete
         * @param bool $shared
         * @return void
         */
        public static function bindIf($abstract, $concrete = null, $shared = false){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::bindIf($abstract, $concrete, $shared);
        }

        /**
         * Register a shared binding in the container.
         *
         * @param string $abstract
         * @param \Closure|string|null $concrete
         * @return void
         */
        public static function singleton($abstract, $concrete = null){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::singleton($abstract, $concrete);
        }

        /**
         * Wrap a Closure such that it is shared.
         *
         * @param \Closure $closure
         * @return \Closure
         */
        public static function share($closure){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::share($closure);
        }

        /**
         * Bind a shared Closure into the container.
         *
         * @param string $abstract
         * @param \Closure $closure
         * @return void
         */
        public static function bindShared($abstract, $closure){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::bindShared($abstract, $closure);
        }

        /**
         * Register an existing instance as shared in the container.
         *
         * @param string $abstract
         * @param mixed $instance
         * @return void
         */
        public static function instance($abstract, $instance){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::instance($abstract, $instance);
        }

        /**
         * Alias a type to a shorter name.
         *
         * @param string $abstract
         * @param string $alias
         * @return void
         */
        public static function alias($abstract, $alias){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::alias($abstract, $alias);
        }

        /**
         * Bind a new callback to an abstract's rebind event.
         *
         * @param string $abstract
         * @param \Closure $callback
         * @return mixed
         */
        public static function rebinding($abstract, $callback){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::rebinding($abstract, $callback);
        }

        /**
         * Refresh an instance on the given target and method.
         *
         * @param string $abstract
         * @param mixed $target
         * @param string $method
         * @return mixed
         */
        public static function refresh($abstract, $target, $method){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::refresh($abstract, $target, $method);
        }

        /**
         * Instantiate a concrete instance of the given type.
         *
         * @param string $concrete
         * @param array $parameters
         * @return mixed
         * @throws \Illuminate\Container\BindingResolutionException
         */
        public static function build($concrete, $parameters = array()){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::build($concrete, $parameters);
        }

        /**
         * Register a new resolving callback.
         *
         * @param string $abstract
         * @param \Closure $callback
         * @return void
         */
        public static function resolving($abstract, $callback){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::resolving($abstract, $callback);
        }

        /**
         * Register a new resolving callback for all types.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function resolvingAny($callback){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::resolvingAny($callback);
        }

        /**
         * Determine if a given type is shared.
         *
         * @param string $abstract
         * @return bool
         */
        public static function isShared($abstract){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::isShared($abstract);
        }

        /**
         * Get the container's bindings.
         *
         * @return array
         */
        public static function getBindings(){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::getBindings();
        }

        /**
         * Remove a resolved instance from the instance cache.
         *
         * @param string $abstract
         * @return void
         */
        public static function forgetInstance($abstract){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::forgetInstance($abstract);
        }

        /**
         * Clear all of the instances from the container.
         *
         * @return void
         */
        public static function forgetInstances(){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::forgetInstances();
        }

        /**
         * Determine if a given offset exists.
         *
         * @param string $key
         * @return bool
         */
        public static function offsetExists($key){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::offsetExists($key);
        }

        /**
         * Get the value at a given offset.
         *
         * @param string $key
         * @return mixed
         */
        public static function offsetGet($key){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Foundation\Application::offsetGet($key);
        }

        /**
         * Set the value at a given offset.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function offsetSet($key, $value){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::offsetSet($key, $value);
        }

        /**
         * Unset the value at a given offset.
         *
         * @param string $key
         * @return void
         */
        public static function offsetUnset($key){
            //Method inherited from \Illuminate\Container\Container
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Foundation\Application::offsetUnset($key);
        }

    }

    class Artisan extends \Illuminate\Support\Facades\Artisan{

        /**
         * Create and boot a new Console application.
         *
         * @param \Illuminate\Foundation\Application $app
         * @return \Illuminate\Console\Application
         */
        public static function start($app){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::start($app);
        }

        /**
         * Create a new Console application.
         *
         * @param \Illuminate\Foundation\Application $app
         * @return \Illuminate\Console\Application
         */
        public static function make($app){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::make($app);
        }

        /**
         * Boot the Console application.
         *
         * @return $this
         */
        public static function boot(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::boot();
        }

        /**
         * Run an Artisan console command by name.
         *
         * @param string $command
         * @param array $parameters
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @return void
         */
        public static function call($command, $parameters = array(), $output = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Console\Application::call($command, $parameters, $output);
        }

        /**
         * Add a command to the console.
         *
         * @param \Symfony\Component\Console\Command\Command $command
         * @return \Symfony\Component\Console\Command\Command
         */
        public static function add($command){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::add($command);
        }

        /**
         * Add a command, resolving through the application.
         *
         * @param string $command
         * @return \Symfony\Component\Console\Command\Command
         */
        public static function resolve($command){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::resolve($command);
        }

        /**
         * Resolve an array of commands through the application.
         *
         * @param array|mixed $commands
         * @return void
         */
        public static function resolveCommands($commands){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Console\Application::resolveCommands($commands);
        }

        /**
         * Render the given exception.
         *
         * @param \Exception $e
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @return void
         */
        public static function renderException($e, $output){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Console\Application::renderException($e, $output);
        }

        /**
         * Set the exception handler instance.
         *
         * @param \Illuminate\Exception\Handler $handler
         * @return $this
         */
        public static function setExceptionHandler($handler){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::setExceptionHandler($handler);
        }

        /**
         * Set the Laravel application instance.
         *
         * @param \Illuminate\Foundation\Application $laravel
         * @return $this
         */
        public static function setLaravel($laravel){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::setLaravel($laravel);
        }

        /**
         * Set whether the Console app should auto-exit when done.
         *
         * @param bool $boolean
         * @return $this
         */
        public static function setAutoExit($boolean){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::setAutoExit($boolean);
        }

        public static function setDispatcher($dispatcher){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::setDispatcher($dispatcher);
        }

        /**
         * Runs the current application.
         *
         * @param \Symfony\Component\Console\Input\InputInterface $input An Input instance
         * @param \Symfony\Component\Console\Output\OutputInterface $output An Output instance
         * @return int 0 if everything went fine, or an error code
         * @throws \Exception When doRun returns Exception
         * @api
         */
        public static function run($input = null, $output = null){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::run($input, $output);
        }

        /**
         * Runs the current application.
         *
         * @param \Symfony\Component\Console\Input\InputInterface $input An Input instance
         * @param \Symfony\Component\Console\Output\OutputInterface $output An Output instance
         * @return int 0 if everything went fine, or an error code
         */
        public static function doRun($input, $output){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::doRun($input, $output);
        }

        /**
         * Set a helper set to be used with the command.
         *
         * @param \Symfony\Component\Console\Helper\HelperSet $helperSet The helper set
         * @api
         */
        public static function setHelperSet($helperSet){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::setHelperSet($helperSet);
        }

        /**
         * Get the helper set associated with the command.
         *
         * @return \Symfony\Component\Console\Helper\HelperSet The HelperSet instance associated with this command
         * @api
         */
        public static function getHelperSet(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getHelperSet();
        }

        /**
         * Set an input definition set to be used with this application.
         *
         * @param \Symfony\Component\Console\Input\InputDefinition $definition The input definition
         * @api
         */
        public static function setDefinition($definition){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::setDefinition($definition);
        }

        /**
         * Gets the InputDefinition related to this Application.
         *
         * @return \Symfony\Component\Console\Input\InputDefinition The InputDefinition instance
         */
        public static function getDefinition(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getDefinition();
        }

        /**
         * Gets the help message.
         *
         * @return string A help message.
         */
        public static function getHelp(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getHelp();
        }

        /**
         * Sets whether to catch exceptions or not during commands execution.
         *
         * @param bool $boolean Whether to catch exceptions or not during commands execution
         * @api
         */
        public static function setCatchExceptions($boolean){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::setCatchExceptions($boolean);
        }

        /**
         * Gets the name of the application.
         *
         * @return string The application name
         * @api
         */
        public static function getName(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getName();
        }

        /**
         * Sets the application name.
         *
         * @param string $name The application name
         * @api
         */
        public static function setName($name){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::setName($name);
        }

        /**
         * Gets the application version.
         *
         * @return string The application version
         * @api
         */
        public static function getVersion(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getVersion();
        }

        /**
         * Sets the application version.
         *
         * @param string $version The application version
         * @api
         */
        public static function setVersion($version){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::setVersion($version);
        }

        /**
         * Returns the long version of the application.
         *
         * @return string The long application version
         * @api
         */
        public static function getLongVersion(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getLongVersion();
        }

        /**
         * Registers a new command.
         *
         * @param string $name The command name
         * @return \Symfony\Component\Console\Command\Command The newly created command
         * @api
         */
        public static function register($name){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::register($name);
        }

        /**
         * Adds an array of command objects.
         *
         * @param \Symfony\Component\Console\Command\Command[] $commands An array of commands
         * @api
         */
        public static function addCommands($commands){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::addCommands($commands);
        }

        /**
         * Returns a registered command by name or alias.
         *
         * @param string $name The command name or alias
         * @return \Symfony\Component\Console\Command\Command A Command object
         * @throws \InvalidArgumentException When command name given does not exist
         * @api
         */
        public static function get($name){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::get($name);
        }

        /**
         * Returns true if the command exists, false otherwise.
         *
         * @param string $name The command name or alias
         * @return bool true if the command exists, false otherwise
         * @api
         */
        public static function has($name){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::has($name);
        }

        /**
         * Returns an array of all unique namespaces used by currently registered commands.
         *
         * It does not returns the global namespace which always exists.
         *
         * @return array An array of namespaces
         */
        public static function getNamespaces(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getNamespaces();
        }

        /**
         * Finds a registered namespace by a name or an abbreviation.
         *
         * @param string $namespace A namespace or abbreviation to search for
         * @return string A registered namespace
         * @throws \InvalidArgumentException When namespace is incorrect or ambiguous
         */
        public static function findNamespace($namespace){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::findNamespace($namespace);
        }

        /**
         * Finds a command by name or alias.
         *
         * Contrary to get, this command tries to find the best
         * match if you give it an abbreviation of a name or alias.
         *
         * @param string $name A command name or a command alias
         * @return \Symfony\Component\Console\Command\Command A Command instance
         * @throws \InvalidArgumentException When command name is incorrect or ambiguous
         * @api
         */
        public static function find($name){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::find($name);
        }

        /**
         * Gets the commands (registered in the given namespace if provided).
         *
         * The array keys are the full names and the values the command instances.
         *
         * @param string $namespace A namespace name
         * @return \Symfony\Component\Console\Command\Command[] An array of Command instances
         * @api
         */
        public static function all($namespace = null){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::all($namespace);
        }

        /**
         * Returns an array of possible abbreviations given a set of names.
         *
         * @param array $names An array of names
         * @return array An array of abbreviations
         */
        public static function getAbbreviations($names){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getAbbreviations($names);
        }

        /**
         * Returns a text representation of the Application.
         *
         * @param string $namespace An optional namespace name
         * @param bool $raw Whether to return raw command list
         * @return string A string representing the Application
         * @deprecated Deprecated since version 2.3, to be removed in 3.0.
         */
        public static function asText($namespace = null, $raw = false){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpDeprecationInspection */
            return \Illuminate\Console\Application::asText($namespace, $raw);
        }

        /**
         * Returns an XML representation of the Application.
         *
         * @param string $namespace An optional namespace name
         * @param bool $asDom Whether to return a DOM or an XML string
         * @return string|\DOMDocument An XML string representing the Application
         * @deprecated Deprecated since version 2.3, to be removed in 3.0.
         */
        public static function asXml($namespace = null, $asDom = false){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpDeprecationInspection */
            return \Illuminate\Console\Application::asXml($namespace, $asDom);
        }

        /**
         * Tries to figure out the terminal dimensions based on the current environment.
         *
         * @return array Array containing width and height
         */
        public static function getTerminalDimensions(){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::getTerminalDimensions();
        }

        /**
         * Sets terminal dimensions.
         *
         * Can be useful to force terminal dimensions for functional tests.
         *
         * @param int $width The width
         * @param int $height The height
         * @return \Symfony\Component\Console\Application The current application
         */
        public static function setTerminalDimensions($width, $height){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::setTerminalDimensions($width, $height);
        }

        /**
         * Returns the namespace part of the command name.
         *
         * This method is not part of public API and should not be used directly.
         *
         * @param string $name The full name of the command
         * @param string $limit The maximum number of parts of the namespace
         * @return string The namespace of the command
         */
        public static function extractNamespace($name, $limit = null){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Console\Application::extractNamespace($name, $limit);
        }

        /**
         * Sets the default Command name.
         *
         * @param string $commandName The Command name
         */
        public static function setDefaultCommand($commandName){
            //Method inherited from \Symfony\Component\Console\Application
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Console\Application::setDefaultCommand($commandName);
        }

    }

    class Auth extends \Illuminate\Support\Facades\Auth{

        /**
         * Create an instance of the database driver.
         *
         * @return \Illuminate\Auth\Guard
         */
        public static function createDatabaseDriver(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\AuthManager::createDatabaseDriver();
        }

        /**
         * Create an instance of the Eloquent driver.
         *
         * @return \Illuminate\Auth\Guard
         */
        public static function createEloquentDriver(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\AuthManager::createEloquentDriver();
        }

        /**
         * Get the default authentication driver name.
         *
         * @return string
         */
        public static function getDefaultDriver(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\AuthManager::getDefaultDriver();
        }

        /**
         * Set the default authentication driver name.
         *
         * @param string $name
         * @return void
         */
        public static function setDefaultDriver($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\AuthManager::setDefaultDriver($name);
        }

        /**
         * Get a driver instance.
         *
         * @param string $driver
         * @return mixed
         */
        public static function driver($driver = null){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\AuthManager::driver($driver);
        }

        /**
         * Register a custom driver creator Closure.
         *
         * @param string $driver
         * @param \Closure $callback
         * @return $this
         */
        public static function extend($driver, $callback){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\AuthManager::extend($driver, $callback);
        }

        /**
         * Get all of the created "drivers".
         *
         * @return array
         */
        public static function getDrivers(){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\AuthManager::getDrivers();
        }

        /**
         * Determine if the current user is authenticated.
         *
         * @return bool
         */
        public static function check(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::check();
        }

        /**
         * Determine if the current user is a guest.
         *
         * @return bool
         */
        public static function guest(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::guest();
        }

        /**
         * Get the currently authenticated user.
         *
         * @return \PCK\Users\User|null
         */
        public static function user(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::user();
        }

        /**
         * Get the ID for the currently authenticated user.
         *
         * @return int|null
         */
        public static function id(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::id();
        }

        /**
         * Log a user into the application without sessions or cookies.
         *
         * @param array $credentials
         * @return bool
         */
        public static function once($credentials = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::once($credentials);
        }

        /**
         * Validate a user's credentials.
         *
         * @param array $credentials
         * @return bool
         */
        public static function validate($credentials = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::validate($credentials);
        }

        /**
         * Attempt to authenticate using HTTP Basic Auth.
         *
         * @param string $field
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return \Symfony\Component\HttpFoundation\Response|null
         */
        public static function basic($field = 'email', $request = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::basic($field, $request);
        }

        /**
         * Perform a stateless HTTP Basic login attempt.
         *
         * @param string $field
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return \Symfony\Component\HttpFoundation\Response|null
         */
        public static function onceBasic($field = 'email', $request = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::onceBasic($field, $request);
        }

        /**
         * Attempt to authenticate a user using the given credentials.
         *
         * @param array $credentials
         * @param bool $remember
         * @param bool $login
         * @return bool
         */
        public static function attempt($credentials = array(), $remember = false, $login = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::attempt($credentials, $remember, $login);
        }

        /**
         * Register an authentication attempt event listener.
         *
         * @param mixed $callback
         * @return void
         */
        public static function attempting($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Guard::attempting($callback);
        }

        /**
         * Log a user into the application.
         *
         * @param \Illuminate\Auth\UserInterface $user
         * @param bool $remember
         * @return void
         */
        public static function login($user, $remember = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Guard::login($user, $remember);
        }

        /**
         * Log the given user ID into the application.
         *
         * @param mixed $id
         * @param bool $remember
         * @return \PCK\Users\User
         */
        public static function loginUsingId($id, $remember = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::loginUsingId($id, $remember);
        }

        /**
         * Log the given user ID into the application without sessions or cookies.
         *
         * @param mixed $id
         * @return bool
         */
        public static function onceUsingId($id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::onceUsingId($id);
        }

        /**
         * Log the user out of the application.
         *
         * @return void
         */
        public static function logout(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Guard::logout();
        }

        /**
         * Get the cookie creator instance used by the guard.
         *
         * @return \Illuminate\Cookie\CookieJar
         * @throws \RuntimeException
         */
        public static function getCookieJar(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getCookieJar();
        }

        /**
         * Set the cookie creator instance used by the guard.
         *
         * @param \Illuminate\Cookie\CookieJar $cookie
         * @return void
         */
        public static function setCookieJar($cookie){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Guard::setCookieJar($cookie);
        }

        /**
         * Get the event dispatcher instance.
         *
         * @return \Illuminate\Events\Dispatcher
         */
        public static function getDispatcher(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getDispatcher();
        }

        /**
         * Set the event dispatcher instance.
         *
         * @param \Illuminate\Events\Dispatcher
         * @return void
         */
        public static function setDispatcher($events){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Guard::setDispatcher($events);
        }

        /**
         * Get the session store used by the guard.
         *
         * @return \Illuminate\Session\Store
         */
        public static function getSession(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getSession();
        }

        /**
         * Get the user provider used by the guard.
         *
         * @return \Illuminate\Auth\Guard
         */
        public static function getProvider(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getProvider();
        }

        /**
         * Set the user provider used by the guard.
         *
         * @param \Illuminate\Auth\UserProviderInterface $provider
         * @return void
         */
        public static function setProvider($provider){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Guard::setProvider($provider);
        }

        /**
         * Return the currently cached user of the application.
         *
         * @return \PCK\Users\User|null
         */
        public static function getUser(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getUser();
        }

        /**
         * Set the current user of the application.
         *
         * @param \Illuminate\Auth\UserInterface $user
         * @return void
         */
        public static function setUser($user){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Guard::setUser($user);
        }

        /**
         * Get the current request instance.
         *
         * @return \Symfony\Component\HttpFoundation\Request
         */
        public static function getRequest(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getRequest();
        }

        /**
         * Set the current request instance.
         *
         * @param \Symfony\Component\HttpFoundation\Request
         * @return $this
         */
        public static function setRequest($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::setRequest($request);
        }

        /**
         * Get the last user we attempted to authenticate.
         *
         * @return \PCK\Users\User
         */
        public static function getLastAttempted(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getLastAttempted();
        }

        /**
         * Get a unique identifier for the auth session value.
         *
         * @return string
         */
        public static function getName(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getName();
        }

        /**
         * Get the name of the cookie used to store the "recaller".
         *
         * @return string
         */
        public static function getRecallerName(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::getRecallerName();
        }

        /**
         * Determine if the user was authenticated via "remember me" cookie.
         *
         * @return bool
         */
        public static function viaRemember(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Guard::viaRemember();
        }

    }

    class Blade extends \Illuminate\Support\Facades\Blade{

        /**
         * Compile the view at the given path.
         *
         * @param string $path
         * @return void
         */
        public static function compile($path = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Compilers\BladeCompiler::compile($path);
        }

        /**
         * Get the path currently being compiled.
         *
         * @return string
         */
        public static function getPath(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::getPath();
        }

        /**
         * Set the path currently being compiled.
         *
         * @param string $path
         * @return void
         */
        public static function setPath($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Compilers\BladeCompiler::setPath($path);
        }

        /**
         * Compile the given Blade template contents.
         *
         * @param string $value
         * @return string
         */
        public static function compileString($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::compileString($value);
        }

        /**
         * Compile the default values for the echo statement.
         *
         * @param string $value
         * @return string
         */
        public static function compileEchoDefaults($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::compileEchoDefaults($value);
        }

        /**
         * Register a custom Blade compiler.
         *
         * @param \Closure $compiler
         * @return void
         */
        public static function extend($compiler){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Compilers\BladeCompiler::extend($compiler);
        }

        /**
         * Get the regular expression for a generic Blade function.
         *
         * @param string $function
         * @return string
         */
        public static function createMatcher($function){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::createMatcher($function);
        }

        /**
         * Get the regular expression for a generic Blade function.
         *
         * @param string $function
         * @return string
         */
        public static function createOpenMatcher($function){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::createOpenMatcher($function);
        }

        /**
         * Create a plain Blade matcher.
         *
         * @param string $function
         * @return string
         */
        public static function createPlainMatcher($function){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::createPlainMatcher($function);
        }

        /**
         * Sets the content tags used for the compiler.
         *
         * @param string $openTag
         * @param string $closeTag
         * @param bool $escaped
         * @return void
         */
        public static function setContentTags($openTag, $closeTag, $escaped = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Compilers\BladeCompiler::setContentTags($openTag, $closeTag, $escaped);
        }

        /**
         * Sets the escaped content tags used for the compiler.
         *
         * @param string $openTag
         * @param string $closeTag
         * @return void
         */
        public static function setEscapedContentTags($openTag, $closeTag){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Compilers\BladeCompiler::setEscapedContentTags($openTag, $closeTag);
        }

        /**
         * Gets the content tags used for the compiler.
         *
         * @return string
         */
        public static function getContentTags(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::getContentTags();
        }

        /**
         * Gets the escaped content tags used for the compiler.
         *
         * @return string
         */
        public static function getEscapedContentTags(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::getEscapedContentTags();
        }

        /**
         * Get the path to the compiled version of a view.
         *
         * @param string $path
         * @return string
         */
        public static function getCompiledPath($path){
            //Method inherited from \Illuminate\View\Compilers\Compiler
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::getCompiledPath($path);
        }

        /**
         * Determine if the view at the given path is expired.
         *
         * @param string $path
         * @return bool
         */
        public static function isExpired($path){
            //Method inherited from \Illuminate\View\Compilers\Compiler
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Compilers\BladeCompiler::isExpired($path);
        }

    }

    class Cache extends \Illuminate\Support\Facades\Cache{

        /**
         * Get the cache "prefix" value.
         *
         * @return string
         */
        public static function getPrefix(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\CacheManager::getPrefix();
        }

        /**
         * Set the cache "prefix" value.
         *
         * @param string $name
         * @return void
         */
        public static function setPrefix($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\CacheManager::setPrefix($name);
        }

        /**
         * Get the default cache driver name.
         *
         * @return string
         */
        public static function getDefaultDriver(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\CacheManager::getDefaultDriver();
        }

        /**
         * Set the default cache driver name.
         *
         * @param string $name
         * @return void
         */
        public static function setDefaultDriver($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\CacheManager::setDefaultDriver($name);
        }

        /**
         * Get a driver instance.
         *
         * @param string $driver
         * @return mixed
         */
        public static function driver($driver = null){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\CacheManager::driver($driver);
        }

        /**
         * Register a custom driver creator Closure.
         *
         * @param string $driver
         * @param \Closure $callback
         * @return $this
         */
        public static function extend($driver, $callback){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\CacheManager::extend($driver, $callback);
        }

        /**
         * Get all of the created "drivers".
         *
         * @return array
         */
        public static function getDrivers(){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\CacheManager::getDrivers();
        }

        /**
         * Determine if an item exists in the cache.
         *
         * @param string $key
         * @return bool
         */
        public static function has($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::has($key);
        }

        /**
         * Retrieve an item from the cache by key.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function get($key, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::get($key, $default);
        }

        /**
         * Retrieve an item from the cache and delete it.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function pull($key, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::pull($key, $default);
        }

        /**
         * Store an item in the cache.
         *
         * @param string $key
         * @param mixed $value
         * @param \DateTime|int $minutes
         * @return void
         */
        public static function put($key, $value, $minutes){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\Repository::put($key, $value, $minutes);
        }

        /**
         * Store an item in the cache if the key does not exist.
         *
         * @param string $key
         * @param mixed $value
         * @param \DateTime|int $minutes
         * @return bool
         */
        public static function add($key, $value, $minutes){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::add($key, $value, $minutes);
        }

        /**
         * Get an item from the cache, or store the default value.
         *
         * @param string $key
         * @param \DateTime|int $minutes
         * @param \Closure $callback
         * @return mixed
         */
        public static function remember($key, $minutes, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::remember($key, $minutes, $callback);
        }

        /**
         * Get an item from the cache, or store the default value forever.
         *
         * @param string $key
         * @param \Closure $callback
         * @return mixed
         */
        public static function sear($key, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::sear($key, $callback);
        }

        /**
         * Get an item from the cache, or store the default value forever.
         *
         * @param string $key
         * @param \Closure $callback
         * @return mixed
         */
        public static function rememberForever($key, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::rememberForever($key, $callback);
        }

        /**
         * Get the default cache time.
         *
         * @return int
         */
        public static function getDefaultCacheTime(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::getDefaultCacheTime();
        }

        /**
         * Set the default cache time in minutes.
         *
         * @param int $minutes
         * @return void
         */
        public static function setDefaultCacheTime($minutes){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\Repository::setDefaultCacheTime($minutes);
        }

        /**
         * Get the cache store implementation.
         *
         * @return \Illuminate\Cache\RedisStore
         */
        public static function getStore(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::getStore();
        }

        /**
         * Determine if a cached value exists.
         *
         * @param string $key
         * @return bool
         */
        public static function offsetExists($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::offsetExists($key);
        }

        /**
         * Retrieve an item from the cache by key.
         *
         * @param string $key
         * @return mixed
         */
        public static function offsetGet($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::offsetGet($key);
        }

        /**
         * Store an item in the cache for the default time.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function offsetSet($key, $value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\Repository::offsetSet($key, $value);
        }

        /**
         * Remove an item from the cache.
         *
         * @param string $key
         * @return void
         */
        public static function offsetUnset($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\Repository::offsetUnset($key);
        }

        /**
         * Register a custom macro.
         *
         * @param string $name
         * @param callable $macro
         * @return void
         */
        public static function macro($name, $macro){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\Repository::macro($name, $macro);
        }

        /**
         * Checks if macro is registered
         *
         * @param string $name
         * @return boolean
         */
        public static function hasMacro($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::hasMacro($name);
        }

        /**
         * Dynamically handle calls to the class.
         *
         * @param string $method
         * @param array $parameters
         * @return mixed
         * @throws \BadMethodCallException
         */
        public static function macroCall($method, $parameters){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\Repository::macroCall($method, $parameters);
        }

        /**
         * Increment the value of an item in the cache.
         *
         * @param string $key
         * @param mixed $value
         * @return int
         */
        public static function increment($key, $value = 1){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\RedisStore::increment($key, $value);
        }

        /**
         * Increment the value of an item in the cache.
         *
         * @param string $key
         * @param mixed $value
         * @return int
         */
        public static function decrement($key, $value = 1){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\RedisStore::decrement($key, $value);
        }

        /**
         * Store an item in the cache indefinitely.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function forever($key, $value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\RedisStore::forever($key, $value);
        }

        /**
         * Remove an item from the cache.
         *
         * @param string $key
         * @return void
         */
        public static function forget($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\RedisStore::forget($key);
        }

        /**
         * Remove all items from the cache.
         *
         * @return void
         */
        public static function flush(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\RedisStore::flush();
        }

        /**
         * Begin executing a new tags operation.
         *
         * @param array|mixed $names
         * @return \Illuminate\Cache\RedisTaggedCache
         */
        public static function tags($names){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\RedisStore::tags($names);
        }

        /**
         * Get the Redis connection instance.
         *
         * @return \Predis\ClientInterface
         */
        public static function connection(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\RedisStore::connection();
        }

        /**
         * Set the connection name to be used.
         *
         * @param string $connection
         * @return void
         */
        public static function setConnection($connection){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cache\RedisStore::setConnection($connection);
        }

        /**
         * Get the Redis database instance.
         *
         * @return \Illuminate\Redis\Database
         */
        public static function getRedis(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\RedisStore::getRedis();
        }

        /**
         * Begin executing a new tags operation.
         *
         * @param string $name
         * @return \Illuminate\Cache\TaggedCache
         */
        public static function section($name){
            //Method inherited from \Illuminate\Cache\TaggableStore
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cache\RedisStore::section($name);
        }

    }

    class ClassLoader extends \Illuminate\Support\ClassLoader{

    }

    class Config extends \Illuminate\Support\Facades\Config{

        /**
         * Determine if the given configuration value exists.
         *
         * @param string $key
         * @return bool
         */
        public static function has($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::has($key);
        }

        /**
         * Determine if a configuration group exists.
         *
         * @param string $key
         * @return bool
         */
        public static function hasGroup($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::hasGroup($key);
        }

        /**
         * Get the specified configuration value.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function get($key, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::get($key, $default);
        }

        /**
         * Set a given configuration value.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function set($key, $value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::set($key, $value);
        }

        /**
         * Register a package for cascading configuration.
         *
         * @param string $package
         * @param string $hint
         * @param string $namespace
         * @return void
         */
        public static function package($package, $hint, $namespace = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::package($package, $hint, $namespace);
        }

        /**
         * Register an after load callback for a given namespace.
         *
         * @param string $namespace
         * @param \Closure $callback
         * @return void
         */
        public static function afterLoading($namespace, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::afterLoading($namespace, $callback);
        }

        /**
         * Add a new namespace to the loader.
         *
         * @param string $namespace
         * @param string $hint
         * @return void
         */
        public static function addNamespace($namespace, $hint){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::addNamespace($namespace, $hint);
        }

        /**
         * Returns all registered namespaces with the config
         * loader.
         *
         * @return array
         */
        public static function getNamespaces(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::getNamespaces();
        }

        /**
         * Get the loader implementation.
         *
         * @return \Illuminate\Config\LoaderInterface
         */
        public static function getLoader(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::getLoader();
        }

        /**
         * Set the loader implementation.
         *
         * @param \Illuminate\Config\LoaderInterface $loader
         * @return void
         */
        public static function setLoader($loader){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::setLoader($loader);
        }

        /**
         * Get the current configuration environment.
         *
         * @return string
         */
        public static function getEnvironment(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::getEnvironment();
        }

        /**
         * Get the after load callback array.
         *
         * @return array
         */
        public static function getAfterLoadCallbacks(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::getAfterLoadCallbacks();
        }

        /**
         * Get all of the configuration items.
         *
         * @return array
         */
        public static function getItems(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::getItems();
        }

        /**
         * Determine if the given configuration option exists.
         *
         * @param string $key
         * @return bool
         */
        public static function offsetExists($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::offsetExists($key);
        }

        /**
         * Get a configuration option.
         *
         * @param string $key
         * @return mixed
         */
        public static function offsetGet($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::offsetGet($key);
        }

        /**
         * Set a configuration option.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function offsetSet($key, $value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::offsetSet($key, $value);
        }

        /**
         * Unset a configuration option.
         *
         * @param string $key
         * @return void
         */
        public static function offsetUnset($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::offsetUnset($key);
        }

        /**
         * Parse a key into namespace, group, and item.
         *
         * @param string $key
         * @return array
         */
        public static function parseKey($key){
            //Method inherited from \Illuminate\Support\NamespacedItemResolver
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Config\Repository::parseKey($key);
        }

        /**
         * Set the parsed value of a key.
         *
         * @param string $key
         * @param array $parsed
         * @return void
         */
        public static function setParsedKey($key, $parsed){
            //Method inherited from \Illuminate\Support\NamespacedItemResolver
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Config\Repository::setParsedKey($key, $parsed);
        }

    }

    class Controller extends \Illuminate\Routing\Controller{

    }

    class Cookie extends \Illuminate\Support\Facades\Cookie{

        /**
         * Create a new cookie instance.
         *
         * @param string $name
         * @param string $value
         * @param int $minutes
         * @param string $path
         * @param string $domain
         * @param bool $secure
         * @param bool $httpOnly
         * @return \Symfony\Component\HttpFoundation\Cookie
         */
        public static function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cookie\CookieJar::make($name, $value, $minutes, $path, $domain, $secure, $httpOnly);
        }

        /**
         * Create a cookie that lasts "forever" (five years).
         *
         * @param string $name
         * @param string $value
         * @param string $path
         * @param string $domain
         * @param bool $secure
         * @param bool $httpOnly
         * @return \Symfony\Component\HttpFoundation\Cookie
         */
        public static function forever($name, $value, $path = null, $domain = null, $secure = false, $httpOnly = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cookie\CookieJar::forever($name, $value, $path, $domain, $secure, $httpOnly);
        }

        /**
         * Expire the given cookie.
         *
         * @param string $name
         * @param string $path
         * @param string $domain
         * @return \Symfony\Component\HttpFoundation\Cookie
         */
        public static function forget($name, $path = null, $domain = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cookie\CookieJar::forget($name, $path, $domain);
        }

        /**
         * Determine if a cookie has been queued.
         *
         * @param string $key
         * @return bool
         */
        public static function hasQueued($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cookie\CookieJar::hasQueued($key);
        }

        /**
         * Get a queued cookie instance.
         *
         * @param string $key
         * @param mixed $default
         * @return \Symfony\Component\HttpFoundation\Cookie
         */
        public static function queued($key, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cookie\CookieJar::queued($key, $default);
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Queue a cookie to send with the next response.
         *
         * @param mixed
         * @return void
         */
        public static function queue(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Cookie\CookieJar::queue();
        }

        /**
         * Remove a cookie from the queue.
         *
         * @param string $name
         */
        public static function unqueue($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Cookie\CookieJar::unqueue($name);
        }

        /**
         * Set the default path and domain for the jar.
         *
         * @param string $path
         * @param string $domain
         * @return $this
         */
        public static function setDefaultPathAndDomain($path, $domain){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cookie\CookieJar::setDefaultPathAndDomain($path, $domain);
        }

        /**
         * Get the cookies which have been queued for the next request
         *
         * @return array
         */
        public static function getQueuedCookies(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Cookie\CookieJar::getQueuedCookies();
        }

    }

    class Crypt extends \Illuminate\Support\Facades\Crypt{

        /**
         * Determine if the given key and cipher combination is valid.
         *
         * @param string $key
         * @param string $cipher
         * @return bool
         */
        public static function supported($key, $cipher){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Tomgrohl\Laravel\Encryption\Encrypter::supported($key, $cipher);
        }

        /**
         * @param  $value
         * @param bool $serialize
         * @return string
         */
        public static function encrypt($value, $serialize = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Tomgrohl\Laravel\Encryption\Encrypter::encrypt($value, $serialize);
        }

        /**
         * Encrypt a string without serialization.
         *
         * @param string $value
         * @return string
         */
        public static function encryptString($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Tomgrohl\Laravel\Encryption\Encrypter::encryptString($value);
        }

        /**
         * @param  $payload
         * @param bool $unserialize
         * @return mixed|string
         */
        public static function decrypt($payload, $unserialize = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Tomgrohl\Laravel\Encryption\Encrypter::decrypt($payload, $unserialize);
        }

        /**
         * Decrypt the given string without unserialization.
         *
         * @param string $payload
         * @return string
         */
        public static function decryptString($payload){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Tomgrohl\Laravel\Encryption\Encrypter::decryptString($payload);
        }

        /**
         * Get the encryption key.
         *
         * @return string
         */
        public static function getKey(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Tomgrohl\Laravel\Encryption\Encrypter::getKey();
        }

        /**
         * Set the encryption key.
         *
         * @param string $key
         * @return void
         */
        public static function setKey($key){
            //Method inherited from \Illuminate\Encryption\Encrypter
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Tomgrohl\Laravel\Encryption\Encrypter::setKey($key);
        }

        /**
         * Set the encryption cipher.
         *
         * @param string $cipher
         * @return void
         */
        public static function setCipher($cipher){
            //Method inherited from \Illuminate\Encryption\Encrypter
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Tomgrohl\Laravel\Encryption\Encrypter::setCipher($cipher);
        }

        /**
         * Set the encryption mode.
         *
         * @param string $mode
         * @return void
         */
        public static function setMode($mode){
            //Method inherited from \Illuminate\Encryption\Encrypter
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Tomgrohl\Laravel\Encryption\Encrypter::setMode($mode);
        }

    }

    class DB extends \Illuminate\Support\Facades\DB{

        /**
         * Get a database connection instance.
         *
         * @param string $name
         * @return \Illuminate\Database\Connection
         */
        public static function connection($name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\DatabaseManager::connection($name);
        }

        /**
         * Disconnect from the given database and remove from local cache.
         *
         * @param string $name
         * @return void
         */
        public static function purge($name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\DatabaseManager::purge($name);
        }

        /**
         * Disconnect from the given database.
         *
         * @param string $name
         * @return void
         */
        public static function disconnect($name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\DatabaseManager::disconnect($name);
        }

        /**
         * Reconnect to the given database.
         *
         * @param string $name
         * @return \Illuminate\Database\Connection
         */
        public static function reconnect($name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\DatabaseManager::reconnect($name);
        }

        /**
         * Get the default connection name.
         *
         * @return string
         */
        public static function getDefaultConnection(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\DatabaseManager::getDefaultConnection();
        }

        /**
         * Set the default connection name.
         *
         * @param string $name
         * @return void
         */
        public static function setDefaultConnection($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\DatabaseManager::setDefaultConnection($name);
        }

        /**
         * Register an extension connection resolver.
         *
         * @param string $name
         * @param callable $resolver
         * @return void
         */
        public static function extend($name, $resolver){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\DatabaseManager::extend($name, $resolver);
        }

        /**
         * Return all of the created connections.
         *
         * @return array
         */
        public static function getConnections(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\DatabaseManager::getConnections();
        }

        /**
         * Set the query grammar to the default implementation.
         *
         * @return void
         */
        public static function useDefaultQueryGrammar(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::useDefaultQueryGrammar();
        }

        /**
         * Set the schema grammar to the default implementation.
         *
         * @return void
         */
        public static function useDefaultSchemaGrammar(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::useDefaultSchemaGrammar();
        }

        /**
         * Set the query post processor to the default implementation.
         *
         * @return void
         */
        public static function useDefaultPostProcessor(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::useDefaultPostProcessor();
        }

        /**
         * Get a schema builder instance for the connection.
         *
         * @return \Illuminate\Database\Schema\Builder
         */
        public static function getSchemaBuilder(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getSchemaBuilder();
        }

        /**
         * Begin a fluent query against a database table.
         *
         * @param string $table
         * @return \Illuminate\Database\Query\Builder
         */
        public static function table($table){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::table($table);
        }

        /**
         * Get a new raw query expression.
         *
         * @param mixed $value
         * @return \Illuminate\Database\Query\Expression
         */
        public static function raw($value){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::raw($value);
        }

        /**
         * Run a select statement and return a single result.
         *
         * @param string $query
         * @param array $bindings
         * @return mixed
         */
        public static function selectOne($query, $bindings = array()){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::selectOne($query, $bindings);
        }

        /**
         * Run a select statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return array
         */
        public static function selectFromWriteConnection($query, $bindings = array()){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::selectFromWriteConnection($query, $bindings);
        }

        /**
         * Run a select statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @param bool $useReadPdo
         * @return array
         */
        public static function select($query, $bindings = array(), $useReadPdo = true){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::select($query, $bindings, $useReadPdo);
        }

        /**
         * Run an insert statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return bool
         */
        public static function insert($query, $bindings = array()){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::insert($query, $bindings);
        }

        /**
         * Run an update statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         */
        public static function update($query, $bindings = array()){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::update($query, $bindings);
        }

        /**
         * Run a delete statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         */
        public static function delete($query, $bindings = array()){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::delete($query, $bindings);
        }

        /**
         * Execute an SQL statement and return the boolean result.
         *
         * @param string $query
         * @param array $bindings
         * @return bool
         */
        public static function statement($query, $bindings = array()){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::statement($query, $bindings);
        }

        /**
         * Run an SQL statement and get the number of rows affected.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         */
        public static function affectingStatement($query, $bindings = array()){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::affectingStatement($query, $bindings);
        }

        /**
         * Run a raw, unprepared query against the PDO connection.
         *
         * @param string $query
         * @return bool
         */
        public static function unprepared($query){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::unprepared($query);
        }

        /**
         * Prepare the query bindings for execution.
         *
         * @param array $bindings
         * @return array
         */
        public static function prepareBindings($bindings){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::prepareBindings($bindings);
        }

        /**
         * Execute a Closure within a transaction.
         *
         * @param \Closure $callback
         * @return mixed
         * @throws \Exception
         */
        public static function transaction($callback){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::transaction($callback);
        }

        /**
         * Start a new database transaction.
         *
         * @return void
         */
        public static function beginTransaction(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::beginTransaction();
        }

        /**
         * Commit the active database transaction.
         *
         * @return void
         */
        public static function commit(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::commit();
        }

        /**
         * Rollback the active database transaction.
         *
         * @return void
         */
        public static function rollBack(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::rollBack();
        }

        /**
         * Get the number of active transactions.
         *
         * @return int
         */
        public static function transactionLevel(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::transactionLevel();
        }

        /**
         * Execute the given callback in "dry run" mode.
         *
         * @param \Closure $callback
         * @return array
         */
        public static function pretend($callback){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::pretend($callback);
        }

        /**
         * Log a query in the connection's query log.
         *
         * @param string $query
         * @param array $bindings
         * @param float|null $time
         * @return void
         */
        public static function logQuery($query, $bindings, $time = null){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::logQuery($query, $bindings, $time);
        }

        /**
         * Register a database query listener with the connection.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function listen($callback){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::listen($callback);
        }

        /**
         * Get a Doctrine Schema Column instance.
         *
         * @param string $table
         * @param string $column
         * @return \Doctrine\DBAL\Schema\Column
         */
        public static function getDoctrineColumn($table, $column){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getDoctrineColumn($table, $column);
        }

        /**
         * Get the Doctrine DBAL schema manager for the connection.
         *
         * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
         */
        public static function getDoctrineSchemaManager(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getDoctrineSchemaManager();
        }

        /**
         * Get the Doctrine DBAL database connection instance.
         *
         * @return \Doctrine\DBAL\Connection
         */
        public static function getDoctrineConnection(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getDoctrineConnection();
        }

        /**
         * Get the current PDO connection.
         *
         * @return \PDO
         */
        public static function getPdo(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getPdo();
        }

        /**
         * Get the current PDO connection used for reading.
         *
         * @return \PDO
         */
        public static function getReadPdo(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getReadPdo();
        }

        /**
         * Set the PDO connection.
         *
         * @param \PDO|null $pdo
         * @return $this
         */
        public static function setPdo($pdo){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::setPdo($pdo);
        }

        /**
         * Set the PDO connection used for reading.
         *
         * @param \PDO|null $pdo
         * @return $this
         */
        public static function setReadPdo($pdo){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::setReadPdo($pdo);
        }

        /**
         * Set the reconnect instance on the connection.
         *
         * @param callable $reconnector
         * @return $this
         */
        public static function setReconnector($reconnector){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::setReconnector($reconnector);
        }

        /**
         * Get the database connection name.
         *
         * @return string|null
         */
        public static function getName(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getName();
        }

        /**
         * Get an option from the configuration options.
         *
         * @param string $option
         * @return mixed
         */
        public static function getConfig($option){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getConfig($option);
        }

        /**
         * Get the PDO driver name.
         *
         * @return string
         */
        public static function getDriverName(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getDriverName();
        }

        /**
         * Get the query grammar used by the connection.
         *
         * @return \Illuminate\Database\Query\Grammars\Grammar
         */
        public static function getQueryGrammar(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getQueryGrammar();
        }

        /**
         * Set the query grammar used by the connection.
         *
         * @param \Illuminate\Database\Query\Grammars\Grammar
         * @return void
         */
        public static function setQueryGrammar($grammar){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::setQueryGrammar($grammar);
        }

        /**
         * Get the schema grammar used by the connection.
         *
         * @return \Illuminate\Database\Query\Grammars\Grammar
         */
        public static function getSchemaGrammar(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getSchemaGrammar();
        }

        /**
         * Set the schema grammar used by the connection.
         *
         * @param \Illuminate\Database\Schema\Grammars\Grammar
         * @return void
         */
        public static function setSchemaGrammar($grammar){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::setSchemaGrammar($grammar);
        }

        /**
         * Get the query post processor used by the connection.
         *
         * @return \Illuminate\Database\Query\Processors\Processor
         */
        public static function getPostProcessor(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getPostProcessor();
        }

        /**
         * Set the query post processor used by the connection.
         *
         * @param \Illuminate\Database\Query\Processors\Processor
         * @return void
         */
        public static function setPostProcessor($processor){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::setPostProcessor($processor);
        }

        /**
         * Get the event dispatcher used by the connection.
         *
         * @return \Illuminate\Events\Dispatcher
         */
        public static function getEventDispatcher(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getEventDispatcher();
        }

        /**
         * Set the event dispatcher instance on the connection.
         *
         * @param \Illuminate\Events\Dispatcher
         * @return void
         */
        public static function setEventDispatcher($events){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::setEventDispatcher($events);
        }

        /**
         * Get the paginator environment instance.
         *
         * @return \Illuminate\Pagination\Factory
         */
        public static function getPaginator(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getPaginator();
        }

        /**
         * Set the pagination environment instance.
         *
         * @param \Illuminate\Pagination\Factory|\Closure $paginator
         * @return void
         */
        public static function setPaginator($paginator){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::setPaginator($paginator);
        }

        /**
         * Get the cache manager instance.
         *
         * @return \Illuminate\Cache\CacheManager
         */
        public static function getCacheManager(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getCacheManager();
        }

        /**
         * Set the cache manager instance on the connection.
         *
         * @param \Illuminate\Cache\CacheManager|\Closure $cache
         * @return void
         */
        public static function setCacheManager($cache){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::setCacheManager($cache);
        }

        /**
         * Determine if the connection in a "dry run".
         *
         * @return bool
         */
        public static function pretending(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::pretending();
        }

        /**
         * Get the default fetch mode for the connection.
         *
         * @return int
         */
        public static function getFetchMode(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getFetchMode();
        }

        /**
         * Set the default fetch mode for the connection.
         *
         * @param int $fetchMode
         * @return int
         */
        public static function setFetchMode($fetchMode){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::setFetchMode($fetchMode);
        }

        /**
         * Get the connection query log.
         *
         * @return array
         */
        public static function getQueryLog(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getQueryLog();
        }

        /**
         * Clear the query log.
         *
         * @return void
         */
        public static function flushQueryLog(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::flushQueryLog();
        }

        /**
         * Enable the query log on the connection.
         *
         * @return void
         */
        public static function enableQueryLog(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::enableQueryLog();
        }

        /**
         * Disable the query log on the connection.
         *
         * @return void
         */
        public static function disableQueryLog(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::disableQueryLog();
        }

        /**
         * Determine whether we're logging queries.
         *
         * @return bool
         */
        public static function logging(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::logging();
        }

        /**
         * Get the name of the connected database.
         *
         * @return string
         */
        public static function getDatabaseName(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getDatabaseName();
        }

        /**
         * Set the name of the connected database.
         *
         * @param string $database
         * @return string
         */
        public static function setDatabaseName($database){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::setDatabaseName($database);
        }

        /**
         * Get the table prefix for the connection.
         *
         * @return string
         */
        public static function getTablePrefix(){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::getTablePrefix();
        }

        /**
         * Set the table prefix in use by the connection.
         *
         * @param string $prefix
         * @return void
         */
        public static function setTablePrefix($prefix){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\PostgresConnection::setTablePrefix($prefix);
        }

        /**
         * Set the table prefix and return the grammar.
         *
         * @param \Illuminate\Database\Grammar $grammar
         * @return \Illuminate\Database\Grammar
         */
        public static function withTablePrefix($grammar){
            //Method inherited from \Illuminate\Database\Connection
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\PostgresConnection::withTablePrefix($grammar);
        }

    }

    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    class Eloquent extends \Illuminate\Database\Eloquent\Model{

        /**
         * Find a model by its primary key.
         *
         * @param array $id
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static
         */
        public static function findMany($id, $columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::findMany($id, $columns);
        }

        /**
         * Execute the query and get the first result.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|static|null
         */
        public static function first($columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::first($columns);
        }

        /**
         * Execute the query and get the first result or throw an exception.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|static
         * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
         */
        public static function firstOrFail($columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::firstOrFail($columns);
        }

        /**
         * Execute the query as a "select" statement.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Collection|static[]
         */
        public static function get($columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::get($columns);
        }

        /**
         * Pluck a single column from the database.
         *
         * @param string $column
         * @return mixed
         */
        public static function pluck($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::pluck($column);
        }

        /**
         * Chunk the results of the query.
         *
         * @param int $count
         * @param callable $callback
         * @return void
         */
        public static function chunk($count, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Eloquent\Builder::chunk($count, $callback);
        }

        /**
         * Get an array with the values of a given column.
         *
         * @param string $column
         * @param string $key
         * @return array
         */
        public static function lists($column, $key = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::lists($column, $key);
        }

        /**
         * Get a paginator for the "select" statement.
         *
         * @param int $perPage
         * @param array $columns
         * @return \Illuminate\Pagination\Paginator
         */
        public static function paginate($perPage = null, $columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::paginate($perPage, $columns);
        }

        /**
         * Get a paginator only supporting simple next and previous links.
         *
         * This is more efficient on larger data-sets, etc.
         *
         * @param int $perPage
         * @param array $columns
         * @return \Illuminate\Pagination\Paginator
         */
        public static function simplePaginate($perPage = null, $columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::simplePaginate($perPage, $columns);
        }

        /**
         * Register a replacement for the default delete function.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function onDelete($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Eloquent\Builder::onDelete($callback);
        }

        /**
         * Get the hydrated models without eager loading.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model[]
         */
        public static function getModels($columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::getModels($columns);
        }

        /**
         * Eager load the relationships for the models.
         *
         * @param array $models
         * @return array
         */
        public static function eagerLoadRelations($models){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::eagerLoadRelations($models);
        }

        /**
         * Add a basic where clause to the query.
         *
         * @param string $column
         * @param string $operator
         * @param mixed $value
         * @param string $boolean
         * @return $this
         */
        public static function where($column, $operator = null, $value = null, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::where($column, $operator, $value, $boolean);
        }

        /**
         * Add an "or where" clause to the query.
         *
         * @param string $column
         * @param string $operator
         * @param mixed $value
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public static function orWhere($column, $operator = null, $value = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::orWhere($column, $operator, $value);
        }

        /**
         * Add a relationship count condition to the query.
         *
         * @param string $relation
         * @param string $operator
         * @param int $count
         * @param string $boolean
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public static function has($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::has($relation, $operator, $count, $boolean, $callback);
        }

        /**
         * Add a relationship count condition to the query.
         *
         * @param string $relation
         * @param string $boolean
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public static function doesntHave($relation, $boolean = 'and', $callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::doesntHave($relation, $boolean, $callback);
        }

        /**
         * Add a relationship count condition to the query with where clauses.
         *
         * @param string $relation
         * @param \Closure $callback
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public static function whereHas($relation, $callback, $operator = '>=', $count = 1){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::whereHas($relation, $callback, $operator, $count);
        }

        /**
         * Add a relationship count condition to the query with where clauses.
         *
         * @param string $relation
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public static function whereDoesntHave($relation, $callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::whereDoesntHave($relation, $callback);
        }

        /**
         * Add a relationship count condition to the query with an "or".
         *
         * @param string $relation
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public static function orHas($relation, $operator = '>=', $count = 1){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::orHas($relation, $operator, $count);
        }

        /**
         * Add a relationship count condition to the query with where clauses and an "or".
         *
         * @param string $relation
         * @param \Closure $callback
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public static function orWhereHas($relation, $callback, $operator = '>=', $count = 1){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::orWhereHas($relation, $callback, $operator, $count);
        }

        /**
         * Get the underlying query builder instance.
         *
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function getQuery(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::getQuery();
        }

        /**
         * Set the underlying query builder instance.
         *
         * @param \Illuminate\Database\Query\Builder $query
         * @return void
         */
        public static function setQuery($query){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Eloquent\Builder::setQuery($query);
        }

        /**
         * Get the relationships being eagerly loaded.
         *
         * @return array
         */
        public static function getEagerLoads(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::getEagerLoads();
        }

        /**
         * Set the relationships being eagerly loaded.
         *
         * @param array $eagerLoad
         * @return void
         */
        public static function setEagerLoads($eagerLoad){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Eloquent\Builder::setEagerLoads($eagerLoad);
        }

        /**
         * Get the model instance being queried.
         *
         * @return \Illuminate\Database\Eloquent\Model
         */
        public static function getModel(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::getModel();
        }

        /**
         * Set a model instance for the model being queried.
         *
         * @param \Illuminate\Database\Eloquent\Model $model
         * @return $this
         */
        public static function setModel($model){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::setModel($model);
        }

        /**
         * Extend the builder with a given callback.
         *
         * @param string $name
         * @param \Closure $callback
         * @return void
         */
        public static function macro($name, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Eloquent\Builder::macro($name, $callback);
        }

        /**
         * Get the given macro by name.
         *
         * @param string $name
         * @return \Closure
         */
        public static function getMacro($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Eloquent\Builder::getMacro($name);
        }

        /**
         * Set the columns to be selected.
         *
         * @param array $columns
         * @return $this
         */
        public static function select($columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::select($columns);
        }

        /**
         * Add a new "raw" select expression to the query.
         *
         * @param string $expression
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function selectRaw($expression){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::selectRaw($expression);
        }

        /**
         * Add a new select column to the query.
         *
         * @param mixed $column
         * @return $this
         */
        public static function addSelect($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::addSelect($column);
        }

        /**
         * Force the query to only return distinct results.
         *
         * @return $this
         */
        public static function distinct(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::distinct();
        }

        /**
         * Set the table which the query is targeting.
         *
         * @param string $table
         * @return $this
         */
        public static function from($table){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::from($table);
        }

        /**
         * Add a join clause to the query.
         *
         * @param string $table
         * @param string $one
         * @param string $operator
         * @param string $two
         * @param string $type
         * @param bool $where
         * @return $this
         */
        public static function join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::join($table, $one, $operator, $two, $type, $where);
        }

        /**
         * Add a "join where" clause to the query.
         *
         * @param string $table
         * @param string $one
         * @param string $operator
         * @param string $two
         * @param string $type
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function joinWhere($table, $one, $operator, $two, $type = 'inner'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::joinWhere($table, $one, $operator, $two, $type);
        }

        /**
         * Add a left join to the query.
         *
         * @param string $table
         * @param string $first
         * @param string $operator
         * @param string $second
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function leftJoin($table, $first, $operator = null, $second = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::leftJoin($table, $first, $operator, $second);
        }

        /**
         * Add a "join where" clause to the query.
         *
         * @param string $table
         * @param string $one
         * @param string $operator
         * @param string $two
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function leftJoinWhere($table, $one, $operator, $two){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::leftJoinWhere($table, $one, $operator, $two);
        }

        /**
         * Add a right join to the query.
         *
         * @param string $table
         * @param string $first
         * @param string $operator
         * @param string $second
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function rightJoin($table, $first, $operator = null, $second = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::rightJoin($table, $first, $operator, $second);
        }

        /**
         * Add a "right join where" clause to the query.
         *
         * @param string $table
         * @param string $one
         * @param string $operator
         * @param string $two
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function rightJoinWhere($table, $one, $operator, $two){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::rightJoinWhere($table, $one, $operator, $two);
        }

        /**
         * Add a raw where clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @param string $boolean
         * @return $this
         */
        public static function whereRaw($sql, $bindings = array(), $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereRaw($sql, $bindings, $boolean);
        }

        /**
         * Add a raw or where clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereRaw($sql, $bindings = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereRaw($sql, $bindings);
        }

        /**
         * Add a where between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @param string $boolean
         * @param bool $not
         * @return $this
         */
        public static function whereBetween($column, $values, $boolean = 'and', $not = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereBetween($column, $values, $boolean, $not);
        }

        /**
         * Add an or where between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereBetween($column, $values){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereBetween($column, $values);
        }

        /**
         * Add a where not between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereNotBetween($column, $values, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereNotBetween($column, $values, $boolean);
        }

        /**
         * Add an or where not between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereNotBetween($column, $values){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereNotBetween($column, $values);
        }

        /**
         * Add a nested where statement to the query.
         *
         * @param \Closure $callback
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereNested($callback, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereNested($callback, $boolean);
        }

        /**
         * Add another query builder as a nested where to the query builder.
         *
         * @param \Illuminate\Database\Query\Builder|static $query
         * @param string $boolean
         * @return $this
         */
        public static function addNestedWhereQuery($query, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::addNestedWhereQuery($query, $boolean);
        }

        /**
         * Add an exists clause to the query.
         *
         * @param \Closure $callback
         * @param string $boolean
         * @param bool $not
         * @return $this
         */
        public static function whereExists($callback, $boolean = 'and', $not = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereExists($callback, $boolean, $not);
        }

        /**
         * Add an or exists clause to the query.
         *
         * @param \Closure $callback
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereExists($callback, $not = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereExists($callback, $not);
        }

        /**
         * Add a where not exists clause to the query.
         *
         * @param \Closure $callback
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereNotExists($callback, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereNotExists($callback, $boolean);
        }

        /**
         * Add a where not exists clause to the query.
         *
         * @param \Closure $callback
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereNotExists($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereNotExists($callback);
        }

        /**
         * Add a "where in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @param string $boolean
         * @param bool $not
         * @return $this
         */
        public static function whereIn($column, $values, $boolean = 'and', $not = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereIn($column, $values, $boolean, $not);
        }

        /**
         * Add an "or where in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereIn($column, $values){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereIn($column, $values);
        }

        /**
         * Add a "where not in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereNotIn($column, $values, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereNotIn($column, $values, $boolean);
        }

        /**
         * Add an "or where not in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereNotIn($column, $values){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereNotIn($column, $values);
        }

        /**
         * Add a "where null" clause to the query.
         *
         * @param string $column
         * @param string $boolean
         * @param bool $not
         * @return $this
         */
        public static function whereNull($column, $boolean = 'and', $not = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereNull($column, $boolean, $not);
        }

        /**
         * Add an "or where null" clause to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereNull($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereNull($column);
        }

        /**
         * Add a "where not null" clause to the query.
         *
         * @param string $column
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereNotNull($column, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereNotNull($column, $boolean);
        }

        /**
         * Add an "or where not null" clause to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orWhereNotNull($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orWhereNotNull($column);
        }

        /**
         * Add a "where date" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param int $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereDate($column, $operator, $value, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereDate($column, $operator, $value, $boolean);
        }

        /**
         * Add a "where day" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param int $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereDay($column, $operator, $value, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereDay($column, $operator, $value, $boolean);
        }

        /**
         * Add a "where month" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param int $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereMonth($column, $operator, $value, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereMonth($column, $operator, $value, $boolean);
        }

        /**
         * Add a "where year" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param int $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function whereYear($column, $operator, $value, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::whereYear($column, $operator, $value, $boolean);
        }

        /**
         * Handles dynamic "where" clauses to the query.
         *
         * @param string $method
         * @param string $parameters
         * @return $this
         */
        public static function dynamicWhere($method, $parameters){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::dynamicWhere($method, $parameters);
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Add a "group by" clause to the query.
         *
         * @param array|string $column,...
         * @return $this
         */
        public static function groupBy(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::groupBy();
        }

        /**
         * Add a "having" clause to the query.
         *
         * @param string $column
         * @param string $operator
         * @param string $value
         * @param string $boolean
         * @return $this
         */
        public static function having($column, $operator = null, $value = null, $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::having($column, $operator, $value, $boolean);
        }

        /**
         * Add a "or having" clause to the query.
         *
         * @param string $column
         * @param string $operator
         * @param string $value
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orHaving($column, $operator = null, $value = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orHaving($column, $operator, $value);
        }

        /**
         * Add a raw having clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @param string $boolean
         * @return $this
         */
        public static function havingRaw($sql, $bindings = array(), $boolean = 'and'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::havingRaw($sql, $bindings, $boolean);
        }

        /**
         * Add a raw or having clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function orHavingRaw($sql, $bindings = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orHavingRaw($sql, $bindings);
        }

        /**
         * Add an "order by" clause to the query.
         *
         * @param string $column
         * @param string $direction
         * @return $this
         */
        public static function orderBy($column, $direction = 'asc'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orderBy($column, $direction);
        }

        /**
         * Add an "order by" clause for a timestamp to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function latest($column = 'created_at'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::latest($column);
        }

        /**
         * Add an "order by" clause for a timestamp to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function oldest($column = 'created_at'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::oldest($column);
        }

        /**
         * Add a raw "order by" clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @return $this
         */
        public static function orderByRaw($sql, $bindings = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::orderByRaw($sql, $bindings);
        }

        /**
         * Set the "offset" value of the query.
         *
         * @param int $value
         * @return $this
         */
        public static function offset($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::offset($value);
        }

        /**
         * Alias to set the "offset" value of the query.
         *
         * @param int $value
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function skip($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::skip($value);
        }

        /**
         * Set the "limit" value of the query.
         *
         * @param int $value
         * @return $this
         */
        public static function limit($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::limit($value);
        }

        /**
         * Alias to set the "limit" value of the query.
         *
         * @param int $value
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function take($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::take($value);
        }

        /**
         * Set the limit and offset for a given page.
         *
         * @param int $page
         * @param int $perPage
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function forPage($page, $perPage = 15){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::forPage($page, $perPage);
        }

        /**
         * Add a union statement to the query.
         *
         * @param \Illuminate\Database\Query\Builder|\Closure $query
         * @param bool $all
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function union($query, $all = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::union($query, $all);
        }

        /**
         * Add a union all statement to the query.
         *
         * @param \Illuminate\Database\Query\Builder|\Closure $query
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function unionAll($query){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::unionAll($query);
        }

        /**
         * Lock the selected rows in the table.
         *
         * @param bool $value
         * @return $this
         */
        public static function lock($value = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::lock($value);
        }

        /**
         * Lock the selected rows in the table for updating.
         *
         * @return \Illuminate\Database\Query\Builder
         */
        public static function lockForUpdate(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::lockForUpdate();
        }

        /**
         * Share lock the selected rows in the table.
         *
         * @return \Illuminate\Database\Query\Builder
         */
        public static function sharedLock(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::sharedLock();
        }

        /**
         * Get the SQL representation of the query.
         *
         * @return string
         */
        public static function toSql(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::toSql();
        }

        /**
         * Indicate that the query results should be cached.
         *
         * @param \DateTime|int $minutes
         * @param string $key
         * @return $this
         */
        public static function remember($minutes, $key = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::remember($minutes, $key);
        }

        /**
         * Indicate that the query results should be cached forever.
         *
         * @param string $key
         * @return \Illuminate\Database\Query\Builder|static
         */
        public static function rememberForever($key = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::rememberForever($key);
        }

        /**
         * Indicate that the results, if cached, should use the given cache tags.
         *
         * @param array|mixed $cacheTags
         * @return $this
         */
        public static function cacheTags($cacheTags){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::cacheTags($cacheTags);
        }

        /**
         * Indicate that the results, if cached, should use the given cache driver.
         *
         * @param string $cacheDriver
         * @return $this
         */
        public static function cacheDriver($cacheDriver){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::cacheDriver($cacheDriver);
        }

        /**
         * Execute the query as a fresh "select" statement.
         *
         * @param array $columns
         * @return array|static[]
         */
        public static function getFresh($columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getFresh($columns);
        }

        /**
         * Execute the query as a cached "select" statement.
         *
         * @param array $columns
         * @return array
         */
        public static function getCached($columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getCached($columns);
        }

        /**
         * Get a unique cache key for the complete query.
         *
         * @return string
         */
        public static function getCacheKey(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getCacheKey();
        }

        /**
         * Generate the unique cache key for the query.
         *
         * @return string
         */
        public static function generateCacheKey(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::generateCacheKey();
        }

        /**
         * Concatenate values of a given column as a string.
         *
         * @param string $column
         * @param string $glue
         * @return string
         */
        public static function implode($column, $glue = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::implode($column, $glue);
        }

        /**
         * Build a paginator instance from a raw result array.
         *
         * @param \Illuminate\Pagination\Factory $paginator
         * @param array $results
         * @param int $perPage
         * @return \Illuminate\Pagination\Paginator
         */
        public static function buildRawPaginator($paginator, $results, $perPage){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::buildRawPaginator($paginator, $results, $perPage);
        }

        /**
         * Get the count of the total records for pagination.
         *
         * @return int
         */
        public static function getPaginationCount(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getPaginationCount();
        }

        /**
         * Determine if any rows exist for the current query.
         *
         * @return bool
         */
        public static function exists(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::exists();
        }

        /**
         * Retrieve the "count" result of the query.
         *
         * @param string $columns
         * @return int
         */
        public static function count($columns = '*'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::count($columns);
        }

        /**
         * Retrieve the minimum value of a given column.
         *
         * @param string $column
         * @return mixed
         */
        public static function min($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::min($column);
        }

        /**
         * Retrieve the maximum value of a given column.
         *
         * @param string $column
         * @return mixed
         */
        public static function max($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::max($column);
        }

        /**
         * Retrieve the sum of the values of a given column.
         *
         * @param string $column
         * @return mixed
         */
        public static function sum($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::sum($column);
        }

        /**
         * Retrieve the average of the values of a given column.
         *
         * @param string $column
         * @return mixed
         */
        public static function avg($column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::avg($column);
        }

        /**
         * Execute an aggregate function on the database.
         *
         * @param string $function
         * @param array $columns
         * @return mixed
         */
        public static function aggregate($function, $columns = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::aggregate($function, $columns);
        }

        /**
         * Insert a new record into the database.
         *
         * @param array $values
         * @return bool
         */
        public static function insert($values){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::insert($values);
        }

        /**
         * Insert a new record and get the value of the primary key.
         *
         * @param array $values
         * @param string $sequence
         * @return int
         */
        public static function insertGetId($values, $sequence = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::insertGetId($values, $sequence);
        }

        /**
         * Run a truncate statement on the table.
         *
         * @return void
         */
        public static function truncate(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Query\Builder::truncate();
        }

        /**
         * Merge an array of where clauses and bindings.
         *
         * @param array $wheres
         * @param array $bindings
         * @return void
         */
        public static function mergeWheres($wheres, $bindings){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Query\Builder::mergeWheres($wheres, $bindings);
        }

        /**
         * Create a raw database expression.
         *
         * @param mixed $value
         * @return \Illuminate\Database\Query\Expression
         */
        public static function raw($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::raw($value);
        }

        /**
         * Get the current query value bindings in a flattened array.
         *
         * @return array
         */
        public static function getBindings(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getBindings();
        }

        /**
         * Get the raw array of bindings.
         *
         * @return array
         */
        public static function getRawBindings(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getRawBindings();
        }

        /**
         * Set the bindings on the query builder.
         *
         * @param array $bindings
         * @param string $type
         * @return $this
         * @throws \InvalidArgumentException
         */
        public static function setBindings($bindings, $type = 'where'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::setBindings($bindings, $type);
        }

        /**
         * Add a binding to the query.
         *
         * @param mixed $value
         * @param string $type
         * @return $this
         * @throws \InvalidArgumentException
         */
        public static function addBinding($value, $type = 'where'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::addBinding($value, $type);
        }

        /**
         * Merge an array of bindings into our bindings.
         *
         * @param \Illuminate\Database\Query\Builder $query
         * @return $this
         */
        public static function mergeBindings($query){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::mergeBindings($query);
        }

        /**
         * Get the database query processor instance.
         *
         * @return \Illuminate\Database\Query\Processors\Processor
         */
        public static function getProcessor(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getProcessor();
        }

        /**
         * Get the query grammar instance.
         *
         * @return \Illuminate\Database\Grammar
         */
        public static function getGrammar(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::getGrammar();
        }

        /**
         * Use the write pdo for query.
         *
         * @return $this
         */
        public static function useWritePdo(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Query\Builder::useWritePdo();
        }

    }

    class Event extends \Illuminate\Support\Facades\Event{

        /**
         * Register an event listener with the dispatcher.
         *
         * @param string|array $events
         * @param mixed $listener
         * @param int $priority
         * @return void
         */
        public static function listen($events, $listener, $priority = 0){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Events\Dispatcher::listen($events, $listener, $priority);
        }

        /**
         * Determine if a given event has listeners.
         *
         * @param string $eventName
         * @return bool
         */
        public static function hasListeners($eventName){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Events\Dispatcher::hasListeners($eventName);
        }

        /**
         * Register a queued event and payload.
         *
         * @param string $event
         * @param array $payload
         * @return void
         */
        public static function queue($event, $payload = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Events\Dispatcher::queue($event, $payload);
        }

        /**
         * Register an event subscriber with the dispatcher.
         *
         * @param string $subscriber
         * @return void
         */
        public static function subscribe($subscriber){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Events\Dispatcher::subscribe($subscriber);
        }

        /**
         * Fire an event until the first non-null response is returned.
         *
         * @param string $event
         * @param array $payload
         * @return mixed
         */
        public static function until($event, $payload = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Events\Dispatcher::until($event, $payload);
        }

        /**
         * Flush a set of queued events.
         *
         * @param string $event
         * @return void
         */
        public static function flush($event){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Events\Dispatcher::flush($event);
        }

        /**
         * Get the event that is currently firing.
         *
         * @return string
         */
        public static function firing(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Events\Dispatcher::firing();
        }

        /**
         * Fire an event and call the listeners.
         *
         * @param string $event
         * @param mixed $payload
         * @param bool $halt
         * @return array|null
         */
        public static function fire($event, $payload = array(), $halt = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Events\Dispatcher::fire($event, $payload, $halt);
        }

        /**
         * Get all of the listeners for a given event name.
         *
         * @param string $eventName
         * @return array
         */
        public static function getListeners($eventName){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Events\Dispatcher::getListeners($eventName);
        }

        /**
         * Register an event listener with the dispatcher.
         *
         * @param mixed $listener
         * @return mixed
         */
        public static function makeListener($listener){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Events\Dispatcher::makeListener($listener);
        }

        /**
         * Create a class based listener using the IoC container.
         *
         * @param mixed $listener
         * @return \Closure
         */
        public static function createClassListener($listener){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Events\Dispatcher::createClassListener($listener);
        }

        /**
         * Remove a set of listeners from the dispatcher.
         *
         * @param string $event
         * @return void
         */
        public static function forget($event){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Events\Dispatcher::forget($event);
        }

        /**
         * Forget all of the queued listeners.
         *
         * @return void
         */
        public static function forgetQueued(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Events\Dispatcher::forgetQueued();
        }

    }

    class File extends \Illuminate\Support\Facades\File{

        /**
         * Determine if a file exists.
         *
         * @param string $path
         * @return bool
         */
        public static function exists($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::exists($path);
        }

        /**
         * Get the contents of a file.
         *
         * @param string $path
         * @return string
         * @throws \Illuminate\Filesystem\FileNotFoundException
         */
        public static function get($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::get($path);
        }

        /**
         * Get the returned value of a file.
         *
         * @param string $path
         * @return mixed
         * @throws \Illuminate\Filesystem\FileNotFoundException
         */
        public static function getRequire($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::getRequire($path);
        }

        /**
         * Require the given file once.
         *
         * @param string $file
         * @return mixed
         */
        public static function requireOnce($file){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::requireOnce($file);
        }

        /**
         * Write the contents of a file.
         *
         * @param string $path
         * @param string $contents
         * @param bool $lock
         * @return int
         */
        public static function put($path, $contents, $lock = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::put($path, $contents, $lock);
        }

        /**
         * Prepend to a file.
         *
         * @param string $path
         * @param string $data
         * @return int
         */
        public static function prepend($path, $data){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::prepend($path, $data);
        }

        /**
         * Append to a file.
         *
         * @param string $path
         * @param string $data
         * @return int
         */
        public static function append($path, $data){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::append($path, $data);
        }

        /**
         * Delete the file at a given path.
         *
         * @param string|array $paths
         * @return bool
         */
        public static function delete($paths){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::delete($paths);
        }

        /**
         * Move a file to a new location.
         *
         * @param string $path
         * @param string $target
         * @return bool
         */
        public static function move($path, $target){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::move($path, $target);
        }

        /**
         * Copy a file to a new location.
         *
         * @param string $path
         * @param string $target
         * @return bool
         */
        public static function copy($path, $target){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::copy($path, $target);
        }

        /**
         * Extract the file name from a file path.
         *
         * @param string $path
         * @return string
         */
        public static function name($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::name($path);
        }

        /**
         * Extract the file extension from a file path.
         *
         * @param string $path
         * @return string
         */
        public static function extension($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::extension($path);
        }

        /**
         * Get the file type of a given file.
         *
         * @param string $path
         * @return string
         */
        public static function type($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::type($path);
        }

        /**
         * Get the file size of a given file.
         *
         * @param string $path
         * @return int
         */
        public static function size($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::size($path);
        }

        /**
         * Get the file's last modification time.
         *
         * @param string $path
         * @return int
         */
        public static function lastModified($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::lastModified($path);
        }

        /**
         * Determine if the given path is a directory.
         *
         * @param string $directory
         * @return bool
         */
        public static function isDirectory($directory){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::isDirectory($directory);
        }

        /**
         * Determine if the given path is writable.
         *
         * @param string $path
         * @return bool
         */
        public static function isWritable($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::isWritable($path);
        }

        /**
         * Determine if the given path is a file.
         *
         * @param string $file
         * @return bool
         */
        public static function isFile($file){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::isFile($file);
        }

        /**
         * Find path names matching a given pattern.
         *
         * @param string $pattern
         * @param int $flags
         * @return array
         */
        public static function glob($pattern, $flags = 0){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::glob($pattern, $flags);
        }

        /**
         * Get an array of all files in a directory.
         *
         * @param string $directory
         * @return array
         */
        public static function files($directory){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::files($directory);
        }

        /**
         * Get all of the files from the given directory (recursive).
         *
         * @param string $directory
         * @return array
         */
        public static function allFiles($directory){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::allFiles($directory);
        }

        /**
         * Get all of the directories within a given directory.
         *
         * @param string $directory
         * @return array
         */
        public static function directories($directory){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::directories($directory);
        }

        /**
         * Create a directory.
         *
         * @param string $path
         * @param int $mode
         * @param bool $recursive
         * @param bool $force
         * @return bool
         */
        public static function makeDirectory($path, $mode = 493, $recursive = false, $force = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::makeDirectory($path, $mode, $recursive, $force);
        }

        /**
         * Copy a directory from one location to another.
         *
         * @param string $directory
         * @param string $destination
         * @param int $options
         * @return bool
         */
        public static function copyDirectory($directory, $destination, $options = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::copyDirectory($directory, $destination, $options);
        }

        /**
         * Recursively delete a directory.
         *
         * The directory itself may be optionally preserved.
         *
         * @param string $directory
         * @param bool $preserve
         * @return bool
         */
        public static function deleteDirectory($directory, $preserve = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::deleteDirectory($directory, $preserve);
        }

        /**
         * Empty the specified directory of all files and folders.
         *
         * @param string $directory
         * @return bool
         */
        public static function cleanDirectory($directory){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Filesystem\Filesystem::cleanDirectory($directory);
        }

    }

    class Form extends \Illuminate\Support\Facades\Form{

        /**
         * Open up a new HTML form.
         *
         * @param array $options
         * @return string
         */
        public static function open($options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::open($options);
        }

        /**
         * Create a new model based form builder.
         *
         * @param mixed $model
         * @param array $options
         * @return string
         */
        public static function model($model, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::model($model, $options);
        }

        /**
         * Set the model instance on the form builder.
         *
         * @param mixed $model
         * @return void
         */
        public static function setModel($model){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Html\FormBuilder::setModel($model);
        }

        /**
         * Close the current form.
         *
         * @return string
         */
        public static function close(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::close();
        }

        /**
         * Generate a hidden field with the current CSRF token.
         *
         * @return string
         */
        public static function token(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::token();
        }

        /**
         * Create a form label element.
         *
         * @param string $name
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function label($name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::label($name, $value, $options);
        }

        /**
         * Create a form input field.
         *
         * @param string $type
         * @param string $name
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function input($type, $name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::input($type, $name, $value, $options);
        }

        /**
         * Create a text input field.
         *
         * @param string $name
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function text($name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::text($name, $value, $options);
        }

        /**
         * Create a password input field.
         *
         * @param string $name
         * @param array $options
         * @return string
         */
        public static function password($name, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::password($name, $options);
        }

        /**
         * Create a hidden input field.
         *
         * @param string $name
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function hidden($name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::hidden($name, $value, $options);
        }

        /**
         * Create an e-mail input field.
         *
         * @param string $name
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function email($name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::email($name, $value, $options);
        }

        /**
         * Create a url input field.
         *
         * @param string $name
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function url($name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::url($name, $value, $options);
        }

        /**
         * Create a file input field.
         *
         * @param string $name
         * @param array $options
         * @return string
         */
        public static function file($name, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::file($name, $options);
        }

        /**
         * Create a textarea input field.
         *
         * @param string $name
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function textarea($name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::textarea($name, $value, $options);
        }

        /**
         * Create a number input field.
         *
         * @param string $name
         * @param string|null $value
         * @param array $options
         * @return string
         */
        public static function number($name, $value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::number($name, $value, $options);
        }

        /**
         * Create a select box field.
         *
         * @param string $name
         * @param array $list
         * @param string $selected
         * @param array $options
         * @return string
         */
        public static function select($name, $list = array(), $selected = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::select($name, $list, $selected, $options);
        }

        /**
         * Create a select range field.
         *
         * @param string $name
         * @param string $begin
         * @param string $end
         * @param string $selected
         * @param array $options
         * @return string
         */
        public static function selectRange($name, $begin, $end, $selected = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::selectRange($name, $begin, $end, $selected, $options);
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Create a select year field.
         *
         * @param string $name
         * @param string $begin
         * @param string $end
         * @param string $selected
         * @param array $options
         * @return string
         */
        public static function selectYear(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::selectYear();
        }

        /**
         * Create a select month field.
         *
         * @param string $name
         * @param string $selected
         * @param array $options
         * @param string $format
         * @return string
         */
        public static function selectMonth($name, $selected = null, $options = array(), $format = '%B'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::selectMonth($name, $selected, $options, $format);
        }

        /**
         * Get the select option for the given value.
         *
         * @param string $display
         * @param string $value
         * @param string $selected
         * @return string
         */
        public static function getSelectOption($display, $value, $selected){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::getSelectOption($display, $value, $selected);
        }

        /**
         * Create a checkbox input field.
         *
         * @param string $name
         * @param mixed $value
         * @param bool $checked
         * @param array $options
         * @return string
         */
        public static function checkbox($name, $value = 1, $checked = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::checkbox($name, $value, $checked, $options);
        }

        /**
         * Create a radio button input field.
         *
         * @param string $name
         * @param mixed $value
         * @param bool $checked
         * @param array $options
         * @return string
         */
        public static function radio($name, $value = null, $checked = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::radio($name, $value, $checked, $options);
        }

        /**
         * Create a HTML reset input element.
         *
         * @param string $value
         * @param array $attributes
         * @return string
         */
        public static function reset($value, $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::reset($value, $attributes);
        }

        /**
         * Create a HTML image input element.
         *
         * @param string $url
         * @param string $name
         * @param array $attributes
         * @return string
         */
        public static function image($url, $name = null, $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::image($url, $name, $attributes);
        }

        /**
         * Create a submit button element.
         *
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function submit($value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::submit($value, $options);
        }

        /**
         * Create a button element.
         *
         * @param string $value
         * @param array $options
         * @return string
         */
        public static function button($value = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::button($value, $options);
        }

        /**
         * Get the ID attribute for a field name.
         *
         * @param string $name
         * @param array $attributes
         * @return string
         */
        public static function getIdAttribute($name, $attributes){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::getIdAttribute($name, $attributes);
        }

        /**
         * Get the value that should be assigned to the field.
         *
         * @param string $name
         * @param string $value
         * @return string
         */
        public static function getValueAttribute($name, $value = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::getValueAttribute($name, $value);
        }

        /**
         * Get a value from the session's old input.
         *
         * @param string $name
         * @return string
         */
        public static function old($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::old($name);
        }

        /**
         * Determine if the old input is empty.
         *
         * @return bool
         */
        public static function oldInputIsEmpty(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::oldInputIsEmpty();
        }

        /**
         * Get the session store implementation.
         *
         * @return \Illuminate\Session\Store $session
         */
        public static function getSessionStore(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::getSessionStore();
        }

        /**
         * Set the session store implementation.
         *
         * @param \Illuminate\Session\Store $session
         * @return $this
         */
        public static function setSessionStore($session){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::setSessionStore($session);
        }

        /**
         * Register a custom macro.
         *
         * @param string $name
         * @param callable $macro
         * @return void
         */
        public static function macro($name, $macro){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Html\FormBuilder::macro($name, $macro);
        }

        /**
         * Checks if macro is registered
         *
         * @param string $name
         * @return boolean
         */
        public static function hasMacro($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\FormBuilder::hasMacro($name);
        }

    }

    class Hash extends \Illuminate\Support\Facades\Hash{

        /**
         * Hash the given value.
         *
         * @param string $value
         * @param array $options
         * @return string
         * @throws \RuntimeException
         */
        public static function make($value, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Hashing\BcryptHasher::make($value, $options);
        }

        /**
         * Check the given plain value against a hash.
         *
         * @param string $value
         * @param string $hashedValue
         * @param array $options
         * @return bool
         */
        public static function check($value, $hashedValue, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Hashing\BcryptHasher::check($value, $hashedValue, $options);
        }

        /**
         * Check if the given hash has been hashed using the given options.
         *
         * @param string $hashedValue
         * @param array $options
         * @return bool
         */
        public static function needsRehash($hashedValue, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Hashing\BcryptHasher::needsRehash($hashedValue, $options);
        }

        /**
         * Set the default crypt cost factor.
         *
         * @param int $rounds
         * @return void
         */
        public static function setRounds($rounds){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Hashing\BcryptHasher::setRounds($rounds);
        }

    }

    class HTML extends \Illuminate\Support\Facades\HTML{

        /**
         * Convert an HTML string to entities.
         *
         * @param string $value
         * @return string
         */
        public static function entities($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::entities($value);
        }

        /**
         * Convert entities to HTML characters.
         *
         * @param string $value
         * @return string
         */
        public static function decode($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::decode($value);
        }

        /**
         * Generate a link to a JavaScript file.
         *
         * @param string $url
         * @param array $attributes
         * @param bool $secure
         * @return string
         */
        public static function script($url, $attributes = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::script($url, $attributes, $secure);
        }

        /**
         * Generate a link to a CSS file.
         *
         * @param string $url
         * @param array $attributes
         * @param bool $secure
         * @return string
         */
        public static function style($url, $attributes = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::style($url, $attributes, $secure);
        }

        /**
         * Generate an HTML image element.
         *
         * @param string $url
         * @param string $alt
         * @param array $attributes
         * @param bool $secure
         * @return string
         */
        public static function image($url, $alt = null, $attributes = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::image($url, $alt, $attributes, $secure);
        }

        /**
         * Generate a HTML link.
         *
         * @param string $url
         * @param string $title
         * @param array $attributes
         * @param bool $secure
         * @return string
         */
        public static function link($url, $title = null, $attributes = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::link($url, $title, $attributes, $secure);
        }

        /**
         * Generate a HTTPS HTML link.
         *
         * @param string $url
         * @param string $title
         * @param array $attributes
         * @return string
         */
        public static function secureLink($url, $title = null, $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::secureLink($url, $title, $attributes);
        }

        /**
         * Generate a HTML link to an asset.
         *
         * @param string $url
         * @param string $title
         * @param array $attributes
         * @param bool $secure
         * @return string
         */
        public static function linkAsset($url, $title = null, $attributes = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::linkAsset($url, $title, $attributes, $secure);
        }

        /**
         * Generate a HTTPS HTML link to an asset.
         *
         * @param string $url
         * @param string $title
         * @param array $attributes
         * @return string
         */
        public static function linkSecureAsset($url, $title = null, $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::linkSecureAsset($url, $title, $attributes);
        }

        /**
         * Generate a HTML link to a named route.
         *
         * @param string $name
         * @param string $title
         * @param array $parameters
         * @param array $attributes
         * @return string
         */
        public static function linkRoute($name, $title = null, $parameters = array(), $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::linkRoute($name, $title, $parameters, $attributes);
        }

        /**
         * Generate a HTML link to a controller action.
         *
         * @param string $action
         * @param string $title
         * @param array $parameters
         * @param array $attributes
         * @return string
         */
        public static function linkAction($action, $title = null, $parameters = array(), $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::linkAction($action, $title, $parameters, $attributes);
        }

        /**
         * Generate a HTML link to an email address.
         *
         * @param string $email
         * @param string $title
         * @param array $attributes
         * @return string
         */
        public static function mailto($email, $title = null, $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::mailto($email, $title, $attributes);
        }

        /**
         * Obfuscate an e-mail address to prevent spam-bots from sniffing it.
         *
         * @param string $email
         * @return string
         */
        public static function email($email){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::email($email);
        }

        /**
         * Generate an ordered list of items.
         *
         * @param array $list
         * @param array $attributes
         * @return string
         */
        public static function ol($list, $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::ol($list, $attributes);
        }

        /**
         * Generate an un-ordered list of items.
         *
         * @param array $list
         * @param array $attributes
         * @return string
         */
        public static function ul($list, $attributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::ul($list, $attributes);
        }

        /**
         * Build an HTML attribute string from an array.
         *
         * @param array $attributes
         * @return string
         */
        public static function attributes($attributes){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::attributes($attributes);
        }

        /**
         * Obfuscate a string to prevent spam-bots from sniffing it.
         *
         * @param string $value
         * @return string
         */
        public static function obfuscate($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::obfuscate($value);
        }

        /**
         * Register a custom macro.
         *
         * @param string $name
         * @param callable $macro
         * @return void
         */
        public static function macro($name, $macro){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Html\HtmlBuilder::macro($name, $macro);
        }

        /**
         * Checks if macro is registered
         *
         * @param string $name
         * @return boolean
         */
        public static function hasMacro($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Html\HtmlBuilder::hasMacro($name);
        }

    }

    class Input extends \Illuminate\Support\Facades\Input{

        /**
         * Return the Request instance.
         *
         * @return $this
         */
        public static function instance(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::instance();
        }

        /**
         * Get the request method.
         *
         * @return string
         */
        public static function method(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::method();
        }

        /**
         * Get the root URL for the application.
         *
         * @return string
         */
        public static function root(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::root();
        }

        /**
         * Get the URL (no query string) for the request.
         *
         * @return string
         */
        public static function url(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::url();
        }

        /**
         * Get the full URL for the request.
         *
         * @return string
         */
        public static function fullUrl(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::fullUrl();
        }

        /**
         * Get the current path info for the request.
         *
         * @return string
         */
        public static function path(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::path();
        }

        /**
         * Get the current encoded path info for the request.
         *
         * @return string
         */
        public static function decodedPath(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::decodedPath();
        }

        /**
         * Get a segment from the URI (1 based index).
         *
         * @param string $index
         * @param mixed $default
         * @return string
         */
        public static function segment($index, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::segment($index, $default);
        }

        /**
         * Get all of the segments for the request path.
         *
         * @return array
         */
        public static function segments(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::segments();
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Determine if the current request URI matches a pattern.
         *
         * @param mixed  string
         * @return bool
         */
        public static function is(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::is();
        }

        /**
         * Determine if the request is the result of an AJAX call.
         *
         * @return bool
         */
        public static function ajax(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::ajax();
        }

        /**
         * Determine if the request is over HTTPS.
         *
         * @return bool
         */
        public static function secure(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::secure();
        }

        /**
         * Returns the client IP address.
         *
         * @return string
         */
        public static function ip(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::ip();
        }

        /**
         * Returns the client IP addresses.
         *
         * @return array
         */
        public static function ips(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::ips();
        }

        /**
         * Determine if the request contains a given input item key.
         *
         * @param string|array $key
         * @return bool
         */
        public static function exists($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::exists($key);
        }

        /**
         * Determine if the request contains a non-empty value for an input item.
         *
         * @param string|array $key
         * @return bool
         */
        public static function has($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::has($key);
        }

        /**
         * Get all of the input and files for the request.
         *
         * @return array
         */
        public static function all(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::all();
        }

        /**
         * Retrieve an input item from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function input($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::input($key, $default);
        }

        /**
         * Get a subset of the items from the input data.
         *
         * @param array $keys
         * @return array
         */
        public static function only($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::only($keys);
        }

        /**
         * Get all of the input except for a specified array of items.
         *
         * @param array $keys
         * @return array
         */
        public static function except($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::except($keys);
        }

        /**
         * Retrieve a query string item from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string|array
         */
        public static function query($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::query($key, $default);
        }

        /**
         * Determine if a cookie is set on the request.
         *
         * @param string $key
         * @return bool
         */
        public static function hasCookie($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasCookie($key);
        }

        /**
         * Retrieve a cookie from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function cookie($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::cookie($key, $default);
        }

        /**
         * Retrieve a file from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return \Symfony\Component\HttpFoundation\File\UploadedFile|array
         */
        public static function file($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::file($key, $default);
        }

        /**
         * Determine if the uploaded data contains a file.
         *
         * @param string $key
         * @return bool
         */
        public static function hasFile($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasFile($key);
        }

        /**
         * Retrieve a header from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function header($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::header($key, $default);
        }

        /**
         * Retrieve a server variable from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function server($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::server($key, $default);
        }

        /**
         * Retrieve an old input item.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function old($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::old($key, $default);
        }

        /**
         * Flash the input for the current request to the session.
         *
         * @param string $filter
         * @param array $keys
         * @return void
         */
        public static function flash($filter = null, $keys = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flash($filter, $keys);
        }

        /**
         * Flash only some of the input to the session.
         *
         * @param mixed  string
         * @return void
         */
        public static function flashOnly($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flashOnly($keys);
        }

        /**
         * Flash only some of the input to the session.
         *
         * @param mixed  string
         * @return void
         */
        public static function flashExcept($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flashExcept($keys);
        }

        /**
         * Flush all of the old input from the session.
         *
         * @return void
         */
        public static function flush(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flush();
        }

        /**
         * Merge new input into the current request's input array.
         *
         * @param array $input
         * @return void
         */
        public static function merge($input){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::merge($input);
        }

        /**
         * Replace the input for the current request.
         *
         * @param array $input
         * @return void
         */
        public static function replace($input){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::replace($input);
        }

        /**
         * Get the JSON payload for the request.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function json($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::json($key, $default);
        }

        /**
         * Determine if the request is sending JSON.
         *
         * @return bool
         */
        public static function isJson(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isJson();
        }

        /**
         * Determine if the current request is asking for JSON in return.
         *
         * @return bool
         */
        public static function wantsJson(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::wantsJson();
        }

        /**
         * Get the data format expected in the response.
         *
         * @param string $default
         * @return string
         */
        public static function format($default = 'html'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::format($default);
        }

        /**
         * Create an Illuminate request from a Symfony instance.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return \Illuminate\Http\Request
         */
        public static function createFromBase($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::createFromBase($request);
        }

        /**
         * Get the session associated with the request.
         *
         * @return \Illuminate\Session\Store
         * @throws \RuntimeException
         */
        public static function session(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::session();
        }

        /**
         * Sets the parameters for this request.
         *
         * This method also re-initializes all properties.
         *
         * @param array $query The GET parameters
         * @param array $request The POST parameters
         * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
         * @param array $cookies The COOKIE parameters
         * @param array $files The FILES parameters
         * @param array $server The SERVER parameters
         * @param string $content The raw body data
         * @api
         */
        public static function initialize($query = array(), $request = array(), $attributes = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        }

        /**
         * Creates a new request with values from PHP's super globals.
         *
         * @return \Symfony\Component\HttpFoundation\Request A new request
         * @api
         */
        public static function createFromGlobals(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::createFromGlobals();
        }

        /**
         * Creates a Request based on a given URI and configuration.
         *
         * The information contained in the URI always take precedence
         * over the other information (server and parameters).
         *
         * @param string $uri The URI
         * @param string $method The HTTP method
         * @param array $parameters The query (GET) or request (POST) parameters
         * @param array $cookies The request cookies ($_COOKIE)
         * @param array $files The request files ($_FILES)
         * @param array $server The server parameters ($_SERVER)
         * @param string $content The raw body data
         * @return \Symfony\Component\HttpFoundation\Request A Request instance
         * @api
         */
        public static function create($uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::create($uri, $method, $parameters, $cookies, $files, $server, $content);
        }

        /**
         * Sets a callable able to create a Request instance.
         *
         * This is mainly useful when you need to override the Request class
         * to keep BC with an existing system. It should not be used for any
         * other purpose.
         *
         * @param callable|null $callable A PHP callable
         */
        public static function setFactory($callable){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setFactory($callable);
        }

        /**
         * Clones a request and overrides some of its parameters.
         *
         * @param array $query The GET parameters
         * @param array $request The POST parameters
         * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
         * @param array $cookies The COOKIE parameters
         * @param array $files The FILES parameters
         * @param array $server The SERVER parameters
         * @return \Symfony\Component\HttpFoundation\Request The duplicated request
         * @api
         */
        public static function duplicate($query = null, $request = null, $attributes = null, $cookies = null, $files = null, $server = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::duplicate($query, $request, $attributes, $cookies, $files, $server);
        }

        /**
         * Overrides the PHP global variables according to this request instance.
         *
         * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
         * $_FILES is never overridden, see rfc1867
         *
         * @api
         */
        public static function overrideGlobals(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::overrideGlobals();
        }

        /**
         * Sets a list of trusted proxies.
         *
         * You should only list the reverse proxies that you manage directly.
         *
         * @param array $proxies A list of trusted proxies
         * @api
         */
        public static function setTrustedProxies($proxies){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setTrustedProxies($proxies);
        }

        /**
         * Gets the list of trusted proxies.
         *
         * @return array An array of trusted proxies.
         */
        public static function getTrustedProxies(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getTrustedProxies();
        }

        /**
         * Sets a list of trusted host patterns.
         *
         * You should only list the hosts you manage using regexs.
         *
         * @param array $hostPatterns A list of trusted host patterns
         */
        public static function setTrustedHosts($hostPatterns){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setTrustedHosts($hostPatterns);
        }

        /**
         * Gets the list of trusted host patterns.
         *
         * @return array An array of trusted host patterns.
         */
        public static function getTrustedHosts(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getTrustedHosts();
        }

        /**
         * Sets the name for trusted headers.
         *
         * The following header keys are supported:
         *
         *  * Request::HEADER_CLIENT_IP:    defaults to X-Forwarded-For   (see getClientIp())
         *  * Request::HEADER_CLIENT_HOST:  defaults to X-Forwarded-Host  (see getClientHost())
         *  * Request::HEADER_CLIENT_PORT:  defaults to X-Forwarded-Port  (see getClientPort())
         *  * Request::HEADER_CLIENT_PROTO: defaults to X-Forwarded-Proto (see getScheme() and isSecure())
         *
         * Setting an empty value allows to disable the trusted header for the given key.
         *
         * @param string $key The header key
         * @param string $value The header name
         * @throws \InvalidArgumentException
         */
        public static function setTrustedHeaderName($key, $value){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setTrustedHeaderName($key, $value);
        }

        /**
         * Gets the trusted proxy header name.
         *
         * @param string $key The header key
         * @return string The header name
         * @throws \InvalidArgumentException
         */
        public static function getTrustedHeaderName($key){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getTrustedHeaderName($key);
        }

        /**
         * Normalizes a query string.
         *
         * It builds a normalized query string, where keys/value pairs are alphabetized,
         * have consistent escaping and unneeded delimiters are removed.
         *
         * @param string $qs Query string
         * @return string A normalized query string for the Request
         */
        public static function normalizeQueryString($qs){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::normalizeQueryString($qs);
        }

        /**
         * Enables support for the _method request parameter to determine the intended HTTP method.
         *
         * Be warned that enabling this feature might lead to CSRF issues in your code.
         * Check that you are using CSRF tokens when required.
         * If the HTTP method parameter override is enabled, an html-form with method "POST" can be altered
         * and used to send a "PUT" or "DELETE" request via the _method request parameter.
         * If these methods are not protected against CSRF, this presents a possible vulnerability.
         *
         * The HTTP method can only be overridden when the real HTTP method is POST.
         */
        public static function enableHttpMethodParameterOverride(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::enableHttpMethodParameterOverride();
        }

        /**
         * Checks whether support for the _method request parameter is enabled.
         *
         * @return bool True when the _method request parameter is enabled, false otherwise
         */
        public static function getHttpMethodParameterOverride(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getHttpMethodParameterOverride();
        }

        /**
         * Gets a "parameter" value.
         *
         * This method is mainly useful for libraries that want to provide some flexibility.
         *
         * Order of precedence: GET, PATH, POST
         *
         * Avoid using this method in controllers:
         *
         *  * slow
         *  * prefer to get from a "named" source
         *
         * It is better to explicitly get request parameters from the appropriate
         * public property instead (query, attributes, request).
         *
         * @param string $key the key
         * @param mixed $default the default value
         * @param bool $deep is parameter deep in multidimensional array
         * @return mixed
         */
        public static function get($key, $default = null, $deep = false){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::get($key, $default, $deep);
        }

        /**
         * Gets the Session.
         *
         * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|null The session
         * @api
         */
        public static function getSession(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getSession();
        }

        /**
         * Whether the request contains a Session which was started in one of the
         * previous requests.
         *
         * @return bool
         * @api
         */
        public static function hasPreviousSession(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasPreviousSession();
        }

        /**
         * Whether the request contains a Session object.
         *
         * This method does not give any information about the state of the session object,
         * like whether the session is started or not. It is just a way to check if this Request
         * is associated with a Session instance.
         *
         * @return bool true when the Request contains a Session object, false otherwise
         * @api
         */
        public static function hasSession(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasSession();
        }

        /**
         * Sets the Session.
         *
         * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session The Session
         * @api
         */
        public static function setSession($session){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setSession($session);
        }

        /**
         * Returns the client IP addresses.
         *
         * In the returned array the most trusted IP address is first, and the
         * least trusted one last. The "real" client IP address is the last one,
         * but this is also the least trusted one. Trusted proxies are stripped.
         *
         * Use this method carefully; you should use getClientIp() instead.
         *
         * @return array The client IP addresses
         * @see getClientIp()
         */
        public static function getClientIps(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getClientIps();
        }

        /**
         * Returns the client IP address.
         *
         * This method can read the client IP address from the "X-Forwarded-For" header
         * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
         * header value is a comma+space separated list of IP addresses, the left-most
         * being the original client, and each successive proxy that passed the request
         * adding the IP address where it received the request from.
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-For",
         * ("Client-Ip" for instance), configure it via "setTrustedHeaderName()" with
         * the "client-ip" key.
         *
         * @return string The client IP address
         * @see getClientIps()
         * @see http://en.wikipedia.org/wiki/X-Forwarded-For
         * @api
         */
        public static function getClientIp(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getClientIp();
        }

        /**
         * Returns current script name.
         *
         * @return string
         * @api
         */
        public static function getScriptName(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getScriptName();
        }

        /**
         * Returns the path being requested relative to the executed script.
         *
         * The path info always starts with a /.
         *
         * Suppose this request is instantiated from /mysite on localhost:
         *
         *  * http://localhost/mysite              returns an empty string
         *  * http://localhost/mysite/about        returns '/about'
         *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
         *  * http://localhost/mysite/about?var=1  returns '/about'
         *
         * @return string The raw path (i.e. not urldecoded)
         * @api
         */
        public static function getPathInfo(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPathInfo();
        }

        /**
         * Returns the root path from which this request is executed.
         *
         * Suppose that an index.php file instantiates this request object:
         *
         *  * http://localhost/index.php         returns an empty string
         *  * http://localhost/index.php/page    returns an empty string
         *  * http://localhost/web/index.php     returns '/web'
         *  * http://localhost/we%20b/index.php  returns '/we%20b'
         *
         * @return string The raw path (i.e. not urldecoded)
         * @api
         */
        public static function getBasePath(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getBasePath();
        }

        /**
         * Returns the root URL from which this request is executed.
         *
         * The base URL never ends with a /.
         *
         * This is similar to getBasePath(), except that it also includes the
         * script filename (e.g. index.php) if one exists.
         *
         * @return string The raw URL (i.e. not urldecoded)
         * @api
         */
        public static function getBaseUrl(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getBaseUrl();
        }

        /**
         * Gets the request's scheme.
         *
         * @return string
         * @api
         */
        public static function getScheme(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getScheme();
        }

        /**
         * Returns the port on which the request is made.
         *
         * This method can read the client port from the "X-Forwarded-Port" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Port" header must contain the client port.
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-Port",
         * configure it via "setTrustedHeaderName()" with the "client-port" key.
         *
         * @return string
         * @api
         */
        public static function getPort(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPort();
        }

        /**
         * Returns the user.
         *
         * @return string|null
         */
        public static function getUser(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUser();
        }

        /**
         * Returns the password.
         *
         * @return string|null
         */
        public static function getPassword(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPassword();
        }

        /**
         * Gets the user info.
         *
         * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
         */
        public static function getUserInfo(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUserInfo();
        }

        /**
         * Returns the HTTP host being requested.
         *
         * The port name will be appended to the host if it's non-standard.
         *
         * @return string
         * @api
         */
        public static function getHttpHost(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getHttpHost();
        }

        /**
         * Returns the requested URI (path and query string).
         *
         * @return string The raw URI (i.e. not URI decoded)
         * @api
         */
        public static function getRequestUri(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getRequestUri();
        }

        /**
         * Gets the scheme and HTTP host.
         *
         * If the URL was called with basic authentication, the user
         * and the password are not added to the generated string.
         *
         * @return string The scheme and HTTP host
         */
        public static function getSchemeAndHttpHost(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getSchemeAndHttpHost();
        }

        /**
         * Generates a normalized URI (URL) for the Request.
         *
         * @return string A normalized URI (URL) for the Request
         * @see getQueryString()
         * @api
         */
        public static function getUri(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUri();
        }

        /**
         * Generates a normalized URI for the given path.
         *
         * @param string $path A path to use instead of the current one
         * @return string The normalized URI for the path
         * @api
         */
        public static function getUriForPath($path){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUriForPath($path);
        }

        /**
         * Generates the normalized query string for the Request.
         *
         * It builds a normalized query string, where keys/value pairs are alphabetized
         * and have consistent escaping.
         *
         * @return string|null A normalized query string for the Request
         * @api
         */
        public static function getQueryString(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getQueryString();
        }

        /**
         * Checks whether the request is secure or not.
         *
         * This method can read the client port from the "X-Forwarded-Proto" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-Proto"
         * ("SSL_HTTPS" for instance), configure it via "setTrustedHeaderName()" with
         * the "client-proto" key.
         *
         * @return bool
         * @api
         */
        public static function isSecure(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isSecure();
        }

        /**
         * Returns the host name.
         *
         * This method can read the client port from the "X-Forwarded-Host" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Host" header must contain the client host name.
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-Host",
         * configure it via "setTrustedHeaderName()" with the "client-host" key.
         *
         * @return string
         * @throws \UnexpectedValueException when the host name is invalid
         * @api
         */
        public static function getHost(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getHost();
        }

        /**
         * Sets the request method.
         *
         * @param string $method
         * @api
         */
        public static function setMethod($method){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setMethod($method);
        }

        /**
         * Gets the request "intended" method.
         *
         * If the X-HTTP-Method-Override header is set, and if the method is a POST,
         * then it is used to determine the "real" intended HTTP method.
         *
         * The _method request parameter can also be used to determine the HTTP method,
         * but only if enableHttpMethodParameterOverride() has been called.
         *
         * The method is always an uppercased string.
         *
         * @return string The request method
         * @api
         * @see getRealMethod()
         */
        public static function getMethod(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getMethod();
        }

        /**
         * Gets the "real" request method.
         *
         * @return string The request method
         * @see getMethod()
         */
        public static function getRealMethod(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getRealMethod();
        }

        /**
         * Gets the mime type associated with the format.
         *
         * @param string $format The format
         * @return string The associated mime type (null if not found)
         * @api
         */
        public static function getMimeType($format){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getMimeType($format);
        }

        /**
         * Gets the format associated with the mime type.
         *
         * @param string $mimeType The associated mime type
         * @return string|null The format (null if not found)
         * @api
         */
        public static function getFormat($mimeType){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getFormat($mimeType);
        }

        /**
         * Associates a format with mime types.
         *
         * @param string $format The format
         * @param string|array $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
         * @api
         */
        public static function setFormat($format, $mimeTypes){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setFormat($format, $mimeTypes);
        }

        /**
         * Gets the request format.
         *
         * Here is the process to determine the format:
         *
         *  * format defined by the user (with setRequestFormat())
         *  * _format request parameter
         *  * $default
         *
         * @param string $default The default format
         * @return string The request format
         * @api
         */
        public static function getRequestFormat($default = 'html'){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getRequestFormat($default);
        }

        /**
         * Sets the request format.
         *
         * @param string $format The request format.
         * @api
         */
        public static function setRequestFormat($format){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setRequestFormat($format);
        }

        /**
         * Gets the format associated with the request.
         *
         * @return string|null The format (null if no content type is present)
         * @api
         */
        public static function getContentType(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getContentType();
        }

        /**
         * Sets the default locale.
         *
         * @param string $locale
         * @api
         */
        public static function setDefaultLocale($locale){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setDefaultLocale($locale);
        }

        /**
         * Get the default locale.
         *
         * @return string
         */
        public static function getDefaultLocale(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getDefaultLocale();
        }

        /**
         * Sets the locale.
         *
         * @param string $locale
         * @api
         */
        public static function setLocale($locale){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setLocale($locale);
        }

        /**
         * Get the locale.
         *
         * @return string
         */
        public static function getLocale(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getLocale();
        }

        /**
         * Checks if the request method is of specified type.
         *
         * @param string $method Uppercase request method (GET, POST etc).
         * @return bool
         */
        public static function isMethod($method){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isMethod($method);
        }

        /**
         * Checks whether the method is safe or not.
         *
         * @return bool
         * @api
         */
        public static function isMethodSafe(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isMethodSafe();
        }

        /**
         * Returns the request body content.
         *
         * @param bool $asResource If true, a resource will be returned
         * @return string|resource The request body content or a resource to read the body stream.
         * @throws \LogicException
         */
        public static function getContent($asResource = false){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getContent($asResource);
        }

        /**
         * Gets the Etags.
         *
         * @return array The entity tags
         */
        public static function getETags(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getETags();
        }

        /**
         * @return bool
         */
        public static function isNoCache(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isNoCache();
        }

        /**
         * Returns the preferred language.
         *
         * @param array $locales An array of ordered available locales
         * @return string|null The preferred locale
         * @api
         */
        public static function getPreferredLanguage($locales = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPreferredLanguage($locales);
        }

        /**
         * Gets a list of languages acceptable by the client browser.
         *
         * @return array Languages ordered in the user browser preferences
         * @api
         */
        public static function getLanguages(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getLanguages();
        }

        /**
         * Gets a list of charsets acceptable by the client browser.
         *
         * @return array List of charsets in preferable order
         * @api
         */
        public static function getCharsets(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getCharsets();
        }

        /**
         * Gets a list of encodings acceptable by the client browser.
         *
         * @return array List of encodings in preferable order
         */
        public static function getEncodings(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getEncodings();
        }

        /**
         * Gets a list of content types acceptable by the client browser.
         *
         * @return array List of content types in preferable order
         * @api
         */
        public static function getAcceptableContentTypes(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getAcceptableContentTypes();
        }

        /**
         * Returns true if the request is a XMLHttpRequest.
         *
         * It works if your JavaScript library sets an X-Requested-With HTTP header.
         * It is known to work with common JavaScript frameworks:
         *
         * @link http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
         * @return bool true if the request is an XMLHttpRequest, false otherwise
         * @api
         */
        public static function isXmlHttpRequest(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isXmlHttpRequest();
        }

    }

    class Lang extends \Illuminate\Support\Facades\Lang{

        /**
         * Determine if a translation exists.
         *
         * @param string $key
         * @param string $locale
         * @return bool
         */
        public static function has($key, $locale = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::has($key, $locale);
        }

        /**
         * Get the translation for the given key.
         *
         * @param string $key
         * @param array $replace
         * @param string $locale
         * @return string
         */
        public static function get($key, $replace = array(), $locale = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::get($key, $replace, $locale);
        }

        /**
         * Get a translation according to an integer value.
         *
         * @param string $key
         * @param int $number
         * @param array $replace
         * @param string $locale
         * @return string
         */
        public static function choice($key, $number, $replace = array(), $locale = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::choice($key, $number, $replace, $locale);
        }

        /**
         * Get the translation for a given key.
         *
         * @param string $id
         * @param array $parameters
         * @param string $domain
         * @param string $locale
         * @return string
         */
        public static function trans($id, $parameters = array(), $domain = 'messages', $locale = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::trans($id, $parameters, $domain, $locale);
        }

        /**
         * Get a translation according to an integer value.
         *
         * @param string $id
         * @param int $number
         * @param array $parameters
         * @param string $domain
         * @param string $locale
         * @return string
         */
        public static function transChoice($id, $number, $parameters = array(), $domain = 'messages', $locale = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::transChoice($id, $number, $parameters, $domain, $locale);
        }

        /**
         * Load the specified language group.
         *
         * @param string $namespace
         * @param string $group
         * @param string $locale
         * @return void
         */
        public static function load($namespace, $group, $locale){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Translation\Translator::load($namespace, $group, $locale);
        }

        /**
         * Add a new namespace to the loader.
         *
         * @param string $namespace
         * @param string $hint
         * @return void
         */
        public static function addNamespace($namespace, $hint){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Translation\Translator::addNamespace($namespace, $hint);
        }

        /**
         * Parse a key into namespace, group, and item.
         *
         * @param string $key
         * @return array
         */
        public static function parseKey($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::parseKey($key);
        }

        /**
         * Get the message selector instance.
         *
         * @return \Symfony\Component\Translation\MessageSelector
         */
        public static function getSelector(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::getSelector();
        }

        /**
         * Set the message selector instance.
         *
         * @param \Symfony\Component\Translation\MessageSelector $selector
         * @return void
         */
        public static function setSelector($selector){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Translation\Translator::setSelector($selector);
        }

        /**
         * Get the language line loader implementation.
         *
         * @return \Illuminate\Translation\LoaderInterface
         */
        public static function getLoader(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::getLoader();
        }

        /**
         * Get the default locale being used.
         *
         * @return string
         */
        public static function locale(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::locale();
        }

        /**
         * Get the default locale being used.
         *
         * @return string
         */
        public static function getLocale(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::getLocale();
        }

        /**
         * Set the default locale.
         *
         * @param string $locale
         * @return void
         */
        public static function setLocale($locale){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Translation\Translator::setLocale($locale);
        }

        /**
         * Get the fallback locale being used.
         *
         * @return string
         */
        public static function getFallback(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Translation\Translator::getFallback();
        }

        /**
         * Set the fallback locale being used.
         *
         * @param string $fallback
         * @return void
         */
        public static function setFallback($fallback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Translation\Translator::setFallback($fallback);
        }

        /**
         * Set the parsed value of a key.
         *
         * @param string $key
         * @param array $parsed
         * @return void
         */
        public static function setParsedKey($key, $parsed){
            //Method inherited from \Illuminate\Support\NamespacedItemResolver
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Translation\Translator::setParsedKey($key, $parsed);
        }

    }

    class Log extends \Illuminate\Support\Facades\Log{

        /**
         * Adds a log record at the DEBUG level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function debug($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::debug($message, $context);
        }

        /**
         * Adds a log record at the INFO level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function info($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::info($message, $context);
        }

        /**
         * Adds a log record at the NOTICE level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function notice($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::notice($message, $context);
        }

        /**
         * Adds a log record at the WARNING level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function warning($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::warning($message, $context);
        }

        /**
         * Adds a log record at the ERROR level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function error($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::error($message, $context);
        }

        /**
         * Adds a log record at the CRITICAL level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function critical($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::critical($message, $context);
        }

        /**
         * Adds a log record at the ALERT level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function alert($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::alert($message, $context);
        }

        /**
         * Adds a log record at the EMERGENCY level.
         *
         * @param string $message The log message
         * @param array $context The log context
         * @return bool Whether the record has been processed
         */
        public static function emergency($message, $context = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Monolog\Logger::emergency($message, $context);
        }

        /**
         * Register a file log handler.
         *
         * @param string $path
         * @param string $level
         * @return void
         */
        public static function useFiles($path, $level = 'debug'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Log\Writer::useFiles($path, $level);
        }

        /**
         * Register a daily file log handler.
         *
         * @param string $path
         * @param int $days
         * @param string $level
         * @return void
         */
        public static function useDailyFiles($path, $days = 0, $level = 'debug'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Log\Writer::useDailyFiles($path, $days, $level);
        }

        /**
         * Register an error_log handler.
         *
         * @param string $level
         * @param int $messageType
         * @return void
         */
        public static function useErrorLog($level = 'debug', $messageType = 0){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Log\Writer::useErrorLog($level, $messageType);
        }

        /**
         * Register a new callback handler for when
         * a log event is triggered.
         *
         * @param \Closure $callback
         * @return void
         * @throws \RuntimeException
         */
        public static function listen($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Log\Writer::listen($callback);
        }

        /**
         * Get the underlying Monolog instance.
         *
         * @return \Monolog\Logger
         */
        public static function getMonolog(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Log\Writer::getMonolog();
        }

        /**
         * Get the event dispatcher instance.
         *
         * @return \Illuminate\Events\Dispatcher
         */
        public static function getEventDispatcher(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Log\Writer::getEventDispatcher();
        }

        /**
         * Set the event dispatcher instance.
         *
         * @param \Illuminate\Events\Dispatcher
         * @return void
         */
        public static function setEventDispatcher($dispatcher){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Log\Writer::setEventDispatcher($dispatcher);
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Dynamically pass log calls into the writer.
         *
         * @param mixed  (level, param, param)
         * @return mixed
         */
        public static function write(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Log\Writer::write();
        }

    }

    class Mail extends \Illuminate\Support\Facades\Mail{

        /**
         * Set the global from address and name.
         *
         * @param string $address
         * @param string $name
         * @return void
         */
        public static function alwaysFrom($address, $name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Mail\Mailer::alwaysFrom($address, $name);
        }

        /**
         * Send a new message when only a plain part.
         *
         * @param string $view
         * @param array $data
         * @param mixed $callback
         * @return int
         */
        public static function plain($view, $data, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::plain($view, $data, $callback);
        }

        /**
         * Send a new message using a view.
         *
         * @param string|array $view
         * @param array $data
         * @param \Closure|string $callback
         * @return void
         */
        public static function send($view, $data, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Mail\Mailer::send($view, $data, $callback);
        }

        /**
         * Queue a new e-mail message for sending.
         *
         * @param string|array $view
         * @param array $data
         * @param \Closure|string $callback
         * @param string $queue
         * @return mixed
         */
        public static function queue($view, $data, $callback, $queue = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::queue($view, $data, $callback, $queue);
        }

        /**
         * Queue a new e-mail message for sending on the given queue.
         *
         * @param string $queue
         * @param string|array $view
         * @param array $data
         * @param \Closure|string $callback
         * @return mixed
         */
        public static function queueOn($queue, $view, $data, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::queueOn($queue, $view, $data, $callback);
        }

        /**
         * Queue a new e-mail message for sending after (n) seconds.
         *
         * @param int $delay
         * @param string|array $view
         * @param array $data
         * @param \Closure|string $callback
         * @param string $queue
         * @return mixed
         */
        public static function later($delay, $view, $data, $callback, $queue = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::later($delay, $view, $data, $callback, $queue);
        }

        /**
         * Queue a new e-mail message for sending after (n) seconds on the given queue.
         *
         * @param string $queue
         * @param int $delay
         * @param string|array $view
         * @param array $data
         * @param \Closure|string $callback
         * @return mixed
         */
        public static function laterOn($queue, $delay, $view, $data, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::laterOn($queue, $delay, $view, $data, $callback);
        }

        /**
         * Handle a queued e-mail message job.
         *
         * @param \Illuminate\Queue\Jobs\Job $job
         * @param array $data
         * @return void
         */
        public static function handleQueuedMessage($job, $data){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Mail\Mailer::handleQueuedMessage($job, $data);
        }

        /**
         * Tell the mailer to not really send messages.
         *
         * @param bool $value
         * @return void
         */
        public static function pretend($value = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Mail\Mailer::pretend($value);
        }

        /**
         * Check if the mailer is pretending to send messages.
         *
         * @return bool
         */
        public static function isPretending(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::isPretending();
        }

        /**
         * Get the view factory instance.
         *
         * @return \Illuminate\View\Factory
         */
        public static function getViewFactory(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::getViewFactory();
        }

        /**
         * Get the Swift Mailer instance.
         *
         * @return \Swift_Mailer
         */
        public static function getSwiftMailer(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::getSwiftMailer();
        }

        /**
         * Get the array of failed recipients.
         *
         * @return array
         */
        public static function failures(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::failures();
        }

        /**
         * Set the Swift Mailer instance.
         *
         * @param \Swift_Mailer $swift
         * @return void
         */
        public static function setSwiftMailer($swift){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Mail\Mailer::setSwiftMailer($swift);
        }

        /**
         * Set the log writer instance.
         *
         * @param \Illuminate\Log\Writer $logger
         * @return $this
         */
        public static function setLogger($logger){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::setLogger($logger);
        }

        /**
         * Set the queue manager instance.
         *
         * @param \Illuminate\Queue\QueueManager $queue
         * @return $this
         */
        public static function setQueue($queue){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Mail\Mailer::setQueue($queue);
        }

        /**
         * Set the IoC container instance.
         *
         * @param \Illuminate\Container\Container $container
         * @return void
         */
        public static function setContainer($container){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Mail\Mailer::setContainer($container);
        }

    }

    class Paginator extends \Illuminate\Support\Facades\Paginator{

        /**
         * Get a new paginator instance.
         *
         * @param array $items
         * @param int $total
         * @param int|null $perPage
         * @return \Illuminate\Pagination\Paginator
         */
        public static function make($items, $total, $perPage = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::make($items, $total, $perPage);
        }

        /**
         * Get the pagination view.
         *
         * @param \Illuminate\Pagination\Paginator $paginator
         * @param string $view
         * @return \Illuminate\View\View
         */
        public static function getPaginationView($paginator, $view = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getPaginationView($paginator, $view);
        }

        /**
         * Get the number of the current page.
         *
         * @return int
         */
        public static function getCurrentPage(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getCurrentPage();
        }

        /**
         * Set the number of the current page.
         *
         * @param int $number
         * @return void
         */
        public static function setCurrentPage($number){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Pagination\Factory::setCurrentPage($number);
        }

        /**
         * Get the root URL for the request.
         *
         * @return string
         */
        public static function getCurrentUrl(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getCurrentUrl();
        }

        /**
         * Set the base URL in use by the paginator.
         *
         * @param string $baseUrl
         * @return void
         */
        public static function setBaseUrl($baseUrl){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Pagination\Factory::setBaseUrl($baseUrl);
        }

        /**
         * Set the input page parameter name used by the paginator.
         *
         * @param string $pageName
         * @return void
         */
        public static function setPageName($pageName){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Pagination\Factory::setPageName($pageName);
        }

        /**
         * Get the input page parameter name used by the paginator.
         *
         * @return string
         */
        public static function getPageName(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getPageName();
        }

        /**
         * Get the name of the pagination view.
         *
         * @param string $view
         * @return string
         */
        public static function getViewName($view = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getViewName($view);
        }

        /**
         * Set the name of the pagination view.
         *
         * @param string $viewName
         * @return void
         */
        public static function setViewName($viewName){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Pagination\Factory::setViewName($viewName);
        }

        /**
         * Get the locale of the paginator.
         *
         * @return string
         */
        public static function getLocale(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getLocale();
        }

        /**
         * Set the locale of the paginator.
         *
         * @param string $locale
         * @return void
         */
        public static function setLocale($locale){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Pagination\Factory::setLocale($locale);
        }

        /**
         * Get the active request instance.
         *
         * @return \Symfony\Component\HttpFoundation\Request
         */
        public static function getRequest(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getRequest();
        }

        /**
         * Set the active request instance.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return void
         */
        public static function setRequest($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Pagination\Factory::setRequest($request);
        }

        /**
         * Get the current view factory.
         *
         * @return \Illuminate\View\Factory
         */
        public static function getViewFactory(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getViewFactory();
        }

        /**
         * Set the current view factory.
         *
         * @param \Illuminate\View\Factory $view
         * @return void
         */
        public static function setViewFactory($view){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Pagination\Factory::setViewFactory($view);
        }

        /**
         * Get the translator instance.
         *
         * @return \Symfony\Component\Translation\TranslatorInterface
         */
        public static function getTranslator(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Pagination\Factory::getTranslator();
        }

    }

    class Password extends \Illuminate\Support\Facades\Password{

        /**
         * Send a password reminder to a user.
         *
         * @param array $credentials
         * @param \Closure $callback
         * @return string
         */
        public static function remind($credentials, $callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Reminders\PasswordBroker::remind($credentials, $callback);
        }

        /**
         * Send the password reminder e-mail.
         *
         * @param \Illuminate\Auth\Reminders\RemindableInterface $user
         * @param string $token
         * @param \Closure $callback
         * @return int
         */
        public static function sendReminder($user, $token, $callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Reminders\PasswordBroker::sendReminder($user, $token, $callback);
        }

        /**
         * Reset the password for the given token.
         *
         * @param array $credentials
         * @param \Closure $callback
         * @return mixed
         */
        public static function reset($credentials, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Reminders\PasswordBroker::reset($credentials, $callback);
        }

        /**
         * Set a custom password validator.
         *
         * @param \Closure $callback
         * @return void
         */
        public static function validator($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Auth\Reminders\PasswordBroker::validator($callback);
        }

        /**
         * Get the user for the given credentials.
         *
         * @param array $credentials
         * @return \Illuminate\Auth\Reminders\RemindableInterface
         * @throws \UnexpectedValueException
         */
        public static function getUser($credentials){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Auth\Reminders\PasswordBroker::getUser($credentials);
        }

    }

    class Queue extends \Illuminate\Support\Facades\Queue{

        /**
         * Register an event listener for the daemon queue loop.
         *
         * @param mixed $callback
         * @return void
         */
        public static function looping($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\QueueManager::looping($callback);
        }

        /**
         * Register an event listener for the failed job event.
         *
         * @param mixed $callback
         * @return void
         */
        public static function failing($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\QueueManager::failing($callback);
        }

        /**
         * Register an event listener for the daemon queue stopping.
         *
         * @param mixed $callback
         * @return void
         */
        public static function stopping($callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\QueueManager::stopping($callback);
        }

        /**
         * Determine if the driver is connected.
         *
         * @param string $name
         * @return bool
         */
        public static function connected($name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\QueueManager::connected($name);
        }

        /**
         * Resolve a queue connection instance.
         *
         * @param string $name
         * @return \Illuminate\Queue\RedisQueue
         */
        public static function connection($name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\QueueManager::connection($name);
        }

        /**
         * Add a queue connection resolver.
         *
         * @param string $driver
         * @param \Closure $resolver
         * @return void
         */
        public static function extend($driver, $resolver){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\QueueManager::extend($driver, $resolver);
        }

        /**
         * Add a queue connection resolver.
         *
         * @param string $driver
         * @param \Closure $resolver
         * @return void
         */
        public static function addConnector($driver, $resolver){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\QueueManager::addConnector($driver, $resolver);
        }

        /**
         * Get the name of the default queue connection.
         *
         * @return string
         */
        public static function getDefaultDriver(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\QueueManager::getDefaultDriver();
        }

        /**
         * Set the name of the default queue connection.
         *
         * @param string $name
         * @return void
         */
        public static function setDefaultDriver($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\QueueManager::setDefaultDriver($name);
        }

        /**
         * Get the full name for the given connection.
         *
         * @param string $connection
         * @return string
         */
        public static function getName($connection = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\QueueManager::getName($connection);
        }

        /**
         * Determine if the application is in maintenance mode.
         *
         * @return bool
         */
        public static function isDownForMaintenance(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\QueueManager::isDownForMaintenance();
        }

        /**
         * Push a new job onto the queue.
         *
         * @param string $job
         * @param mixed $data
         * @param string $queue
         * @return void
         */
        public static function push($job, $data = '', $queue = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::push($job, $data, $queue);
        }

        /**
         * Push a raw payload onto the queue.
         *
         * @param string $payload
         * @param string $queue
         * @param array $options
         * @return mixed
         */
        public static function pushRaw($payload, $queue = null, $options = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\RedisQueue::pushRaw($payload, $queue, $options);
        }

        /**
         * Push a new job onto the queue after a delay.
         *
         * @param \DateTime|int $delay
         * @param string $job
         * @param mixed $data
         * @param string $queue
         * @return void
         */
        public static function later($delay, $job, $data = '', $queue = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::later($delay, $job, $data, $queue);
        }

        /**
         * Release a reserved job back onto the queue.
         *
         * @param string $queue
         * @param string $payload
         * @param int $delay
         * @param int $attempts
         * @return void
         */
        public static function release($queue, $payload, $delay, $attempts){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::release($queue, $payload, $delay, $attempts);
        }

        /**
         * Pop the next job off of the queue.
         *
         * @param string $queue
         * @return \Illuminate\Queue\Jobs\Job|null
         */
        public static function pop($queue = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\RedisQueue::pop($queue);
        }

        /**
         * Delete a reserved job from the queue.
         *
         * @param string $queue
         * @param string $job
         * @return void
         */
        public static function deleteReserved($queue, $job){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::deleteReserved($queue, $job);
        }

        /**
         * Migrate the delayed jobs that are ready to the regular queue.
         *
         * @param string $from
         * @param string $to
         * @return void
         */
        public static function migrateExpiredJobs($from, $to){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::migrateExpiredJobs($from, $to);
        }

        /**
         * Get the underlying Redis instance.
         *
         * @return \Illuminate\Redis\Database
         */
        public static function getRedis(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\RedisQueue::getRedis();
        }

        /**
         * Get the expiration time in seconds.
         *
         * @return int|null
         */
        public static function getExpire(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\RedisQueue::getExpire();
        }

        /**
         * Set the expiration time in seconds.
         *
         * @param int|null $seconds
         * @return void
         */
        public static function setExpire($seconds){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::setExpire($seconds);
        }

        /**
         * Marshal a push queue request and fire the job.
         *
         * @throws \RuntimeException
         */
        public static function marshal(){
            //Method inherited from \Illuminate\Queue\Queue
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Queue\RedisQueue::marshal();
        }

        /**
         * Push an array of jobs onto the queue.
         *
         * @param array $jobs
         * @param mixed $data
         * @param string $queue
         * @return mixed
         */
        public static function bulk($jobs, $data = '', $queue = null){
            //Method inherited from \Illuminate\Queue\Queue
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\RedisQueue::bulk($jobs, $data, $queue);
        }

        /**
         * Get the current UNIX timestamp.
         *
         * @return int
         */
        public static function getTime(){
            //Method inherited from \Illuminate\Queue\Queue
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Queue\RedisQueue::getTime();
        }

        /**
         * Set the IoC container instance.
         *
         * @param \Illuminate\Container\Container $container
         * @return void
         */
        public static function setContainer($container){
            //Method inherited from \Illuminate\Queue\Queue
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::setContainer($container);
        }

        /**
         * Set the encrypter instance.
         *
         * @param \Illuminate\Encryption\Encrypter $crypt
         * @return void
         */
        public static function setEncrypter($crypt){
            //Method inherited from \Illuminate\Queue\Queue
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Queue\RedisQueue::setEncrypter($crypt);
        }

    }

    class Redirect extends \Illuminate\Support\Facades\Redirect{

        /**
         * Create a new redirect response to the "home" route.
         *
         * @param int $status
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function home($status = 302){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::home($status);
        }

        /**
         * Create a new redirect response to the previous location.
         *
         * @param int $status
         * @param array $headers
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function back($status = 302, $headers = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::back($status, $headers);
        }

        /**
         * Create a new redirect response to the current URI.
         *
         * @param int $status
         * @param array $headers
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function refresh($status = 302, $headers = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::refresh($status, $headers);
        }

        /**
         * Create a new redirect response, while putting the current URL in the session.
         *
         * @param string $path
         * @param int $status
         * @param array $headers
         * @param bool $secure
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function guest($path, $status = 302, $headers = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::guest($path, $status, $headers, $secure);
        }

        /**
         * Create a new redirect response to the previously intended location.
         *
         * @param string $default
         * @param int $status
         * @param array $headers
         * @param bool $secure
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function intended($default = '/', $status = 302, $headers = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::intended($default, $status, $headers, $secure);
        }

        /**
         * Create a new redirect response to the given path.
         *
         * @param string $path
         * @param int $status
         * @param array $headers
         * @param bool $secure
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function to($path, $status = 302, $headers = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::to($path, $status, $headers, $secure);
        }

        /**
         * Create a new redirect response to an external URL (no validation).
         *
         * @param string $path
         * @param int $status
         * @param array $headers
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function away($path, $status = 302, $headers = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::away($path, $status, $headers);
        }

        /**
         * Create a new redirect response to the given HTTPS path.
         *
         * @param string $path
         * @param int $status
         * @param array $headers
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function secure($path, $status = 302, $headers = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::secure($path, $status, $headers);
        }

        /**
         * Create a new redirect response to a named route.
         *
         * @param string $route
         * @param array $parameters
         * @param int $status
         * @param array $headers
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function route($route, $parameters = array(), $status = 302, $headers = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::route($route, $parameters, $status, $headers);
        }

        /**
         * Create a new redirect response to a controller action.
         *
         * @param string $action
         * @param array $parameters
         * @param int $status
         * @param array $headers
         * @return \Illuminate\Http\RedirectResponse
         */
        public static function action($action, $parameters = array(), $status = 302, $headers = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::action($action, $parameters, $status, $headers);
        }

        /**
         * Get the URL generator instance.
         *
         * @return \Illuminate\Routing\UrlGenerator
         */
        public static function getUrlGenerator(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\Redirector::getUrlGenerator();
        }

        /**
         * Set the active session store.
         *
         * @param \Illuminate\Session\Store $session
         * @return void
         */
        public static function setSession($session){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Routing\Redirector::setSession($session);
        }

    }

    class Redis extends \Illuminate\Support\Facades\Redis{

        /**
         * Get a specific Redis connection instance.
         *
         * @param string $name
         * @return \Predis\ClientInterface
         */
        public static function connection($name = 'default'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Redis\Database::connection($name);
        }

        /**
         * Run a command against the Redis database.
         *
         * @param string $method
         * @param array $parameters
         * @return mixed
         */
        public static function command($method, $parameters = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Redis\Database::command($method, $parameters);
        }

    }

    class Request extends \Illuminate\Support\Facades\Request{

        /**
         * Return the Request instance.
         *
         * @return $this
         */
        public static function instance(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::instance();
        }

        /**
         * Get the request method.
         *
         * @return string
         */
        public static function method(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::method();
        }

        /**
         * Get the root URL for the application.
         *
         * @return string
         */
        public static function root(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::root();
        }

        /**
         * Get the URL (no query string) for the request.
         *
         * @return string
         */
        public static function url(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::url();
        }

        /**
         * Get the full URL for the request.
         *
         * @return string
         */
        public static function fullUrl(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::fullUrl();
        }

        /**
         * Get the current path info for the request.
         *
         * @return string
         */
        public static function path(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::path();
        }

        /**
         * Get the current encoded path info for the request.
         *
         * @return string
         */
        public static function decodedPath(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::decodedPath();
        }

        /**
         * Get a segment from the URI (1 based index).
         *
         * @param string $index
         * @param mixed $default
         * @return string
         */
        public static function segment($index, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::segment($index, $default);
        }

        /**
         * Get all of the segments for the request path.
         *
         * @return array
         */
        public static function segments(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::segments();
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Determine if the current request URI matches a pattern.
         *
         * @param mixed  string
         * @return bool
         */
        public static function is(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::is();
        }

        /**
         * Determine if the request is the result of an AJAX call.
         *
         * @return bool
         */
        public static function ajax(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::ajax();
        }

        /**
         * Determine if the request is over HTTPS.
         *
         * @return bool
         */
        public static function secure(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::secure();
        }

        /**
         * Returns the client IP address.
         *
         * @return string
         */
        public static function ip(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::ip();
        }

        /**
         * Returns the client IP addresses.
         *
         * @return array
         */
        public static function ips(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::ips();
        }

        /**
         * Determine if the request contains a given input item key.
         *
         * @param string|array $key
         * @return bool
         */
        public static function exists($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::exists($key);
        }

        /**
         * Determine if the request contains a non-empty value for an input item.
         *
         * @param string|array $key
         * @return bool
         */
        public static function has($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::has($key);
        }

        /**
         * Get all of the input and files for the request.
         *
         * @return array
         */
        public static function all(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::all();
        }

        /**
         * Retrieve an input item from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function input($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::input($key, $default);
        }

        /**
         * Get a subset of the items from the input data.
         *
         * @param array $keys
         * @return array
         */
        public static function only($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::only($keys);
        }

        /**
         * Get all of the input except for a specified array of items.
         *
         * @param array $keys
         * @return array
         */
        public static function except($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::except($keys);
        }

        /**
         * Retrieve a query string item from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string|array
         */
        public static function query($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::query($key, $default);
        }

        /**
         * Determine if a cookie is set on the request.
         *
         * @param string $key
         * @return bool
         */
        public static function hasCookie($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasCookie($key);
        }

        /**
         * Retrieve a cookie from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function cookie($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::cookie($key, $default);
        }

        /**
         * Retrieve a file from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return \Symfony\Component\HttpFoundation\File\UploadedFile|array
         */
        public static function file($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::file($key, $default);
        }

        /**
         * Determine if the uploaded data contains a file.
         *
         * @param string $key
         * @return bool
         */
        public static function hasFile($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasFile($key);
        }

        /**
         * Retrieve a header from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function header($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::header($key, $default);
        }

        /**
         * Retrieve a server variable from the request.
         *
         * @param string $key
         * @param mixed $default
         * @return string
         */
        public static function server($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::server($key, $default);
        }

        /**
         * Retrieve an old input item.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function old($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::old($key, $default);
        }

        /**
         * Flash the input for the current request to the session.
         *
         * @param string $filter
         * @param array $keys
         * @return void
         */
        public static function flash($filter = null, $keys = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flash($filter, $keys);
        }

        /**
         * Flash only some of the input to the session.
         *
         * @param mixed  string
         * @return void
         */
        public static function flashOnly($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flashOnly($keys);
        }

        /**
         * Flash only some of the input to the session.
         *
         * @param mixed  string
         * @return void
         */
        public static function flashExcept($keys){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flashExcept($keys);
        }

        /**
         * Flush all of the old input from the session.
         *
         * @return void
         */
        public static function flush(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::flush();
        }

        /**
         * Merge new input into the current request's input array.
         *
         * @param array $input
         * @return void
         */
        public static function merge($input){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::merge($input);
        }

        /**
         * Replace the input for the current request.
         *
         * @param array $input
         * @return void
         */
        public static function replace($input){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Http\Request::replace($input);
        }

        /**
         * Get the JSON payload for the request.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function json($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::json($key, $default);
        }

        /**
         * Determine if the request is sending JSON.
         *
         * @return bool
         */
        public static function isJson(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isJson();
        }

        /**
         * Determine if the current request is asking for JSON in return.
         *
         * @return bool
         */
        public static function wantsJson(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::wantsJson();
        }

        /**
         * Get the data format expected in the response.
         *
         * @param string $default
         * @return string
         */
        public static function format($default = 'html'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::format($default);
        }

        /**
         * Create an Illuminate request from a Symfony instance.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return \Illuminate\Http\Request
         */
        public static function createFromBase($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::createFromBase($request);
        }

        /**
         * Get the session associated with the request.
         *
         * @return \Illuminate\Session\Store
         * @throws \RuntimeException
         */
        public static function session(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::session();
        }

        /**
         * Sets the parameters for this request.
         *
         * This method also re-initializes all properties.
         *
         * @param array $query The GET parameters
         * @param array $request The POST parameters
         * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
         * @param array $cookies The COOKIE parameters
         * @param array $files The FILES parameters
         * @param array $server The SERVER parameters
         * @param string $content The raw body data
         * @api
         */
        public static function initialize($query = array(), $request = array(), $attributes = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        }

        /**
         * Creates a new request with values from PHP's super globals.
         *
         * @return \Symfony\Component\HttpFoundation\Request A new request
         * @api
         */
        public static function createFromGlobals(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::createFromGlobals();
        }

        /**
         * Creates a Request based on a given URI and configuration.
         *
         * The information contained in the URI always take precedence
         * over the other information (server and parameters).
         *
         * @param string $uri The URI
         * @param string $method The HTTP method
         * @param array $parameters The query (GET) or request (POST) parameters
         * @param array $cookies The request cookies ($_COOKIE)
         * @param array $files The request files ($_FILES)
         * @param array $server The server parameters ($_SERVER)
         * @param string $content The raw body data
         * @return \Symfony\Component\HttpFoundation\Request A Request instance
         * @api
         */
        public static function create($uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::create($uri, $method, $parameters, $cookies, $files, $server, $content);
        }

        /**
         * Sets a callable able to create a Request instance.
         *
         * This is mainly useful when you need to override the Request class
         * to keep BC with an existing system. It should not be used for any
         * other purpose.
         *
         * @param callable|null $callable A PHP callable
         */
        public static function setFactory($callable){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setFactory($callable);
        }

        /**
         * Clones a request and overrides some of its parameters.
         *
         * @param array $query The GET parameters
         * @param array $request The POST parameters
         * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
         * @param array $cookies The COOKIE parameters
         * @param array $files The FILES parameters
         * @param array $server The SERVER parameters
         * @return \Symfony\Component\HttpFoundation\Request The duplicated request
         * @api
         */
        public static function duplicate($query = null, $request = null, $attributes = null, $cookies = null, $files = null, $server = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::duplicate($query, $request, $attributes, $cookies, $files, $server);
        }

        /**
         * Overrides the PHP global variables according to this request instance.
         *
         * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
         * $_FILES is never overridden, see rfc1867
         *
         * @api
         */
        public static function overrideGlobals(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::overrideGlobals();
        }

        /**
         * Sets a list of trusted proxies.
         *
         * You should only list the reverse proxies that you manage directly.
         *
         * @param array $proxies A list of trusted proxies
         * @api
         */
        public static function setTrustedProxies($proxies){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setTrustedProxies($proxies);
        }

        /**
         * Gets the list of trusted proxies.
         *
         * @return array An array of trusted proxies.
         */
        public static function getTrustedProxies(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getTrustedProxies();
        }

        /**
         * Sets a list of trusted host patterns.
         *
         * You should only list the hosts you manage using regexs.
         *
         * @param array $hostPatterns A list of trusted host patterns
         */
        public static function setTrustedHosts($hostPatterns){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setTrustedHosts($hostPatterns);
        }

        /**
         * Gets the list of trusted host patterns.
         *
         * @return array An array of trusted host patterns.
         */
        public static function getTrustedHosts(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getTrustedHosts();
        }

        /**
         * Sets the name for trusted headers.
         *
         * The following header keys are supported:
         *
         *  * Request::HEADER_CLIENT_IP:    defaults to X-Forwarded-For   (see getClientIp())
         *  * Request::HEADER_CLIENT_HOST:  defaults to X-Forwarded-Host  (see getClientHost())
         *  * Request::HEADER_CLIENT_PORT:  defaults to X-Forwarded-Port  (see getClientPort())
         *  * Request::HEADER_CLIENT_PROTO: defaults to X-Forwarded-Proto (see getScheme() and isSecure())
         *
         * Setting an empty value allows to disable the trusted header for the given key.
         *
         * @param string $key The header key
         * @param string $value The header name
         * @throws \InvalidArgumentException
         */
        public static function setTrustedHeaderName($key, $value){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setTrustedHeaderName($key, $value);
        }

        /**
         * Gets the trusted proxy header name.
         *
         * @param string $key The header key
         * @return string The header name
         * @throws \InvalidArgumentException
         */
        public static function getTrustedHeaderName($key){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getTrustedHeaderName($key);
        }

        /**
         * Normalizes a query string.
         *
         * It builds a normalized query string, where keys/value pairs are alphabetized,
         * have consistent escaping and unneeded delimiters are removed.
         *
         * @param string $qs Query string
         * @return string A normalized query string for the Request
         */
        public static function normalizeQueryString($qs){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::normalizeQueryString($qs);
        }

        /**
         * Enables support for the _method request parameter to determine the intended HTTP method.
         *
         * Be warned that enabling this feature might lead to CSRF issues in your code.
         * Check that you are using CSRF tokens when required.
         * If the HTTP method parameter override is enabled, an html-form with method "POST" can be altered
         * and used to send a "PUT" or "DELETE" request via the _method request parameter.
         * If these methods are not protected against CSRF, this presents a possible vulnerability.
         *
         * The HTTP method can only be overridden when the real HTTP method is POST.
         */
        public static function enableHttpMethodParameterOverride(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::enableHttpMethodParameterOverride();
        }

        /**
         * Checks whether support for the _method request parameter is enabled.
         *
         * @return bool True when the _method request parameter is enabled, false otherwise
         */
        public static function getHttpMethodParameterOverride(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getHttpMethodParameterOverride();
        }

        /**
         * Gets a "parameter" value.
         *
         * This method is mainly useful for libraries that want to provide some flexibility.
         *
         * Order of precedence: GET, PATH, POST
         *
         * Avoid using this method in controllers:
         *
         *  * slow
         *  * prefer to get from a "named" source
         *
         * It is better to explicitly get request parameters from the appropriate
         * public property instead (query, attributes, request).
         *
         * @param string $key the key
         * @param mixed $default the default value
         * @param bool $deep is parameter deep in multidimensional array
         * @return mixed
         */
        public static function get($key, $default = null, $deep = false){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::get($key, $default, $deep);
        }

        /**
         * Gets the Session.
         *
         * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|null The session
         * @api
         */
        public static function getSession(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getSession();
        }

        /**
         * Whether the request contains a Session which was started in one of the
         * previous requests.
         *
         * @return bool
         * @api
         */
        public static function hasPreviousSession(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasPreviousSession();
        }

        /**
         * Whether the request contains a Session object.
         *
         * This method does not give any information about the state of the session object,
         * like whether the session is started or not. It is just a way to check if this Request
         * is associated with a Session instance.
         *
         * @return bool true when the Request contains a Session object, false otherwise
         * @api
         */
        public static function hasSession(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::hasSession();
        }

        /**
         * Sets the Session.
         *
         * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session The Session
         * @api
         */
        public static function setSession($session){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setSession($session);
        }

        /**
         * Returns the client IP addresses.
         *
         * In the returned array the most trusted IP address is first, and the
         * least trusted one last. The "real" client IP address is the last one,
         * but this is also the least trusted one. Trusted proxies are stripped.
         *
         * Use this method carefully; you should use getClientIp() instead.
         *
         * @return array The client IP addresses
         * @see getClientIp()
         */
        public static function getClientIps(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getClientIps();
        }

        /**
         * Returns the client IP address.
         *
         * This method can read the client IP address from the "X-Forwarded-For" header
         * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
         * header value is a comma+space separated list of IP addresses, the left-most
         * being the original client, and each successive proxy that passed the request
         * adding the IP address where it received the request from.
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-For",
         * ("Client-Ip" for instance), configure it via "setTrustedHeaderName()" with
         * the "client-ip" key.
         *
         * @return string The client IP address
         * @see getClientIps()
         * @see http://en.wikipedia.org/wiki/X-Forwarded-For
         * @api
         */
        public static function getClientIp(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getClientIp();
        }

        /**
         * Returns current script name.
         *
         * @return string
         * @api
         */
        public static function getScriptName(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getScriptName();
        }

        /**
         * Returns the path being requested relative to the executed script.
         *
         * The path info always starts with a /.
         *
         * Suppose this request is instantiated from /mysite on localhost:
         *
         *  * http://localhost/mysite              returns an empty string
         *  * http://localhost/mysite/about        returns '/about'
         *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
         *  * http://localhost/mysite/about?var=1  returns '/about'
         *
         * @return string The raw path (i.e. not urldecoded)
         * @api
         */
        public static function getPathInfo(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPathInfo();
        }

        /**
         * Returns the root path from which this request is executed.
         *
         * Suppose that an index.php file instantiates this request object:
         *
         *  * http://localhost/index.php         returns an empty string
         *  * http://localhost/index.php/page    returns an empty string
         *  * http://localhost/web/index.php     returns '/web'
         *  * http://localhost/we%20b/index.php  returns '/we%20b'
         *
         * @return string The raw path (i.e. not urldecoded)
         * @api
         */
        public static function getBasePath(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getBasePath();
        }

        /**
         * Returns the root URL from which this request is executed.
         *
         * The base URL never ends with a /.
         *
         * This is similar to getBasePath(), except that it also includes the
         * script filename (e.g. index.php) if one exists.
         *
         * @return string The raw URL (i.e. not urldecoded)
         * @api
         */
        public static function getBaseUrl(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getBaseUrl();
        }

        /**
         * Gets the request's scheme.
         *
         * @return string
         * @api
         */
        public static function getScheme(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getScheme();
        }

        /**
         * Returns the port on which the request is made.
         *
         * This method can read the client port from the "X-Forwarded-Port" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Port" header must contain the client port.
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-Port",
         * configure it via "setTrustedHeaderName()" with the "client-port" key.
         *
         * @return string
         * @api
         */
        public static function getPort(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPort();
        }

        /**
         * Returns the user.
         *
         * @return string|null
         */
        public static function getUser(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUser();
        }

        /**
         * Returns the password.
         *
         * @return string|null
         */
        public static function getPassword(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPassword();
        }

        /**
         * Gets the user info.
         *
         * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
         */
        public static function getUserInfo(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUserInfo();
        }

        /**
         * Returns the HTTP host being requested.
         *
         * The port name will be appended to the host if it's non-standard.
         *
         * @return string
         * @api
         */
        public static function getHttpHost(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getHttpHost();
        }

        /**
         * Returns the requested URI (path and query string).
         *
         * @return string The raw URI (i.e. not URI decoded)
         * @api
         */
        public static function getRequestUri(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getRequestUri();
        }

        /**
         * Gets the scheme and HTTP host.
         *
         * If the URL was called with basic authentication, the user
         * and the password are not added to the generated string.
         *
         * @return string The scheme and HTTP host
         */
        public static function getSchemeAndHttpHost(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getSchemeAndHttpHost();
        }

        /**
         * Generates a normalized URI (URL) for the Request.
         *
         * @return string A normalized URI (URL) for the Request
         * @see getQueryString()
         * @api
         */
        public static function getUri(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUri();
        }

        /**
         * Generates a normalized URI for the given path.
         *
         * @param string $path A path to use instead of the current one
         * @return string The normalized URI for the path
         * @api
         */
        public static function getUriForPath($path){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getUriForPath($path);
        }

        /**
         * Generates the normalized query string for the Request.
         *
         * It builds a normalized query string, where keys/value pairs are alphabetized
         * and have consistent escaping.
         *
         * @return string|null A normalized query string for the Request
         * @api
         */
        public static function getQueryString(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getQueryString();
        }

        /**
         * Checks whether the request is secure or not.
         *
         * This method can read the client port from the "X-Forwarded-Proto" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-Proto"
         * ("SSL_HTTPS" for instance), configure it via "setTrustedHeaderName()" with
         * the "client-proto" key.
         *
         * @return bool
         * @api
         */
        public static function isSecure(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isSecure();
        }

        /**
         * Returns the host name.
         *
         * This method can read the client port from the "X-Forwarded-Host" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Host" header must contain the client host name.
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-Host",
         * configure it via "setTrustedHeaderName()" with the "client-host" key.
         *
         * @return string
         * @throws \UnexpectedValueException when the host name is invalid
         * @api
         */
        public static function getHost(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getHost();
        }

        /**
         * Sets the request method.
         *
         * @param string $method
         * @api
         */
        public static function setMethod($method){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setMethod($method);
        }

        /**
         * Gets the request "intended" method.
         *
         * If the X-HTTP-Method-Override header is set, and if the method is a POST,
         * then it is used to determine the "real" intended HTTP method.
         *
         * The _method request parameter can also be used to determine the HTTP method,
         * but only if enableHttpMethodParameterOverride() has been called.
         *
         * The method is always an uppercased string.
         *
         * @return string The request method
         * @api
         * @see getRealMethod()
         */
        public static function getMethod(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getMethod();
        }

        /**
         * Gets the "real" request method.
         *
         * @return string The request method
         * @see getMethod()
         */
        public static function getRealMethod(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getRealMethod();
        }

        /**
         * Gets the mime type associated with the format.
         *
         * @param string $format The format
         * @return string The associated mime type (null if not found)
         * @api
         */
        public static function getMimeType($format){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getMimeType($format);
        }

        /**
         * Gets the format associated with the mime type.
         *
         * @param string $mimeType The associated mime type
         * @return string|null The format (null if not found)
         * @api
         */
        public static function getFormat($mimeType){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getFormat($mimeType);
        }

        /**
         * Associates a format with mime types.
         *
         * @param string $format The format
         * @param string|array $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
         * @api
         */
        public static function setFormat($format, $mimeTypes){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setFormat($format, $mimeTypes);
        }

        /**
         * Gets the request format.
         *
         * Here is the process to determine the format:
         *
         *  * format defined by the user (with setRequestFormat())
         *  * _format request parameter
         *  * $default
         *
         * @param string $default The default format
         * @return string The request format
         * @api
         */
        public static function getRequestFormat($default = 'html'){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getRequestFormat($default);
        }

        /**
         * Sets the request format.
         *
         * @param string $format The request format.
         * @api
         */
        public static function setRequestFormat($format){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setRequestFormat($format);
        }

        /**
         * Gets the format associated with the request.
         *
         * @return string|null The format (null if no content type is present)
         * @api
         */
        public static function getContentType(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getContentType();
        }

        /**
         * Sets the default locale.
         *
         * @param string $locale
         * @api
         */
        public static function setDefaultLocale($locale){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setDefaultLocale($locale);
        }

        /**
         * Get the default locale.
         *
         * @return string
         */
        public static function getDefaultLocale(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getDefaultLocale();
        }

        /**
         * Sets the locale.
         *
         * @param string $locale
         * @api
         */
        public static function setLocale($locale){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Http\Request::setLocale($locale);
        }

        /**
         * Get the locale.
         *
         * @return string
         */
        public static function getLocale(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getLocale();
        }

        /**
         * Checks if the request method is of specified type.
         *
         * @param string $method Uppercase request method (GET, POST etc).
         * @return bool
         */
        public static function isMethod($method){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isMethod($method);
        }

        /**
         * Checks whether the method is safe or not.
         *
         * @return bool
         * @api
         */
        public static function isMethodSafe(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isMethodSafe();
        }

        /**
         * Returns the request body content.
         *
         * @param bool $asResource If true, a resource will be returned
         * @return string|resource The request body content or a resource to read the body stream.
         * @throws \LogicException
         */
        public static function getContent($asResource = false){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getContent($asResource);
        }

        /**
         * Gets the Etags.
         *
         * @return array The entity tags
         */
        public static function getETags(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getETags();
        }

        /**
         * @return bool
         */
        public static function isNoCache(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isNoCache();
        }

        /**
         * Returns the preferred language.
         *
         * @param array $locales An array of ordered available locales
         * @return string|null The preferred locale
         * @api
         */
        public static function getPreferredLanguage($locales = null){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getPreferredLanguage($locales);
        }

        /**
         * Gets a list of languages acceptable by the client browser.
         *
         * @return array Languages ordered in the user browser preferences
         * @api
         */
        public static function getLanguages(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getLanguages();
        }

        /**
         * Gets a list of charsets acceptable by the client browser.
         *
         * @return array List of charsets in preferable order
         * @api
         */
        public static function getCharsets(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getCharsets();
        }

        /**
         * Gets a list of encodings acceptable by the client browser.
         *
         * @return array List of encodings in preferable order
         */
        public static function getEncodings(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getEncodings();
        }

        /**
         * Gets a list of content types acceptable by the client browser.
         *
         * @return array List of content types in preferable order
         * @api
         */
        public static function getAcceptableContentTypes(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::getAcceptableContentTypes();
        }

        /**
         * Returns true if the request is a XMLHttpRequest.
         *
         * It works if your JavaScript library sets an X-Requested-With HTTP header.
         * It is known to work with common JavaScript frameworks:
         *
         * @link http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
         * @return bool true if the request is an XMLHttpRequest, false otherwise
         * @api
         */
        public static function isXmlHttpRequest(){
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Http\Request::isXmlHttpRequest();
        }

    }

    class Response extends \Illuminate\Support\Facades\Response{

    }

    class Route extends \Illuminate\Support\Facades\Route{

        /**
         * Indicate that the routes that are defined in the given callback
         * should be cached.
         *
         * @param string $filename
         * @param \Closure $callback
         * @param int $cacheMinutes
         * @return string
         */
        public static function cache($filename, $callback, $cacheMinutes = 1440){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::cache($filename, $callback, $cacheMinutes);
        }

        /**
         * Clear the cached data for the given routes file.
         *
         * @param string $filename
         */
        public static function clearCache($filename){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \MaartenStaa\Routing\Router::clearCache($filename);
        }

        /**
         * Determine if the action is routing to a controller.
         *
         * @param array $action
         * @return bool
         */
        public static function routingToController($action){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::routingToController($action);
        }

        /**
         * Replace the string action in the given array with a Closure to call.
         *
         * @param array $action
         * @return array
         */
        public static function makeControllerActionClosure($action){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::makeControllerActionClosure($action);
        }

        /**
         * Register a new GET route with the router.
         *
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function get($uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::get($uri, $action);
        }

        /**
         * Register a new POST route with the router.
         *
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function post($uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::post($uri, $action);
        }

        /**
         * Register a new PUT route with the router.
         *
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function put($uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::put($uri, $action);
        }

        /**
         * Register a new PATCH route with the router.
         *
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function patch($uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::patch($uri, $action);
        }

        /**
         * Register a new DELETE route with the router.
         *
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function delete($uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::delete($uri, $action);
        }

        /**
         * Register a new OPTIONS route with the router.
         *
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function options($uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::options($uri, $action);
        }

        /**
         * Register a new route responding to all verbs.
         *
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function any($uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::any($uri, $action);
        }

        /**
         * Register a new route with the given verbs.
         *
         * @param array|string $methods
         * @param string $uri
         * @param \Closure|array|string $action
         * @return \Illuminate\Routing\Route
         */
        public static function match($methods, $uri, $action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::match($methods, $uri, $action);
        }

        /**
         * Register an array of controllers with wildcard routing.
         *
         * @param array $controllers
         * @return void
         */
        public static function controllers($controllers){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::controllers($controllers);
        }

        /**
         * Route a controller to a URI with wildcard routing.
         *
         * @param string $uri
         * @param string $controller
         * @param array $names
         * @return void
         */
        public static function controller($uri, $controller, $names = array()){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::controller($uri, $controller, $names);
        }

        /**
         * Route a resource to a controller.
         *
         * @param string $name
         * @param string $controller
         * @param array $options
         * @return void
         */
        public static function resource($name, $controller, $options = array()){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::resource($name, $controller, $options);
        }

        /**
         * Get the base resource URI for a given resource.
         *
         * @param string $resource
         * @return string
         */
        public static function getResourceUri($resource){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getResourceUri($resource);
        }

        /**
         * Format a resource wildcard for usage.
         *
         * @param string $value
         * @return string
         */
        public static function getResourceWildcard($value){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getResourceWildcard($value);
        }

        /**
         * Create a route group with shared attributes.
         *
         * @param array $attributes
         * @param \Closure $callback
         * @return void
         */
        public static function group($attributes, $callback){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::group($attributes, $callback);
        }

        /**
         * Merge the given array with the last group stack.
         *
         * @param array $new
         * @return array
         */
        public static function mergeWithLastGroup($new){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::mergeWithLastGroup($new);
        }

        /**
         * Merge the given group attributes.
         *
         * @param array $new
         * @param array $old
         * @return array
         */
        public static function mergeGroup($new, $old){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::mergeGroup($new, $old);
        }

        /**
         * Dispatch the request to the application.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\Response
         */
        public static function dispatch($request){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::dispatch($request);
        }

        /**
         * Dispatch the request to a route and return the response.
         *
         * @param \Illuminate\Http\Request $request
         * @return mixed
         */
        public static function dispatchToRoute($request){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::dispatchToRoute($request);
        }

        /**
         * Register a route matched event listener.
         *
         * @param string|callable $callback
         * @return void
         */
        public static function matched($callback){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::matched($callback);
        }

        /**
         * Register a new "before" filter with the router.
         *
         * @param string|callable $callback
         * @return void
         */
        public static function before($callback){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::before($callback);
        }

        /**
         * Register a new "after" filter with the router.
         *
         * @param string|callable $callback
         * @return void
         */
        public static function after($callback){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::after($callback);
        }

        /**
         * Register a new filter with the router.
         *
         * @param string $name
         * @param string|callable $callback
         * @return void
         */
        public static function filter($name, $callback){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::filter($name, $callback);
        }

        /**
         * Register a pattern-based filter with the router.
         *
         * @param string $pattern
         * @param string $name
         * @param array|null $methods
         * @return void
         */
        public static function when($pattern, $name, $methods = null){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::when($pattern, $name, $methods);
        }

        /**
         * Register a regular expression based filter with the router.
         *
         * @param string $pattern
         * @param string $name
         * @param array|null $methods
         * @return void
         */
        public static function whenRegex($pattern, $name, $methods = null){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::whenRegex($pattern, $name, $methods);
        }

        /**
         * Register a model binder for a wildcard.
         *
         * @param string $key
         * @param string $class
         * @param \Closure $callback
         * @return void
         * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        public static function model($key, $class, $callback = null){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::model($key, $class, $callback);
        }

        /**
         * Add a new route parameter binder.
         *
         * @param string $key
         * @param string|callable $binder
         * @return void
         */
        public static function bind($key, $binder){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::bind($key, $binder);
        }

        /**
         * Create a class based binding using the IoC container.
         *
         * @param string $binding
         * @return \Closure
         */
        public static function createClassBinding($binding){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::createClassBinding($binding);
        }

        /**
         * Set a global where pattern on all routes
         *
         * @param string $key
         * @param string $pattern
         * @return void
         */
        public static function pattern($key, $pattern){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::pattern($key, $pattern);
        }

        /**
         * Set a group of global where patterns on all routes
         *
         * @param array $patterns
         * @return void
         */
        public static function patterns($patterns){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::patterns($patterns);
        }

        /**
         * Call the given route's before filters.
         *
         * @param \Illuminate\Routing\Route $route
         * @param \Illuminate\Http\Request $request
         * @return mixed
         */
        public static function callRouteBefore($route, $request){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::callRouteBefore($route, $request);
        }

        /**
         * Find the patterned filters matching a request.
         *
         * @param \Illuminate\Http\Request $request
         * @return array
         */
        public static function findPatternFilters($request){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::findPatternFilters($request);
        }

        /**
         * Call the given route's before filters.
         *
         * @param \Illuminate\Routing\Route $route
         * @param \Illuminate\Http\Request $request
         * @param \Illuminate\Http\Response $response
         * @return mixed
         */
        public static function callRouteAfter($route, $request, $response){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::callRouteAfter($route, $request, $response);
        }

        /**
         * Call the given route filter.
         *
         * @param string $filter
         * @param array $parameters
         * @param \Illuminate\Routing\Route $route
         * @param \Illuminate\Http\Request $request
         * @param \Illuminate\Http\Response|null $response
         * @return mixed
         */
        public static function callRouteFilter($filter, $parameters, $route, $request, $response = null){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::callRouteFilter($filter, $parameters, $route, $request, $response);
        }

        /**
         * Run a callback with filters disable on the router.
         *
         * @param callable $callback
         * @return void
         */
        public static function withoutFilters($callback){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::withoutFilters($callback);
        }

        /**
         * Enable route filtering on the router.
         *
         * @return void
         */
        public static function enableFilters(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::enableFilters();
        }

        /**
         * Disable route filtering on the router.
         *
         * @return void
         */
        public static function disableFilters(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::disableFilters();
        }

        /**
         * Get a route parameter for the current route.
         *
         * @param string $key
         * @param string $default
         * @return mixed
         */
        public static function input($key, $default = null){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::input($key, $default);
        }

        /**
         * Get the currently dispatched route instance.
         *
         * @return \Illuminate\Routing\Route
         */
        public static function getCurrentRoute(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getCurrentRoute();
        }

        /**
         * Get the currently dispatched route instance.
         *
         * @return \Illuminate\Routing\Route
         */
        public static function current(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::current();
        }

        /**
         * Check if a route with the given name exists.
         *
         * @param string $name
         * @return bool
         */
        public static function has($name){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::has($name);
        }

        /**
         * Get the current route name.
         *
         * @return string|null
         */
        public static function currentRouteName(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::currentRouteName();
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Alias for the "currentRouteNamed" method.
         *
         * @param mixed  string
         * @return bool
         */
        public static function is(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::is();
        }

        /**
         * Determine if the current route matches a given name.
         *
         * @param string $name
         * @return bool
         */
        public static function currentRouteNamed($name){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::currentRouteNamed($name);
        }

        /**
         * Get the current route action.
         *
         * @return string|null
         */
        public static function currentRouteAction(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::currentRouteAction();
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * Alias for the "currentRouteUses" method.
         *
         * @param mixed  string
         * @return bool
         */
        public static function uses(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::uses();
        }

        /**
         * Determine if the current route action matches a given action.
         *
         * @param string $action
         * @return bool
         */
        public static function currentRouteUses($action){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::currentRouteUses($action);
        }

        /**
         * Get the request currently being dispatched.
         *
         * @return \Illuminate\Http\Request
         */
        public static function getCurrentRequest(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getCurrentRequest();
        }

        /**
         * Get the underlying route collection.
         *
         * @return \Illuminate\Routing\RouteCollection
         */
        public static function getRoutes(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getRoutes();
        }

        /**
         * Get the controller dispatcher instance.
         *
         * @return \Illuminate\Routing\ControllerDispatcher
         */
        public static function getControllerDispatcher(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getControllerDispatcher();
        }

        /**
         * Set the controller dispatcher instance.
         *
         * @param \Illuminate\Routing\ControllerDispatcher $dispatcher
         * @return void
         */
        public static function setControllerDispatcher($dispatcher){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \MaartenStaa\Routing\Router::setControllerDispatcher($dispatcher);
        }

        /**
         * Get a controller inspector instance.
         *
         * @return \Illuminate\Routing\ControllerInspector
         */
        public static function getInspector(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getInspector();
        }

        /**
         * Get the global "where" patterns.
         *
         * @return array
         */
        public static function getPatterns(){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::getPatterns();
        }

        /**
         * Get the response for a given request.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @param int $type
         * @param bool $catch
         * @return \Illuminate\Http\Response
         */
        public static function handle($request, $type = 1, $catch = true){
            //Method inherited from \Illuminate\Routing\Router
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \MaartenStaa\Routing\Router::handle($request, $type, $catch);
        }

    }

    class Schema extends \Illuminate\Support\Facades\Schema{

        /**
         * Determine if the given table exists.
         *
         * @param string $table
         * @return bool
         */
        public static function hasTable($table){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::hasTable($table);
        }

        /**
         * Determine if the given table has a given column.
         *
         * @param string $table
         * @param string $column
         * @return bool
         */
        public static function hasColumn($table, $column){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::hasColumn($table, $column);
        }

        /**
         * Get the column listing for a given table.
         *
         * @param string $table
         * @return array
         */
        public static function getColumnListing($table){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::getColumnListing($table);
        }

        /**
         * Modify a table on the schema.
         *
         * @param string $table
         * @param \Closure $callback
         * @return \Illuminate\Database\Schema\Blueprint
         */
        public static function table($table, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::table($table, $callback);
        }

        /**
         * Create a new table on the schema.
         *
         * @param string $table
         * @param \Closure $callback
         * @return \Illuminate\Database\Schema\Blueprint
         */
        public static function create($table, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::create($table, $callback);
        }

        /**
         * Drop a table from the schema.
         *
         * @param string $table
         * @return \Illuminate\Database\Schema\Blueprint
         */
        public static function drop($table){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::drop($table);
        }

        /**
         * Drop a table from the schema if it exists.
         *
         * @param string $table
         * @return \Illuminate\Database\Schema\Blueprint
         */
        public static function dropIfExists($table){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::dropIfExists($table);
        }

        /**
         * Rename a table on the schema.
         *
         * @param string $from
         * @param string $to
         * @return \Illuminate\Database\Schema\Blueprint
         */
        public static function rename($from, $to){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::rename($from, $to);
        }

        /**
         * Get the database connection instance.
         *
         * @return \Illuminate\Database\Connection
         */
        public static function getConnection(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::getConnection();
        }

        /**
         * Set the database connection instance.
         *
         * @param \Illuminate\Database\Connection
         * @return $this
         */
        public static function setConnection($connection){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Database\Schema\Builder::setConnection($connection);
        }

        /**
         * Set the Schema Blueprint resolver callback.
         *
         * @param \Closure $resolver
         * @return void
         */
        public static function blueprintResolver($resolver){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Database\Schema\Builder::blueprintResolver($resolver);
        }

    }

    class Seeder extends \Illuminate\Database\Seeder{

    }

    class Session extends \Illuminate\Support\Facades\Session{

        /**
         * Get the session configuration.
         *
         * @return array
         */
        public static function getSessionConfig(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\SessionManager::getSessionConfig();
        }

        /**
         * Get the default session driver name.
         *
         * @return string
         */
        public static function getDefaultDriver(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\SessionManager::getDefaultDriver();
        }

        /**
         * Set the default session driver name.
         *
         * @param string $name
         * @return void
         */
        public static function setDefaultDriver($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\SessionManager::setDefaultDriver($name);
        }

        /**
         * Get a driver instance.
         *
         * @param string $driver
         * @return mixed
         */
        public static function driver($driver = null){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\SessionManager::driver($driver);
        }

        /**
         * Register a custom driver creator Closure.
         *
         * @param string $driver
         * @param \Closure $callback
         * @return $this
         */
        public static function extend($driver, $callback){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\SessionManager::extend($driver, $callback);
        }

        /**
         * Get all of the created "drivers".
         *
         * @return array
         */
        public static function getDrivers(){
            //Method inherited from \Illuminate\Support\Manager
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\SessionManager::getDrivers();
        }

        /**
         * Starts the session storage.
         *
         * @return bool True if session started.
         * @throws \RuntimeException If session fails to start.
         * @api
         */
        public static function start(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::start();
        }

        /**
         * Returns the session ID.
         *
         * @return string The session ID.
         * @api
         */
        public static function getId(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getId();
        }

        /**
         * Sets the session ID.
         *
         * @param string $id
         * @api
         */
        public static function setId($id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Session\Store::setId($id);
        }

        /**
         * Determine if this is a valid session ID.
         *
         * @param string $id
         * @return bool
         */
        public static function isValidId($id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::isValidId($id);
        }

        /**
         * Returns the session name.
         *
         * @return mixed The session name.
         * @api
         */
        public static function getName(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getName();
        }

        /**
         * Sets the session name.
         *
         * @param string $name
         * @api
         */
        public static function setName($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Session\Store::setName($name);
        }

        /**
         * Invalidates the current session.
         *
         * Clears all session attributes and flashes and regenerates the
         * session and deletes the old session from persistence.
         *
         * @param int $lifetime Sets the cookie lifetime for the session cookie. A null value
         *                      will leave the system settings unchanged, 0 sets the cookie
         *                      to expire with browser session. Time is in seconds, and is
         *                      not a Unix timestamp.
         * @return bool True if session invalidated, false if error.
         * @api
         */
        public static function invalidate($lifetime = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::invalidate($lifetime);
        }

        /**
         * Migrates the current session to a new session id while maintaining all
         * session attributes.
         *
         * @param bool $destroy Whether to delete the old session or leave it to garbage collection.
         * @param int $lifetime Sets the cookie lifetime for the session cookie. A null value
         *                       will leave the system settings unchanged, 0 sets the cookie
         *                       to expire with browser session. Time is in seconds, and is
         *                       not a Unix timestamp.
         * @return bool True if session migrated, false if error.
         * @api
         */
        public static function migrate($destroy = false, $lifetime = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::migrate($destroy, $lifetime);
        }

        /**
         * Generate a new session identifier.
         *
         * @param bool $destroy
         * @return bool
         */
        public static function regenerate($destroy = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::regenerate($destroy);
        }

        /**
         * Force the session to be saved and closed.
         *
         * This method is generally not required for real sessions as
         * the session will be automatically saved at the end of
         * code execution.
         */
        public static function save(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Session\Store::save();
        }

        /**
         * Age the flash data for the session.
         *
         * @return void
         */
        public static function ageFlashData(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::ageFlashData();
        }

        /**
         * Checks if an attribute is defined.
         *
         * @param string $name The attribute name
         * @return bool true if the attribute is defined, false otherwise
         * @api
         */
        public static function has($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::has($name);
        }

        /**
         * Returns an attribute.
         *
         * @param string $name The attribute name
         * @param mixed $default The default value if not found.
         * @return mixed
         * @api
         */
        public static function get($name, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::get($name, $default);
        }

        /**
         * Get the value of a given key and then forget it.
         *
         * @param string $key
         * @param string $default
         * @return mixed
         */
        public static function pull($key, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::pull($key, $default);
        }

        /**
         * Determine if the session contains old input.
         *
         * @param string $key
         * @return bool
         */
        public static function hasOldInput($key = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::hasOldInput($key);
        }

        /**
         * Get the requested item from the flashed input array.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function getOldInput($key = null, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getOldInput($key, $default);
        }

        /**
         * Sets an attribute.
         *
         * @param string $name
         * @param mixed $value
         * @api
         */
        public static function set($name, $value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Session\Store::set($name, $value);
        }

        /**
         * Put a key / value pair or array of key / value pairs in the session.
         *
         * @param string|array $key
         * @param mixed|null $value
         * @return void
         */
        public static function put($key, $value = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::put($key, $value);
        }

        /**
         * Push a value onto a session array.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function push($key, $value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::push($key, $value);
        }

        /**
         * Flash a key / value pair to the session.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function flash($key, $value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::flash($key, $value);
        }

        /**
         * Flash an input array to the session.
         *
         * @param array $value
         * @return void
         */
        public static function flashInput($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::flashInput($value);
        }

        /**
         * Reflash all of the session flash data.
         *
         * @return void
         */
        public static function reflash(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::reflash();
        }

        /**
         * Reflash a subset of the current flash data.
         *
         * @param array|mixed $keys
         * @return void
         */
        public static function keep($keys = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::keep($keys);
        }

        /**
         * Returns attributes.
         *
         * @return array Attributes
         * @api
         */
        public static function all(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::all();
        }

        /**
         * Sets attributes.
         *
         * @param array $attributes Attributes
         */
        public static function replace($attributes){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Session\Store::replace($attributes);
        }

        /**
         * Removes an attribute.
         *
         * @param string $name
         * @return mixed The removed value or null when it does not exist
         * @api
         */
        public static function remove($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::remove($name);
        }

        /**
         * Remove an item from the session.
         *
         * @param string $key
         * @return void
         */
        public static function forget($key){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::forget($key);
        }

        /**
         * Clears all attributes.
         *
         * @api
         */
        public static function clear(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Session\Store::clear();
        }

        /**
         * Remove all of the items from the session.
         *
         * @return void
         */
        public static function flush(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::flush();
        }

        /**
         * Checks if the session was started.
         *
         * @return bool
         */
        public static function isStarted(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::isStarted();
        }

        /**
         * Registers a SessionBagInterface with the session.
         *
         * @param \Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag
         */
        public static function registerBag($bag){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Illuminate\Session\Store::registerBag($bag);
        }

        /**
         * Gets a bag instance by name.
         *
         * @param string $name
         * @return \Symfony\Component\HttpFoundation\Session\SessionBagInterface
         */
        public static function getBag($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getBag($name);
        }

        /**
         * Gets session meta.
         *
         * @return \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
         */
        public static function getMetadataBag(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getMetadataBag();
        }

        /**
         * Get the raw bag data array for a given bag.
         *
         * @param string $name
         * @return array
         */
        public static function getBagData($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getBagData($name);
        }

        /**
         * Get the CSRF token value.
         *
         * @return string
         */
        public static function token(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::token();
        }

        /**
         * Get the CSRF token value.
         *
         * @return string
         */
        public static function getToken(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getToken();
        }

        /**
         * Regenerate the CSRF token value.
         *
         * @return void
         */
        public static function regenerateToken(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::regenerateToken();
        }

        /**
         * Set the existence of the session on the handler if applicable.
         *
         * @param bool $value
         * @return void
         */
        public static function setExists($value){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::setExists($value);
        }

        /**
         * Get the underlying session handler implementation.
         *
         * @return \SessionHandlerInterface
         */
        public static function getHandler(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::getHandler();
        }

        /**
         * Determine if the session handler needs a request.
         *
         * @return bool
         */
        public static function handlerNeedsRequest(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Session\Store::handlerNeedsRequest();
        }

        /**
         * Set the request on the handler instance.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return void
         */
        public static function setRequestOnHandler($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Session\Store::setRequestOnHandler($request);
        }

    }

    class SSH extends \Illuminate\Support\Facades\SSH{

        /**
         * Get a remote connection instance.
         *
         * @param string|array|mixed $name
         * @return \Illuminate\Remote\Connection
         */
        public static function into($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\RemoteManager::into($name);
        }

        /**
         * Get a remote connection instance.
         *
         * @param string|array $name
         * @return \Illuminate\Remote\Connection
         */
        public static function connection($name = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\RemoteManager::connection($name);
        }

        /**
         * Get a connection group instance by name.
         *
         * @param string $name
         * @return \Illuminate\Remote\Connection
         */
        public static function group($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\RemoteManager::group($name);
        }

        /**
         * Resolve a multiple connection instance.
         *
         * @param array $names
         * @return \Illuminate\Remote\MultiConnection
         */
        public static function multiple($names){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\RemoteManager::multiple($names);
        }

        /**
         * Resolve a remote connection instance.
         *
         * @param string $name
         * @return \Illuminate\Remote\Connection
         */
        public static function resolve($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\RemoteManager::resolve($name);
        }

        /**
         * Get the default connection name.
         *
         * @return string
         */
        public static function getDefaultConnection(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\RemoteManager::getDefaultConnection();
        }

        /**
         * Set the default connection name.
         *
         * @param string $name
         * @return void
         */
        public static function setDefaultConnection($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\RemoteManager::setDefaultConnection($name);
        }

        /**
         * Define a set of commands as a task.
         *
         * @param string $task
         * @param string|array $commands
         * @return void
         */
        public static function define($task, $commands){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::define($task, $commands);
        }

        /**
         * Run a task against the connection.
         *
         * @param string $task
         * @param \Closure $callback
         * @return void
         */
        public static function task($task, $callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::task($task, $callback);
        }

        /**
         * Run a set of commands against the connection.
         *
         * @param string|array $commands
         * @param \Closure $callback
         * @return void
         */
        public static function run($commands, $callback = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::run($commands, $callback);
        }

        /**
         * Download the contents of a remote file.
         *
         * @param string $remote
         * @param string $local
         * @return void
         */
        public static function get($remote, $local){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::get($remote, $local);
        }

        /**
         * Get the contents of a remote file.
         *
         * @param string $remote
         * @return string
         */
        public static function getString($remote){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\Connection::getString($remote);
        }

        /**
         * Upload a local file to the server.
         *
         * @param string $local
         * @param string $remote
         * @return void
         */
        public static function put($local, $remote){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::put($local, $remote);
        }

        /**
         * Upload a string to to the given file on the server.
         *
         * @param string $remote
         * @param string $contents
         * @return void
         */
        public static function putString($remote, $contents){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::putString($remote, $contents);
        }

        /**
         * Display the given line using the default output.
         *
         * @param string $line
         * @return void
         */
        public static function display($line){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::display($line);
        }

        /**
         * Get the exit status of the last command.
         *
         * @return int|bool
         */
        public static function status(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\Connection::status();
        }

        /**
         * Get the gateway implementation.
         *
         * @return \Illuminate\Remote\GatewayInterface
         * @throws \RuntimeException
         */
        public static function getGateway(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\Connection::getGateway();
        }

        /**
         * Get the output implementation for the connection.
         *
         * @return \Symfony\Component\Console\Output\OutputInterface
         */
        public static function getOutput(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Remote\Connection::getOutput();
        }

        /**
         * Set the output implementation.
         *
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @return void
         */
        public static function setOutput($output){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Remote\Connection::setOutput($output);
        }

    }

    class Str extends \Illuminate\Support\Str{

    }

    class URL extends \Illuminate\Support\Facades\URL{

        /**
         * Get the full URL for the current request.
         *
         * @return string
         */
        public static function full(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::full();
        }

        /**
         * Get the current URL for the request.
         *
         * @return string
         */
        public static function current(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::current();
        }

        /**
         * Get the URL for the previous request.
         *
         * @return string
         */
        public static function previous(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::previous();
        }

        /**
         * Generate a absolute URL to the given path.
         *
         * @param string $path
         * @param mixed $extra
         * @param bool|null $secure
         * @return string
         */
        public static function to($path, $extra = array(), $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::to($path, $extra, $secure);
        }

        /**
         * Generate a secure, absolute URL to the given path.
         *
         * @param string $path
         * @param array $parameters
         * @return string
         */
        public static function secure($path, $parameters = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::secure($path, $parameters);
        }

        /**
         * Generate a URL to an application asset.
         *
         * @param string $path
         * @param bool|null $secure
         * @return string
         */
        public static function asset($path, $secure = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::asset($path, $secure);
        }

        /**
         * Generate a URL to a secure asset.
         *
         * @param string $path
         * @return string
         */
        public static function secureAsset($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::secureAsset($path);
        }

        /**
         * Force the schema for URLs.
         *
         * @param string $schema
         * @return void
         */
        public static function forceSchema($schema){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Routing\UrlGenerator::forceSchema($schema);
        }

        /**
         * Get the URL to a named route.
         *
         * @param string $name
         * @param mixed $parameters
         * @param bool $absolute
         * @param \Illuminate\Routing\Route $route
         * @return string
         * @throws \InvalidArgumentException
         */
        public static function route($name, $parameters = array(), $absolute = true, $route = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::route($name, $parameters, $absolute, $route);
        }

        /**
         * Get the URL to a controller action.
         *
         * @param string $action
         * @param mixed $parameters
         * @param bool $absolute
         * @return string
         */
        public static function action($action, $parameters = array(), $absolute = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::action($action, $parameters, $absolute);
        }

        /**
         * Set the forced root URL.
         *
         * @param string $root
         * @return void
         */
        public static function forceRootUrl($root){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Routing\UrlGenerator::forceRootUrl($root);
        }

        /**
         * Determine if the given path is a valid URL.
         *
         * @param string $path
         * @return bool
         */
        public static function isValidUrl($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::isValidUrl($path);
        }

        /**
         * Get the request instance.
         *
         * @return \Symfony\Component\HttpFoundation\Request
         */
        public static function getRequest(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Routing\UrlGenerator::getRequest();
        }

        /**
         * Set the current request instance.
         *
         * @param \Illuminate\Http\Request $request
         * @return void
         */
        public static function setRequest($request){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Routing\UrlGenerator::setRequest($request);
        }

    }

    class Validator extends \Illuminate\Support\Facades\Validator{

        /**
         * Create a new Validator instance.
         *
         * @param array $data
         * @param array $rules
         * @param array $messages
         * @param array $customAttributes
         * @return \Illuminate\Validation\Validator
         */
        public static function make($data, $rules, $messages = array(), $customAttributes = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Validation\Factory::make($data, $rules, $messages, $customAttributes);
        }

        /**
         * Register a custom validator extension.
         *
         * @param string $rule
         * @param \Closure|string $extension
         * @param string $message
         * @return void
         */
        public static function extend($rule, $extension, $message = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Validation\Factory::extend($rule, $extension, $message);
        }

        /**
         * Register a custom implicit validator extension.
         *
         * @param string $rule
         * @param \Closure|string $extension
         * @param string $message
         * @return void
         */
        public static function extendImplicit($rule, $extension, $message = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Validation\Factory::extendImplicit($rule, $extension, $message);
        }

        /**
         * Register a custom implicit validator message replacer.
         *
         * @param string $rule
         * @param \Closure|string $replacer
         * @return void
         */
        public static function replacer($rule, $replacer){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Validation\Factory::replacer($rule, $replacer);
        }

        /**
         * Set the Validator instance resolver.
         *
         * @param \Closure $resolver
         * @return void
         */
        public static function resolver($resolver){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Validation\Factory::resolver($resolver);
        }

        /**
         * Get the Translator implementation.
         *
         * @return \Symfony\Component\Translation\TranslatorInterface
         */
        public static function getTranslator(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Validation\Factory::getTranslator();
        }

        /**
         * Get the Presence Verifier implementation.
         *
         * @return \Illuminate\Validation\PresenceVerifierInterface
         */
        public static function getPresenceVerifier(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\Validation\Factory::getPresenceVerifier();
        }

        /**
         * Set the Presence Verifier implementation.
         *
         * @param \Illuminate\Validation\PresenceVerifierInterface $presenceVerifier
         * @return void
         */
        public static function setPresenceVerifier($presenceVerifier){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\Validation\Factory::setPresenceVerifier($presenceVerifier);
        }

    }

    class View extends \Illuminate\Support\Facades\View{

        /**
         * Get the evaluated view contents for the given view.
         *
         * @param string $view
         * @param array $data
         * @param array $mergeData
         * @return \Illuminate\View\View
         */
        public static function make($view, $data = array(), $mergeData = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::make($view, $data, $mergeData);
        }

        /**
         * Get the evaluated view contents for a named view.
         *
         * @param string $view
         * @param mixed $data
         * @return \Illuminate\View\View
         */
        public static function of($view, $data = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::of($view, $data);
        }

        /**
         * Register a named view.
         *
         * @param string $view
         * @param string $name
         * @return void
         */
        public static function name($view, $name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::name($view, $name);
        }

        /**
         * Add an alias for a view.
         *
         * @param string $view
         * @param string $alias
         * @return void
         */
        public static function alias($view, $alias){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::alias($view, $alias);
        }

        /**
         * Determine if a given view exists.
         *
         * @param string $view
         * @return bool
         */
        public static function exists($view){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::exists($view);
        }

        /**
         * Get the rendered contents of a partial from a loop.
         *
         * @param string $view
         * @param array $data
         * @param string $iterator
         * @param string $empty
         * @return string
         */
        public static function renderEach($view, $data, $iterator, $empty = 'raw|'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::renderEach($view, $data, $iterator, $empty);
        }

        /**
         * Get the appropriate view engine for the given path.
         *
         * @param string $path
         * @return \Illuminate\View\Engines\EngineInterface
         * @throws \InvalidArgumentException
         */
        public static function getEngineFromPath($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getEngineFromPath($path);
        }

        /**
         * Add a piece of shared data to the environment.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function share($key, $value = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::share($key, $value);
        }

        /**
         * Register a view creator event.
         *
         * @param array|string $views
         * @param \Closure|string $callback
         * @return array
         */
        public static function creator($views, $callback){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::creator($views, $callback);
        }

        /**
         * Register multiple view composers via an array.
         *
         * @param array $composers
         * @return array
         */
        public static function composers($composers){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::composers($composers);
        }

        /**
         * Register a view composer event.
         *
         * @param array|string $views
         * @param \Closure|string $callback
         * @param int|null $priority
         * @return array
         */
        public static function composer($views, $callback, $priority = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::composer($views, $callback, $priority);
        }

        /**
         * Call the composer for a given view.
         *
         * @param \Illuminate\View\View $view
         * @return void
         */
        public static function callComposer($view){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::callComposer($view);
        }

        /**
         * Call the creator for a given view.
         *
         * @param \Illuminate\View\View $view
         * @return void
         */
        public static function callCreator($view){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::callCreator($view);
        }

        /**
         * Start injecting content into a section.
         *
         * @param string $section
         * @param string $content
         * @return void
         */
        public static function startSection($section, $content = ''){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::startSection($section, $content);
        }

        /**
         * Inject inline content into a section.
         *
         * @param string $section
         * @param string $content
         * @return void
         */
        public static function inject($section, $content){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::inject($section, $content);
        }

        /**
         * Stop injecting content into a section and return its contents.
         *
         * @return string
         */
        public static function yieldSection(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::yieldSection();
        }

        /**
         * Stop injecting content into a section.
         *
         * @param bool $overwrite
         * @return string
         */
        public static function stopSection($overwrite = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::stopSection($overwrite);
        }

        /**
         * Stop injecting content into a section and append it.
         *
         * @return string
         */
        public static function appendSection(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::appendSection();
        }

        /**
         * Get the string contents of a section.
         *
         * @param string $section
         * @param string $default
         * @return string
         */
        public static function yieldContent($section, $default = ''){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::yieldContent($section, $default);
        }

        /**
         * Flush all of the section contents.
         *
         * @return void
         */
        public static function flushSections(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::flushSections();
        }

        /**
         * Flush all of the section contents if done rendering.
         *
         * @return void
         */
        public static function flushSectionsIfDoneRendering(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::flushSectionsIfDoneRendering();
        }

        /**
         * Increment the rendering counter.
         *
         * @return void
         */
        public static function incrementRender(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::incrementRender();
        }

        /**
         * Decrement the rendering counter.
         *
         * @return void
         */
        public static function decrementRender(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::decrementRender();
        }

        /**
         * Check if there are no active render operations.
         *
         * @return bool
         */
        public static function doneRendering(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::doneRendering();
        }

        /**
         * Add a location to the array of view locations.
         *
         * @param string $location
         * @return void
         */
        public static function addLocation($location){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::addLocation($location);
        }

        /**
         * Add a new namespace to the loader.
         *
         * @param string $namespace
         * @param string|array $hints
         * @return void
         */
        public static function addNamespace($namespace, $hints){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::addNamespace($namespace, $hints);
        }

        /**
         * Prepend a new namespace to the loader.
         *
         * @param string $namespace
         * @param string|array $hints
         * @return void
         */
        public static function prependNamespace($namespace, $hints){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::prependNamespace($namespace, $hints);
        }

        /**
         * Register a valid view extension and its engine.
         *
         * @param string $extension
         * @param string $engine
         * @param \Closure $resolver
         * @return void
         */
        public static function addExtension($extension, $engine, $resolver = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::addExtension($extension, $engine, $resolver);
        }

        /**
         * Get the extension to engine bindings.
         *
         * @return array
         */
        public static function getExtensions(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getExtensions();
        }

        /**
         * Get the engine resolver instance.
         *
         * @return \Illuminate\View\Engines\EngineResolver
         */
        public static function getEngineResolver(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getEngineResolver();
        }

        /**
         * Get the view finder instance.
         *
         * @return \Illuminate\View\ViewFinderInterface
         */
        public static function getFinder(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getFinder();
        }

        /**
         * Set the view finder instance.
         *
         * @param \Illuminate\View\ViewFinderInterface $finder
         * @return void
         */
        public static function setFinder($finder){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::setFinder($finder);
        }

        /**
         * Get the event dispatcher instance.
         *
         * @return \Illuminate\Events\Dispatcher
         */
        public static function getDispatcher(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getDispatcher();
        }

        /**
         * Set the event dispatcher instance.
         *
         * @param \Illuminate\Events\Dispatcher
         * @return void
         */
        public static function setDispatcher($events){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::setDispatcher($events);
        }

        /**
         * Get the IoC container instance.
         *
         * @return \Illuminate\Container\Container
         */
        public static function getContainer(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getContainer();
        }

        /**
         * Set the IoC container instance.
         *
         * @param \Illuminate\Container\Container $container
         * @return void
         */
        public static function setContainer($container){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Illuminate\View\Factory::setContainer($container);
        }

        /**
         * Get an item from the shared data.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function shared($key, $default = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::shared($key, $default);
        }

        /**
         * Get all of the shared data for the environment.
         *
         * @return array
         */
        public static function getShared(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getShared();
        }

        /**
         * Get the entire array of sections.
         *
         * @return array
         */
        public static function getSections(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getSections();
        }

        /**
         * Get all of the registered named views in environment.
         *
         * @return array
         */
        public static function getNames(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Illuminate\View\Factory::getNames();
        }

    }

    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    class Confide extends \Zizaco\Confide\Facade{

        /**
         * Returns an object of the model set in auth config.
         *
         * @return mixed
         */
        public static function model(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::model();
        }

        /**
         * Get the currently authenticated user or null.
         *
         * @return \Zizaco\Confide\ConfideUserInterface|null
         */
        public static function user(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::user();
        }

        /**
         * Sets the 'confirmed' field of the user with the matching code to true.
         *
         * @param string $code
         * @return bool Success
         */
        public static function confirm($code){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::confirm($code);
        }

        /**
         * Checks if a user with the given identity (email or username) already
         * exists and retrieve it.
         *
         * @param array $identity Array containing at least 'username' or 'email'.
         * @return \Zizaco\Confide\ConfideUserInterface|null
         */
        public static function getUserByEmailOrUsername($identity){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::getUserByEmailOrUsername($identity);
        }

        /**
         * Attempt to log a user into the application with password and
         * identity field(s), usually email or username.
         *
         * @param array $input Array containing at least 'username' or 'email' and 'password'.
         *                               Optionally the 'remember' boolean.
         * @param bool $mustBeConfirmed If true, the user must have confirmed his email account in order to log-in.
         * @return bool Success.
         */
        public static function logAttempt($input, $mustBeConfirmed = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::logAttempt($input, $mustBeConfirmed);
        }

        /**
         * Asks the loginThrottler service if the given identity has reached the throttle_limit.
         *
         * @param mixed $identity The login identity.
         * @return boolean True if the identity has reached the throttle_limit.
         */
        public static function isThrottled($identity){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::isThrottled($identity);
        }

        /**
         * If an user with the given email exists then generate a token for password
         * change and saves it in the 'password_reminders' table with the email
         * of the user.
         *
         * @param string $email
         * @return string $token
         */
        public static function forgotPassword($email){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::forgotPassword($email);
        }

        /**
         * Delete the record of the given token from 'password_reminders' table.
         *
         * @param string $token Token retrieved from a forgotPassword.
         * @return boolean Success.
         */
        public static function destroyForgotPasswordToken($token){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::destroyForgotPasswordToken($token);
        }

        /**
         * Returns a user that corresponds to the given reset password token or
         * false if there is no user with the given token.
         *
         * @param string $token
         * @return \Zizaco\Confide\ConfideUser
         */
        public static function userByResetPasswordToken($token){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::userByResetPasswordToken($token);
        }

        /**
         * Log the user out of the application.
         */
        public static function logout(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Zizaco\Confide\Confide::logout();
        }

        /**
         * Display the default login view.
         *
         * @return \Illuminate\View\View
         */
        public static function makeLoginForm(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::makeLoginForm();
        }

        /**
         * Display the default signup view
         *
         * @return \Illuminate\View\View
         */
        public static function makeSignupForm(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::makeSignupForm();
        }

        /**
         * Display the forget password view.
         *
         * @return \Illuminate\View\View
         */
        public static function makeForgotPasswordForm(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::makeForgotPasswordForm();
        }

        /**
         * Display the forget password view
         *
         * @return \Illuminate\View\View
         */
        public static function makeResetPasswordForm(/** @noinspection PhpDocSignatureInspection */$token){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Zizaco\Confide\Confide::makeResetPasswordForm($token);
        }

    }

    class Flash extends \Laracasts\Flash\Flash{

        /** @noinspection PhpDocSignatureInspection */
        /**
         * @param  $message
         * @param  $title
         */
        public static function success($message){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Laracasts\Flash\FlashNotifier::success($message);
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * @param  $message
         * @param  $title
         */
        public static function error($message){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Laracasts\Flash\FlashNotifier::error($message);
        }

        /** @noinspection PhpDocSignatureInspection */
        /**
         * @param  $message
         * @param  $title
         */
        public static function warning($message){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Laracasts\Flash\FlashNotifier::warning($message);
        }

        /**
         * @param  $message
         * @param  $title
         */
        public static function overlay($message, $title = 'Notice'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Laracasts\Flash\FlashNotifier::overlay($message, $title);
        }

        /**
         * @param  $message
         * @param string $level
         */
        public static function message(/** @noinspection PhpDocSignatureInspection */$message, /** @noinspection PhpDocSignatureInspection */$level = 'info', /** @noinspection PhpDocSignatureInspection */$title = 'Notice'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Laracasts\Flash\FlashNotifier::message($message, $level, $title);
        }

    }

    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    class PDF extends \Nitmedia\Wkhtml2pdf\Facades\Wkhtml2pdf{

        /**
         * @param string $view
         * @param array $data
         * @param string $name
         * @throws \Exception
         */
        public static function html($view, $data = array(), $name = 'file'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::html($view, $data, $name);
        }

        public static function url($url, $name = 'file'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::url($url, $name);
        }

        /**
         * Attempts to return the library's full help info
         *
         * @return string
         */
        public static function getHelp(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getHelp();
        }

        /**
         * Set path to binary executable directory
         *
         * @throws \Exception
         * @return null
         */
        public static function setBinPath(/** @noinspection PhpDocSignatureInspection */$path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setBinPath($path);
        }

        /**
         * Get path to binary executable
         *
         * @return string
         */
        public static function getBinPath(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getBinPath();
        }

        /**
         * Set filename of binary executable
         *
         * @return null
         */
        public static function setBinFile(/** @noinspection PhpDocSignatureInspection */$name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setBinFile($name);
        }

        /**
         * Get filename of binary executable
         *
         * @return string
         */
        public static function getBinFile(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getBinFile();
        }

        /**
         * Get the binary executable
         *
         * @throws \Exception
         * @return string
         */
        public static function getBin(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getBin();
        }

        /**
         * Set absolute path where to store temporary HTML files
         *
         * @throws \Exception
         * @param string $path
         * @return null
         */
        public static function setTmpPath($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setTmpPath($path);
        }

        /**
         * Get path where to store temporary HTML files
         *
         * @return string
         */
        public static function getTmpPath(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getTmpPath();
        }

        /**
         * Set type of output mode
         *
         * @throws \Exception
         * @param string $mode
         * @return null
         */
        public static function setOutputMode($mode){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setOutputMode($mode);
        }

        /**
         * Get type of output mode
         *
         * @return string
         */
        public static function getOutputMode(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getOutputMode();
        }

        /**
         * Set absolute path where to read HTML file
         *
         * @throws \Exception
         * @param string $path
         * @return null
         */
        public static function setHtmlPath($path){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setHtmlPath($path);
        }

        /**
         * Get path where to read HTML file
         *
         * @return string
         */
        public static function getHtmlPath(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getHtmlPath();
        }

        /**
         * Set filename holding HTML
         *
         * @return null
         */
        public static function setHtmlFile(/** @noinspection PhpDocSignatureInspection */$name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setHtmlFile($name);
        }

        /**
         * Get filename holding HTML
         *
         * @return string
         */
        public static function getHtmlFile(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getHtmlFile();
        }

        /**
         * Get the path+filename holding HTML
         *
         * @throws \Exception
         * @return string
         */
        public static function getHtmlPathFile(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getHtmlPathFile();
        }

        /**
         * Set page orientation (default is portrait)
         *
         * @param string $orientation
         * @return null
         */
        public static function setOrientation($orientation){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setOrientation($orientation);
        }

        /**
         * Returns page orientation
         *
         * @return string
         */
        public static function getOrientation(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getOrientation();
        }

        /**
         * Set page/paper size (default is A4)
         *
         * @param string $size
         * @return null
         */
        public static function setPageSize($size){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setPageSize($size);
        }

        /**
         * Returns page size
         *
         * @return int
         */
        public static function getPageSize(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getPageSize();
        }

        /**
         * Automatically generate a TOC (table of contents) or not (default is disabled)
         *
         * @param boolean $toc
         * @return \Nitmedia\Wkhtml2pdf\Wkhtmltopdf
         */
        public static function setTOC($toc = true){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setTOC($toc);
        }

        /**
         * Get value of whether automatic Table Of Contents generation is set
         *
         * @return boolean
         */
        public static function getTOC(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getTOC();
        }

        /**
         * Set the number of copies to make (default is 1)
         *
         * @param int $copies
         * @return null
         */
        public static function setCopies($copies){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setCopies($copies);
        }

        /**
         * Get number of copies to make
         *
         * @return int
         */
        public static function getCopies(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getCopies();
        }

        /**
         * Whether to print in grayscale or not (default is off)
         *
         * @param boolean $mode
         * @return null
         */
        public static function setGrayscale($mode){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setGrayscale($mode);
        }

        /**
         * Get if page will be printed in grayscale
         *
         * @return boolean
         */
        public static function getGrayscale(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getGrayscale();
        }

        /**
         * Set PDF title (default is HTML <title> of first document)
         *
         * @param string $title
         * @return null
         */
        public static function setTitle($title){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setTitle($title);
        }

        /**
         * Get PDF document title
         *
         * @throws \Exception
         * @return string
         */
        public static function getTitle(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getTitle();
        }

        /**
         * Set header html (default is null)
         *
         * @param string $header
         * @return null
         */
        public static function setHeaderHtml($header){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setHeaderHtml($header);
        }

        /**
         * Get header html
         *
         * @return string
         */
        public static function getHeaderHtml(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getHeaderHtml();
        }

        /**
         * Set footer html (default is null)
         *
         * @param string $footer
         * @return null
         */
        public static function setFooterHtml($footer){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setFooterHtml($footer);
        }

        /**
         * Get footer html
         *
         * @return string
         */
        public static function getFooterHtml(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getFooterHtml();
        }

        /**
         * Set http username
         *
         * @param string $username
         * @return null
         */
        public static function setUsername($username){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setUsername($username);
        }

        /**
         * Get http username
         *
         * @return string
         */
        public static function getUsername(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getUsername();
        }

        /**
         * Set http password
         *
         * @param string $password
         * @return null
         */
        public static function setPassword($password){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setPassword($password);
        }

        /**
         * Get http password
         *
         * @return string
         */
        public static function getPassword(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getPassword();
        }

        /**
         * Set any other WKTMLTOPDF options you need
         *
         * @param string $options
         * @return null
         */
        public static function setOptions($options){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setOptions($options);
        }

        /**
         * Get any other WKTMLTOPDF options you need
         *
         * @return string
         */
        public static function getOptions(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getOptions();
        }

        /**
         * Set URL to render
         *
         * @param string $html
         * @return null
         */
        public static function setHttpUrl($url){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setHttpUrl($url);
        }

        /**
         * Get URL to render
         *
         * @return string
         */
        public static function getHttpUrl(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getHttpUrl();
        }

        /**
         * Set HTML content to render (replaces any previous content)
         *
         * @param string $html
         * @return null
         */
        public static function setHtml($html){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::setHtml($html);
        }

        /**
         * Get current HTML content
         *
         * @return string
         */
        public static function getHtml(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::getHtml();
        }

        /**
         * Return PDF with various options.
         *
         * @param int $mode How to output (constants from this same class - c.f. 'PDF get modes')
         * @param string $filename The PDF's filename (usage depends on $mode)
         */
        public static function output($mode, $filename = ''){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf::output($mode, $filename);
        }

    }

    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    class Cabinet extends \Andrew13\Cabinet\CabinetFacade{

        /**
         * Returns the Laravel application
         *
         * @return \Illuminate\Foundation\Application
         */
        public static function app(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Andrew13\Cabinet\Cabinet::app();
        }

        /**
         * Display the default upload view
         *
         * @return \Illuminate\View\View
         */
        public static function makeUploadForm(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Andrew13\Cabinet\Cabinet::makeUploadForm();
        }

        /**
         * Display the default upload list view
         *
         * @return \Illuminate\View\View
         */
        public static function makeUploadListForm(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Andrew13\Cabinet\Cabinet::makeUploadListForm();
        }

    }

    class Notifynder extends \Fenos\Notifynder\Facades\Notifynder{

        /**
         * Set the category of the
         * notification
         *
         * @param  $name
         * @return $this
         */
        public static function category($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::category($name);
        }

        /**
         * Define an entity when Notifynder is
         * used Polymorpically
         *
         * @param  $name
         * @return $this
         */
        public static function entity($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::entity($name);
        }

        /**
         * Send notifications
         * Both multiple and single
         *
         * @param array $info
         * @return mixed
         */
        public static function send($info){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::send($info);
        }

        /**
         * Send immediately the notification
         * even if the queue is enabled
         *
         * @param array $info
         * @return mixed
         */
        public static function sendNow($info){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::sendNow($info);
        }

        /**
         * Send One notification
         *
         * @param array $info
         * @return mixed
         */
        public static function sendOne($info){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::sendOne($info);
        }

        /**
         * Send multiple notifications
         *
         * @param array $info
         * @return \Fenos\Notifynder\Senders\SendMultiple
         */
        public static function sendMultiple($info){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::sendMultiple($info);
        }

        /**
         * Send a group of notifications
         *
         * @param  $group_name
         * @param  $info
         * @return mixed
         */
        public static function sendGroup($group_name, $info){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::sendGroup($group_name, $info);
        }

        /**
         * Read one notification
         *
         * @param  $notification_id
         * @return bool|\Fenos\Notifynder\Models\Notification
         */
        public static function readOne($notification_id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::readOne($notification_id);
        }

        /**
         * Read notification in base the number
         * Given
         *
         * @param  $to_id
         * @param  $numbers
         * @param string $order
         * @return mixed
         */
        public static function readLimit($to_id, $numbers, $order = 'ASC'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::readLimit($to_id, $numbers, $order);
        }

        /**
         * Read all notifications of the given
         * entity
         *
         * @param  $to_id
         * @return \Fenos\Notifynder\Number
         */
        public static function readAll($to_id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::readAll($to_id);
        }

        /**
         * Delete a single notification
         *
         * @param  $notification_id
         * @return Bool
         */
        public static function delete($notification_id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::delete($notification_id);
        }

        /**
         * Delete number of notifications
         * secified of the given entity
         *
         * @param  $to_id
         * @param  $number
         * @param string $order
         * @return mixed
         */
        public static function deleteLimit($to_id, $number, $order = 'ASC'){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::deleteLimit($to_id, $number, $order);
        }

        /**
         * Delete all notifications
         * of the the given entity
         *
         * @param  $to_id
         * @return Bool
         */
        public static function deleteAll($to_id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::deleteAll($to_id);
        }

        /**
         * Get Notifications not read
         * of the given entity
         *
         * @param  $to_id
         * @param null $limit
         * @param bool $paginate
         * @return mixed
         */
        public static function getNotRead($to_id, $limit = null, $paginate = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::getNotRead($to_id, $limit, $paginate);
        }

        /**
         * Get all notifications of the
         * given entity
         *
         * @param  $to_id
         * @param null $limit
         * @param bool $paginate
         * @return mixed
         */
        public static function getAll($to_id, $limit = null, $paginate = false){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::getAll($to_id, $limit, $paginate);
        }

        /**
         * Get number of notification not read
         * of the given entity
         *
         * @param  $to_id
         * @return mixed
         */
        public static function countNotRead($to_id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::countNotRead($to_id);
        }

        /**
         * Find Notification by ID
         *
         * @param  $notification_id
         * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|static
         */
        public static function findNotificationById($notification_id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::findNotificationById($notification_id);
        }

        /**
         * Add category to a group
         * giving the names of them
         *
         * @param  $gorup_name
         * @param  $category_name
         * @return mixed
         */
        public static function addCategoryToGroupByName($gorup_name, $category_name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::addCategoryToGroupByName($gorup_name, $category_name);
        }

        /**
         * Add category to a group
         * giving the ids of them
         *
         * @param  $gorup_id
         * @param  $category_id
         * @return mixed
         */
        public static function addCategoryToGroupById($gorup_id, $category_id){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::addCategoryToGroupById($gorup_id, $category_id);
        }

        /**
         * Add categories to a group having as first parameter
         * the name of the group, and others as name
         * categories
         *
         * @return mixed
         */
        public static function addCategoriesToGroup(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::addCategoriesToGroup();
        }

        /**
         * Fire method for fire listeners
         * of logic
         *
         * @param string $key
         * @param string $category_name
         * @param mixed|null $values
         * @return mixed|null
         */
        public static function fire($key, $category_name, $values = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::fire($key, $category_name, $values);
        }

        /**
         * Associate events to categories
         *
         * @param  $data
         * @param array $delegation
         * @return mixed
         */
        public static function delegate($data, $delegation){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::delegate($data, $delegation);
        }

        /**
         * Boot Listeners
         *
         * @return void
         */
        public static function bootListeners(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            \Fenos\Notifynder\Notifynder::bootListeners();
        }

        /**
         * Get instance of the notifynder builder
         *
         * @return \Fenos\Notifynder\Builder\NotifynderBuilder
         */
        public static function builder(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::builder();
        }

        /**
         * Check if the category is eager Loaded
         *
         * @param  $name
         * @return bool
         */
        public static function isEagerLoaded($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::isEagerLoaded($name);
        }

        /**
         * Return the Id of the category
         *
         * @return mixed
         */
        public static function id(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::id();
        }

        /**
         * Push a category in the categoriesContainer
         * property
         *
         * @param  $name
         * @param array $categoriesContainer
         */
        public static function setCategoriesContainer($name, $categoriesContainer){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Fenos\Notifynder\Notifynder::setCategoriesContainer($name, $categoriesContainer);
        }

        /**
         * Get the categoriesContainer property
         *
         * @return array
         */
        public static function getCategoriesContainer(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Fenos\Notifynder\Notifynder::getCategoriesContainer();
        }

    }

    class Saml extends \KnightSwarm\LaravelSaml\Facades\Saml{

        /**
         * Retrieve the implementing authentication source.
         *
         * @return \SimpleSAML_Auth_Source The authentication source.
         * @throws \SimpleSAML_Error_AuthSource If the requested auth source is unknown.
         */
        public static function getAuthSource(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \SimpleSAML\Auth\Simple::getAuthSource();
        }

        /**
         * Check if the user is authenticated.
         *
         * This function checks if the user is authenticated with the default authentication source selected by the
         * 'default-authsource' option in 'config.php'.
         *
         * @return bool True if the user is authenticated, false if not.
         */
        public static function isAuthenticated(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \SimpleSAML\Auth\Simple::isAuthenticated();
        }

        /**
         * Require the user to be authenticated.
         *
         * If the user is authenticated, this function returns immediately.
         *
         * If the user isn't authenticated, this function will authenticate the user with the authentication source, and
         * then return the user to the current page.
         *
         * This function accepts an array $params, which controls some parts of the authentication. See the login()
         * method for a description.
         *
         * @param array $params Various options to the authentication request. See the documentation.
         */
        public static function requireAuth($params = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \SimpleSAML\Auth\Simple::requireAuth($params);
        }

        /**
         * Start an authentication process.
         *
         * This function accepts an array $params, which controls some parts of the authentication. The accepted parameters
         * depends on the authentication source being used. Some parameters are generic:
         *  - 'ErrorURL': A URL that should receive errors from the authentication.
         *  - 'KeepPost': If the current request is a POST request, keep the POST data until after the authentication.
         *  - 'ReturnTo': The URL the user should be returned to after authentication.
         *  - 'ReturnCallback': The function we should call after the user has finished authentication.
         *
         * Please note: this function never returns.
         *
         * @param array $params Various options to the authentication request.
         */
        public static function login($params = array()){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \SimpleSAML\Auth\Simple::login($params);
        }

        /**
         * Log the user out.
         *
         * This function logs the user out. It will never return. By default, it will cause a redirect to the current page
         * after logging the user out, but a different URL can be given with the $params parameter.
         *
         * Generic parameters are:
         *  - 'ReturnTo': The URL the user should be returned to after logout.
         *  - 'ReturnCallback': The function that should be called after logout.
         *  - 'ReturnStateParam': The parameter we should return the state in when redirecting.
         *  - 'ReturnStateStage': The stage the state array should be saved with.
         *
         * @param string|array|null $params Either the URL the user should be redirected to after logging out, or an array
         * with parameters for the logout. If this parameter is null, we will return to the current page.
         */
        public static function logout($params = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \SimpleSAML\Auth\Simple::logout($params);
        }

        /**
         * Called when logout operation completes.
         *
         * This function never returns.
         *
         * @param array $state The state after the logout.
         */
        public static function logoutCompleted($state){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \SimpleSAML\Auth\Simple::logoutCompleted($state);
        }

        /**
         * Retrieve attributes of the current user.
         *
         * This function will retrieve the attributes of the current user if the user is authenticated. If the user isn't
         * authenticated, it will return an empty array.
         *
         * @return array The users attributes.
         */
        public static function getAttributes(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \SimpleSAML\Auth\Simple::getAttributes();
        }

        /**
         * Retrieve authentication data.
         *
         * @param string $name The name of the parameter, e.g. 'Attributes', 'Expire' or 'saml:sp:IdP'.
         * @return mixed|null The value of the parameter, or null if it isn't found or we are unauthenticated.
         */
        public static function getAuthData($name){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \SimpleSAML\Auth\Simple::getAuthData($name);
        }

        /**
         * Retrieve all authentication data.
         *
         * @return array|null All persistent authentication data, or null if we aren't authenticated.
         */
        public static function getAuthDataArray(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \SimpleSAML\Auth\Simple::getAuthDataArray();
        }

        /**
         * Retrieve a URL that can be used to log the user in.
         *
         * @param string|null $returnTo The page the user should be returned to afterwards. If this parameter is null, the
         * user will be returned to the current page.
         * @return string A URL which is suitable for use in link-elements.
         */
        public static function getLoginURL($returnTo = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \SimpleSAML\Auth\Simple::getLoginURL($returnTo);
        }

        /**
         * Retrieve a URL that can be used to log the user out.
         *
         * @param string|null $returnTo The page the user should be returned to afterwards. If this parameter is null, the
         * user will be returned to the current page.
         * @return string A URL which is suitable for use in link-elements.
         */
        public static function getLogoutURL($returnTo = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \SimpleSAML\Auth\Simple::getLogoutURL($returnTo);
        }

    }

    class Purifier extends \Mews\Purifier\Facades\Purifier{

        /**
         * Returns the singleton instance of HTML Purifier. If no instance has
         * been created, a new instance will be created.
         *
         * $purifier = Purifier::instance();
         *
         * @return \HTMLPurifier
         */
        public static function instance(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Mews\Purifier\Purifier::instance();
        }

        /**
         * Modifies the configuration before creating a HTML Purifier instance.
         *
         * [!!] You must create an extension and overload this method to use it.
         *
         * @param \HTMLPurifier_Config  configuration object
         * @return \HTMLPurifier_Config
         */
        public static function configure($config){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Mews\Purifier\Purifier::configure($config);
        }

        /**
         * Removes broken HTML and XSS from text using [HTMLPurifier](http://htmlpurifier.org/).
         *
         * $text = Purifier::clean($dirty_html);
         *
         * The original content is returned with all broken HTML and XSS removed.
         *
         * @param mixed  text to clean, or an array to clean recursively
         * @param mixed  optional set of configuration options, as an array or a string denoting a set of options in the config file
         * @return mixed
         */
        public static function clean($dirty, $config = null){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Mews\Purifier\Purifier::clean($dirty, $config);
        }

    }

    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    class Checkpoint extends \PCK\Facades\CheckpointRedirect{

        public static function reset(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \PCK\Helpers\CheckpointRedirect::reset();
        }

        public static function previous(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \PCK\Helpers\CheckpointRedirect::previous();
        }

        public static function process(){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \PCK\Helpers\CheckpointRedirect::process();
        }

    }

    class JavaScript extends \Laracasts\Utilities\JavaScript\Facades\JavaScript{

        /**
         * Bind given array of variables to view
         *
         * @param array $vars
         */
        public static function put($vars){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection,PhpVoidFunctionResultUsedInspection */
            return \Laracasts\Utilities\JavaScript\PHPToJavaScriptTransformer::put($vars);
        }

        /**
         * Translate the array of PHP vars
         * to JavaScript syntax.
         *
         * @param array $vars
         * @internal param $js
         * @return array
         */
        public static function buildJavaScriptSyntax($vars){
            /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection,PhpDynamicAsStaticMethodCallInspection */
            return \Laracasts\Utilities\JavaScript\PHPToJavaScriptTransformer::buildJavaScriptSyntax($vars);
        }

    }

}
