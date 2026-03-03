<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Helper\AppFactoryHelper;
use App\Routes\RouteHelperMain;
use App\Routes\RouteHelperAwsS3;

$app = AppFactoryHelper::create();
RouteHelperMain::addRoutesToApp($app);


// load sub-routes
RouteHelperAwsS3::addRoutesToApp($app);

$app->run();
