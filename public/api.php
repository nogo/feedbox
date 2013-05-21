<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

// database connection with pdo
$connection_factory = new Aura\Sql\ConnectionFactory();

/**
 * @var \Aura\Sql\Connection\Sqlite $db
 */
$db = $connection_factory->newInstance(
    $app->config('database_adapter'),
    $app->config('database_dsn'),
    $app->config('database_username'),
    $app->config('database_password')
);

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

// set default route
$app->get('/', function() use ($db, $app) {});

$app->run();