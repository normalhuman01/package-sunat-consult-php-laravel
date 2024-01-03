<?php
namespace MrJmpl3\LaravelPeruConsult\Facades;

use Illuminate\Support\Facades\Facade;

class Sunat extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sunat';
    }
}
