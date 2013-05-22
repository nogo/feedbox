<?php

use Nogo\Feedbox\Feed\Runner;
use Nogo\Feedbox\Helper\ConfigLoader;
use Nogo\Feedbox\Helper\DatabaseConnector;
use Nogo\Feedbox\Repository\Item;
use Nogo\Feedbox\Repository\Source;

define('ROOT_DIR', dirname(__FILE__));

require ROOT_DIR . '/vendor/autoload.php';

// load API config
$configLoader = new ConfigLoader(
    ROOT_DIR . '/src/Nogo/Feedbox/Resources/config/default.yml',
    ROOT_DIR . '/data/config.yml'
);

$config = $configLoader->getConfig();

// database connection with pdo
$connector = new DatabaseConnector(
    $config['database_adapter'],
    $config['database_dsn'],
    $config['database_username'],
    $config['database_password']
);
$connection = $connector->getInstance();

// create repositories
$sourceRepository = new Source($connection);
$itemRepository = new Item($connection);

// fetch active sources with uri
$sources = $sourceRepository->fetchAllActiveWithUri();

// get the feed runner
$defaultWorkerClass = $config['runner.default_worker'];

$feedRunner = new Runner();
$feedRunner->setWorker(new $defaultWorkerClass());
$feedRunner->setTimeout($config['runner.timeout']);

$now = new \DateTime();
foreach ($sources as $source) {
    if (!empty($source['uri'])) {
        // periodic update
        if ($source['last_update'] != null) {
            $last_update = new \DateTime($source['last_update']);
            $interval = $last_update->diff($now, true);

            if ($interval !== false) {
                switch ($source['period']) {
                    case 'hourly':
                        $format = '%h';
                        $period = 0;
                        break;
                    case 'daily':
                        $format = '%a';
                        $period = 0;
                        break;
                    case 'weekly':
                        $format = '%a';
                        $period = 5;
                        break;
                    case 'yearly':
                        $format = '%y';
                        $period = 0;
                        break;
                    default:
                        $format = false;
                        $period = 0;
                }

                if ($format && $interval->format($format) <= $period) {
                    continue;
                }
            }
        }

        // set uri
        if ($config['debug']) {
            echo sprintf("Read source [%s]: ", $source['name']);
        }
        $feedRunner->setUri($source['uri']);
        $items = $feedRunner->run();

        if ($items != null) {
            foreach($items as $item) {
                if (isset($item['uid'])) {
                    $dbItem = $itemRepository->fetchOneBy('uid', $item['uid']);
                    if (!empty($dbItem)) {
                        if ($item['content'] !== $dbItem['content']
                            || $item['title'] !== $dbItem['title']) {
                            $item['id'] = $dbItem['id'];
                            $item['starred'] = $dbItem['starred'];
                            $item['created_at']= $dbItem['created_at'];
                        } else {
                            continue;
                        }
                    }
                }

                $item['source_id'] = $source['id'];
                $itemRepository->persist($item);
            }

            $source['last_update'] = date('Y-m-d H:i:s');
            if (empty($source['period'])) {
                $source['period'] = $feedRunner->getUpdateInterval();
            }
            $source['errors'] = $feedRunner->getErrors();
            $count = $source['unread'];
            $source['unread'] = $itemRepository->countUnread([$source['id']]);
            $sourceRepository->persist($source);

            if ($config['debug']) {
                echo sprintf("%d new items.\n", $source['unread'] - $count);
            }
        } else {
            $source['errors'] = $feedRunner->getErrors();
            $source['unread'] = $itemRepository->countUnread([$source['id']]);
            $sourceRepository->persist($source);

            if ($config['debug']) {
                echo sprintf("%s\n", $feedRunner->getErrors());
            }
        }
    }
}