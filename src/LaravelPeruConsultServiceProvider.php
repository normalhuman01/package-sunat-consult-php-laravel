<?php
namespace MrJmpl3\LaravelPeruConsult;

use Illuminate\Support\ServiceProvider;
use MrJmpl3\LaravelPeruConsult\Consultants\Sunat;

class LaravelPeruConsultServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('sunat', function ($app) {
            return new Sunat();
        });
    }
}
