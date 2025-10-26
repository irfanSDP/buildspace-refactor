<?php namespace PCK\Providers;

use Illuminate\Support\ServiceProvider;

class CheckpointRedirectServiceProvider extends ServiceProvider {

    public function register()
    {
        \App::bind('checkpointRedirect', function()
        {
            return new \PCK\Helpers\CheckpointRedirect;
        });
    }

}
