<?php

require_once dirname(__FILE__) . '/../bootstrap.php';

$app->get('/', function() use ($app) {
    $app->render('index.html');
});

$app->run();