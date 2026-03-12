<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Helper\AppFactoryHelper;
use App\Routes\RouteHelperAwsS3Bucket;
use App\Routes\RouteHelperAwsS3Object;
use App\Routes\RouteHelperMain;

$app = AppFactoryHelper::create();
RouteHelperMain::addRoutesToApp($app);

// load sub-routes
RouteHelperAwsS3Bucket::addRoutesToApp($app);
RouteHelperAwsS3Object::addRoutesToApp($app);

$app->run();
