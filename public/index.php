<?php

use Nogo\Feedbox\Repository\Source;
use Nogo\Feedbox\Repository\User;
use Nogo\Feedbox\Helper\DatabaseConnector;
use Nogo\Feedbox\Helper\OpmlLoader;
use Nogo\Feedbox\Views\Twig;
use Symfony\Component\Yaml\Yaml;

require_once dirname(__FILE__) . '/../bootstrap.php';

Twig::$twigOptions = array(
    'charset' => 'utf-8',
    'cache' => $app->config('cache_dir'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view(new Twig())->appendData(array('base_url' => $app->request()->getRootUri()));

$app->get('/',
    function () use ($app) {
        if (!$app->config('installed')) {
            $app->redirect($app->request()->getRootUri() . '/install');
        }

        $app->render('index.html.twig');
    }
);

/** Registration route */
if ($app->config('login.enabled') && $app->config('registration.enabled')) {
    $app->get('/register',
        function() use ($app) {
            $app->render('register.html.twig');
        }
    );

    $app->post('/register',
        function() use ($app) {
            $connector = new DatabaseConnector(
                $app->config('database_adapter'),
                $app->config('database_dsn'),
                $app->config('database_username'),
                $app->config('database_password')
            );
            $db = $connector->getInstance();
            $userRepository = new User($db);
            $error = array();

            $request = $app->request();

            $user = array(
                'name' => filter_var($request->post('register_username'), FILTER_SANITIZE_STRING),
                'email' => filter_var($request->post('register_email'), FILTER_VALIDATE_EMAIL)
            );
            $password = $request->post('register_password');
            $retype = $request->post('register_password_retype');

            if (empty($user['name'])) {
                $error['register_username'] = 'Username is empty.';
            }

            if (empty($user['email'])) {
                $error['register_email'] = 'eMail is empty.';
            }

            if (empty($password)) {
                $error['register_password'] = 'Password is empty.';
            }

            if (empty($retype)) {
                $error['register_password_retype'] = 'Retyped password is empty.';
            }

            if (empty($error)) {
                $found = $userRepository->findBy('name', $user['name']);
                if (!$found) {
                    if ($password === $retype) {
                        if ($app->config('registration.auto_active')) {
                            $user['active'] = true;
                        }
                        $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                        $user['created_at'] = date('Y-m-d H:i:s');
                        $user['updated_at'] = $user['created_at'];
                        $userRepository->persist($user);
                        $app->render('done.html.twig',  array('title' => 'Registration'));
                        return;
                    } else {
                        $error['register_password_retype'] = 'The retyped password does not match the password.';
                    }
                } else {
                    $error['register_username'] = 'The username is already registered.';
                }
            }

            $app->render('register.html.twig',  array('user' => $user,  'error' => $error));
        }
    );
}

/** Install Route */
if (!$app->config('installed')) {
    $app->get('/install',
        function() use ($app) {
            $error = array();

            if (!file_exists($app->config('data_dir'))) {
                if (!mkdir($app->config('data_dir'), 0755)) {
                    $error['data_dir_error'] = 'Data [' . $app->config('data_dir') . '] directory could not created.';
                }
            } else if (!is_writable($app->config('data_dir'))) {
                $error['data_dir_error'] = 'Data [' . $app->config('data_dir') . '] directory not writable.';
            }

            $app->render('install.html.twig', array('error' => $error));
        }
    );

    $app->post('/install',
        function () use ($app, $configLoader) {
            if (!file_exists($app->config('cache_dir'))) {
                mkdir($app->config('cache_dir'), 0755);
            }

            $error = array();
            $request = $app->request();
            $input = filter_var_array(
                $request->post(),
                array(
                    'database_adapter' => array(
                        'filter' => FILTER_VALIDATE_REGEXP,
                        'options' => array(
                            'regexp' => '/mysql|sqlite/'
                        )
                    ),
                    'database_dsn' => FILTER_UNSAFE_RAW,
                    'database_username' => FILTER_UNSAFE_RAW,
                    'database_password' => FILTER_UNSAFE_RAW,
                    'login_enabled' => FILTER_VALIDATE_BOOLEAN,
                    'login_username' => FILTER_SANITIZE_STRING,
                    'login_email' => FILTER_VALIDATE_EMAIL,
                    'login_password' => FILTER_UNSAFE_RAW,
                    'login_password_retype' => FILTER_UNSAFE_RAW,
                    'opml' => FILTER_UNSAFE_RAW,
                )
            );

            if (empty($input['database_dsn'])) {
                $error['database_dsn'] = "Is required.";
            }
            if ($input['database_adapter'] == 'mysql') {
                if (strpos($input['database_dsn'], 'mysql:host=') === false) {
                    $error['database_dsn'] = " The dsn string should look like \"mysql:host=localhost;dbname=feedbox\"";
                }
                if (empty($input['database_username'])) {
                    $error['database_username'] = "Is required.";
                }
            }

            if ($input['login_enabled']) {
                if (empty($input['login_username'])) {
                    $error['login_username'] = "Is required.";
                }
                if (empty($input['login_email'])) {
                    $error['login_email'] = "Is required.";
                }
                if (empty($input['login_password'])) {
                    $error['login_password'] = "Is required.";
                }
                if (empty($input['login_password_retype'])) {
                    $error['login_password_retype'] = "Is required.";
                }
                if ($input['login_password'] !== $input['login_password_retype']) {
                    $error['login_password_retype'] = "Does not match the password.";
                }
            }

            if (!empty($error)) {
                unset($input['login_password'], $input['login_password_retype']);
                $app->render('install.html.twig', array('input' => $input, 'error' => $error));
            } else {
                // Write to config
                $config = array(
                    'installed' => true,
                    'mode' => 'prod',
                    'debug' => false,
                    'database_adapter' => $input['database_adapter'],
                    'database_dsn' => $input['database_dsn'],
                    'database_username' => $input['database_username'],
                    'database_password' => $input['database_password']
                );

                if ($input['login_enabled']) {
                    $config['login.enabled'] = true;
                }

                file_put_contents($app->config('data_dir') . '/config.yml', Yaml::dump($config));
                chmod($app->config('data_dir') . '/config.yml', 0666);
                file_put_contents($app->config('data_dir') . '/.htaccess', "AllowOverride None\nOrder deny,allow\ndeny from all\n");

                // load new config file
                $configLoader->load($app->config('data_dir') . '/config.yml');
                $app->config($configLoader->getConfig());
                $app->config('installed', false);

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
                    $connector->migrate($db, ROOT_DIR . '/src/Nogo/Feedbox/Resources/sql/' . $app->config('database_adapter'));

                    $user = array(
                        'name' => $input['login_username'],
                        'email' => $input['login_email'],
                        'password' => password_hash($input['login_password'], PASSWORD_DEFAULT),
                        'active' => true,
                        'superadmin' => true,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')

                    );
                    $userRepository = new User($db);
                    $user['id'] = $userRepository->persist($user);

                    $opml = trim($request->post('opml'));
                    if (!empty($opml)) {
                        $opmlLoader = new OpmlLoader();
                        $opmlLoader->setContent($opml);
                        $sources = $opmlLoader->run();

                        if (!empty($sources)) {
                            $sourceRepository = new Source($db);
                            foreach ($sources as $source) {
                                $source['user_id'] = $user['id'];
                                $sourceRepository->persist($source);
                            }
                        }
                    }
                }

                $app->render('done.html.twig', array('title' => 'Install'));
                $app->config('installed', true);
            }
        }
    );

}

$app->get(
    '/migrate',
    function() use ($app) {
        $connector = new DatabaseConnector(
            $app->config('database_adapter'),
            $app->config('database_dsn'),
            $app->config('database_username'),
            $app->config('database_password')
        );
        $db = $connector->getInstance();

        $ignore = $app->config('api.migration.ignore');
        if ($ignore == null) {
            $ignore = [];
        }

        try {
            $migrations = $db->fetchAll("SELECT * FROM version");
            foreach($migrations as $migration) {
                $ignore[] = $migration['key'];
            }
            $ignore = array_unique($ignore);
        } catch (PDOException $e) {}

        print_r($ignore);

        $queries = $connector->migrate(
            $db,
            ROOT_DIR . '/src/Nogo/Feedbox/Resources/sql/' . $app->config('database_adapter'),
            $ignore
        );

        $app->render('done.html.twig', array('title' => 'Migration',  'msg' => $queries));
    }
);


$app->run();