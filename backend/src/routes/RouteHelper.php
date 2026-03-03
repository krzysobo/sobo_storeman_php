<?php

namespace App\Routes;
use Slim\App;


interface RouteHelper {
    public static function addRoutesToApp(App $app);
    
}