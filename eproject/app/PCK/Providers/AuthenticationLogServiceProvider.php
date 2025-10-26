<?php

namespace PCK\Providers;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class AuthenticationLogServiceProvider extends ServiceProvider
{
    protected $events = [
        'auth.login' => [
            'PCK\AuthenticationLog\Listeners\LogSuccessfulLogin',
        ],
        'auth.logout' => [
            'PCK\AuthenticationLog\Listeners\LogSuccessfulLogout',
        ],
    ];

    public function boot()
    {
        $this->registerEvents();
    }

    public function register()
    {
    }
    
    protected function registerEvents()
    {
        $events = $this->app->make(Dispatcher::class);

        foreach ($this->events as $event => $listeners)
        {
            foreach ($listeners as $listener)
            {
                $events->listen($event, $listener);
            }
        }
    }
}