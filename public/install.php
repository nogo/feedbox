<?php

use Nogo\Feedbox\Repository\Source;
use Nogo\Feedbox\Helper\DatabaseConnector;
use Nogo\Feedbox\Helper\OpmlLoader;
use Symfony\Component\Yaml\Yaml;

require_once dirname(__FILE__) . '/../bootstrap.php';

$app->get(
    '/',
    function () use ($app) {
        if ($app->config('installed')) {
            $app->render('install_done.html');
        } else {
            $data = array();

            if (!file_exists($app->config('data_dir'))) {
                if (!mkdir($app->config('data_dir'), 0755)) {
                    $data['data_dir_error'] = 'Data [' . $app->config('data_dir') . '] directory could not created.';
                }
            } else if (!is_writable($app->config('data_dir'))) {
                $data['data_dir_error'] = 'Data [' . $app->config('data_dir') . '] directory not writable.';
            }

            $app->render('install.html', $data);
        }
    }
);

$app->post(
    '/',
    function () use ($app, $configLoader) {
        if (!file_exists($app->config('cache_dir'))) {
            mkdir($app->config('cache_dir'), 0755);
        }

        $request = $app->request();
        // TODO Check input

        // Write to config
        $config = array(
            'installed' => 'true',
            'database_adapter' => $request->post('database_adapter'),
            'database_dsn' => $request->post('database_dsn'),
            'database_username' => $request->post('database_username'),
            'database_password' => $request->post('database_password')
        );

        if ($request->post('login_enabled')) {
            $config['login.enabled'] = true;
            $config['login.algorithm'] =  $request->post('login_algorithm');
            $config['login.credentials'] = array(
                $request->post('login_username') => hash($request->post('login_algorithm'), $request->post('login_password'))
            );
        }
        file_put_contents($app->config('data_dir') . '/config.yml', Yaml::dump($config));

        // load new config file
        $configLoader->load($app->config('data_dir') . '/config.yml');
        $app->config($configLoader->getConfig());

        // Create database
        switch ($app->config('database_adapter')) {
            case 'sqlite':
                if (file_exists($app->config('database_dsn'))) {
                    unlink($app->config('database_dsn'));
                }
                break;
        }

        $connector = new DatabaseConnector(
            $app->config('database_adapter'),
            $app->config('database_dsn'),
            $app->config('database_username'),
            $app->config('database_password')
        );
        $db = $connector->getInstance();

        if ($db != null) {
            DatabaseConnector::loadSqlFile(
                $db,
                ROOT_DIR . '/src/Nogo/Feedbox/Resources/sql/' . $app->config('database_adapter') . '.sql'
            );

            $opml = trim($request->post('opml'));
            if (!empty($opml)) {
                $opmlLoader = new OpmlLoader();
                $opmlLoader->setContent($opml);
                $sources = $opmlLoader->run();

                if (!empty($sources)) {
                    $sourceRepository = new Source($db);
                    foreach ($sources as $source) {
                        $sourceRepository->persist($source);
                    }
                }
            }
        }

        $app->render('install_done.html');
    }
);

$app->run();