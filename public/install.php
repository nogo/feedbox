<?php

require_once dirname(__FILE__) . '/../app/bootstrap.php';

// load API config
$configLoader->mergeLoad(APP_DIR . '/config/api.default.yml');
try {
    $configLoader->mergeLoad(APP_DIR . '/config/api.config.yml', true);
} catch (Exception $ex) {

}
$app->config($configLoader->getConfig());

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

function loadSql($db, $file) {
    if (file_exists($file)) {
        $sql = file_get_contents($file);

        if (!empty($sql)) {
            $queries = explode(';', $sql);
            foreach ($queries as $q) {
                $db->query(trim($q) . ";");
            }
        }
    }
}

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

    loadSql($db, APP_DIR . '/sql/' . $app->config('database_adapter') . '.sql');

    if ($app->getMode() === 'dev') {
        //loadSql($db, APP_DIR . '/sql/fixtures.sql');

        $opml = new \Nogo\Feed\Helper\OpmlLoader();
        $opml->setSourceRepository(new \Nogo\Feed\Repository\Source($db));
        $opml->setContent(file_get_contents($app->config('data_dir') . '/subscriptions.xml'));
        $opml->run();
    }
});

$app->run();