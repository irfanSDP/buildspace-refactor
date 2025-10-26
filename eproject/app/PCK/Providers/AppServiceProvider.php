<?php namespace PCK\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('iunique', 'PCK\Validators\CustomValidation@validateIUnique');
    }

    public function register()
    {
    }
}