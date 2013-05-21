<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

if ($app->config('login.enabled')) {
    $app->add(new \Nogo\Feedbox\Middleware\HttpBasicAuth(
            $app->config('login.credentials'),
            $app->config('login.realm'),
            $app->config('login.algorithm')
    ));
}

$connector = new \Nogo\Feedbox\Helper\DatabaseConnector(
    $app->config('database_adapter'),
    $app->config('database_dsn'),
    $app->config('database_username'),
    $app->config('database_password')
);
$db = $connector->getInstance();

// set content-type
$app->contentType($app->config('api.content_type'));

// set default route
$app->get('/', function() use ($db, $app) {});

// load api controller
foreach($app->config('api.controller') as $class) {
    $ref = new ReflectionClass($class);
    if ($ref->isSubclassOf('Nogo\Feedbox\Controller\AbstractController')) {
        $controller = new $class($app, $db);
        $controller->enable();
    }
}

$app->run();