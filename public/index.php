<?php

require_once dirname(__FILE__) . '/../bootstrap.php';

if ($app->config('login.enabled')) {
    $app->add(
        new \Nogo\Feedbox\Middleware\HttpBasicAuth(
            $app->config('login.credentials'),
            $app->config('login.realm'),
            $app->config('login.algorithm')
        )
    );
}

$app->get('/',
    function () use ($app) {
        if (!$app->config('installed')) {
            $app->redirect('install.php');
        }

        $app->render('index.html');
    }
);

$app->run();