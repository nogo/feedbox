<?php

use Slim\Extras\Views\Twig as Twig;

require_once dirname(__FILE__) . '/../bootstrap.php';

Twig::$twigOptions = array(
    'charset' => 'utf-8',
    'cache' => $app->config('cache_dir'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view(new Twig());

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

        $app->render('index.html.twig');
    }
);

$app->run();