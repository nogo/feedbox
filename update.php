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
 * @var \Aura\Sql\Connection\Sqlite $connection
 */
$connection = $connection_factory->newInstance(
    $app->config('database_adapter'),
    $app->config('database_dsn'),
    $app->config('database_username'),
    $app->config('database_password')
);

$sourceRepository = new \Nogo\Feedbox\Repository\Source($connection);
$itemRepository = new \Nogo\Feedbox\Repository\Item($connection);

$feedRunner = new \Nogo\Feedbox\Helper\FeedLoader();
$feedRunner->setCacheDir($app->config('cache_dir'));
$feedRunner->setSourceRepository($sourceRepository);
$feedRunner->setItemRepository($itemRepository);

$sources = $sourceRepository->fetchAll();

$now = new \DateTime();
foreach($sources as $source) {
    if(isset($result['uri'])) {

        // update periodly
        if ($source['last_update'] != null) {
            $last_update = new \DateTime($source['last_update']);
            $interval = $last_update->diff($now, true);

            if ($interval !== false) {
                switch($source['period']) {
                    case 'hourly':
                        $format = 'h';
                        $period = 0;
                        break;
                    case 'daily':
                        $format = 'a';
                        $period = 0;
                        break;
                    case 'weekly':
                        $format = 'a';
                        $period = 5;
                        break;
                    case 'yearly':
                        $format = 'y';
                        $period = 0;
                        break;
                    default:
                        $format = 'a';
                        $period = 0;
                }

                if ($interval->format($format) <= $period) {
                    continue;
                }
            }
        }


        $feedRunner->setSource($source);
        $feedRunner->run();
    }
}