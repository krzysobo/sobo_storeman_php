<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Helper\AppFactoryHelper;
use App\Routes\RouteHelperAwsAuth;
use App\Routes\RouteHelperAwsS3Bucket;
use App\Routes\RouteHelperAwsS3Object;
use App\Routes\RouteHelperMain;
use App\Routes\RouteHelperOpenApi;

$app = AppFactoryHelper::create();
RouteHelperMain::addRoutesTo($app);
RouteHelperOpenApi::addRoutesTo($app);

$app->group('/aws', function($awsObj){
    RouteHelperAwsAuth::addRoutesTo($awsObj);

    $awsObj->group('/s3', function($s3Obj) {
        RouteHelperAwsS3Bucket::addRoutesTo($s3Obj);
        RouteHelperAwsS3Object::addRoutesTo($s3Obj);
    });


});
// load sub-routes

$app->run();
