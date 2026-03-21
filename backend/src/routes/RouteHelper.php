<?php

namespace App\Routes;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;


interface RouteHelper {
    public static function addRoutesTo(mixed $app);
    
}