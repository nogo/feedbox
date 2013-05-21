<?php

use Nogo\Feedbox\Helper\DatabaseConnector;

require_once dirname(__FILE__) . '/../bootstrap.php';

$connector = new DatabaseConnector(
    $app->config('database_adapter'),
    $app->config('database_dsn'),
    $app->config('database_username'),
    $app->config('database_password')
);
$db = $connector->getInstance();

$app->get('/', function() use ($db, $app) {
    if (!file_exists($app->config('data_dir'))) {
        mkdir($app->config('data_dir'), 0755);
    }

    if (!file_exists($app->config('cache_dir'))) {
        mkdir($app->config('cache_dir'), 0755);
    }


    switch ($app->config('database_adapter')) {
        case 'sqlite':
            if (file_exists($app->config('database_dsn'))) {
                unlink($app->config('database_dsn'));
            }
            break;
    }

    DatabaseConnector::loadSqlFile($db, ROOT_DIR . '/config/sql' . $app->config('database_adapter') . '.sql');

    if ($app->request()->get('with_fixtures')) {
        //loadSql($db, APP_DIR . '/sql/fixtures.sql');

        $opml = new \Nogo\Feedbox\Helper\OpmlLoader();
        $opml->setContent(file_get_contents($app->config('data_dir') . '/subscriptions.xml'));
        $sources = $opml->run();

        if (!empty($sources)) {
            $sourceRepository = new \Nogo\Feedbox\Repository\Source($db);
            foreach ($sources as $source) {
                $sourceRepository->persist($source);
            }
        }
    }
});

$app->run();