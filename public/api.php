<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

$app->container->singleton('db',
    function() use ($app) {
        $connector = new \Nogo\Feedbox\Helper\DatabaseConnector(
            $app->config('database_adapter'),
            $app->config('database_dsn'),
            $app->config('database_username'),
            $app->config('database_password')
        );
        return $connector->getInstance();
    }
);

if ($app->config('login.enabled')) {
    $auth = new \Nogo\Feedbox\Middleware\Authentication();
    $auth->setAccessRepository(new \Nogo\Feedbox\Repository\Access($app->db));
    $auth->setUserRepository(new \Nogo\Feedbox\Repository\User($app->db));
    $app->add($auth);
}

// set content-type
$app->contentType($app->config('api.content_type'));

// set default route
$app->get('/', function() use ($app) {});

// load api controller
foreach($app->config('api.controller') as $class) {
    $ref = new ReflectionClass($class);
    if ($ref->isSubclassOf('Nogo\Feedbox\Controller\AbstractController')) {
        /**
         * @var \Nogo\Feedbox\Controller\AbstractController $controller
         */
        $controller = new $class($app);
        $controller->enable();
    }
}

$app->run();