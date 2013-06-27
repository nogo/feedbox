<?php

use Nogo\Feedbox\Helper\Fetcher;
use Nogo\Feedbox\Helper\ConfigLoader;
use Nogo\Feedbox\Helper\DatabaseConnector;
use Nogo\Feedbox\Repository\Item;
use Nogo\Feedbox\Repository\Source;
use Nogo\Feedbox\Repository\Tag;

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
$tagRepository = new Tag($connection);
$itemRepository = new Item($connection);

// fetch active sources with uri
$sources = $sourceRepository->fetchAllActiveWithUri();

// get the feed runner
$defaultWorkerClass = $config['worker.default'];

$fetcher = new Fetcher();
$fetcher->setTimeout($config['fetcher.timeout']);

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

        $items = null;

        $content = $fetcher->get($source['uri']);
        /**
         * @var $worker \Nogo\Feedbox\Feed\Worker
         */
        $worker = new $defaultWorkerClass();
        $worker->setContent($content);
        try {
            $items = $worker->execute();
        } catch (\Exception $e) {
            $items = null;
        }

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
                $source['period'] = $worker->getUpdateInterval();
            }
            $source['errors'] = $worker->getErrors();

            // update source unread counter
            $count = $source['unread'];
            $source['unread'] = $itemRepository->countSourceUnread([$source['id']]);
            $sourceRepository->persist($source);

            // update tag unread counter
            if (!empty($source['tag_id'])) {
                $tag = $tagRepository->fetchOneById($source['tag_id']);
                if ($tag) {
                    $tag['unread'] = $sourceRepository->countTagUnread([$tag['id']]);
                    $tagRepository->persist($tag);
                }
            }

            if ($config['debug']) {
                echo sprintf("%d new items.\n", abs($source['unread'] - $count));
            }
        } else {
            $source['errors'] = $worker->getErrors();
            $source['unread'] = $itemRepository->countSourceUnread([$source['id']]);
            $sourceRepository->persist($source);

            // update tag unread counter
            if (!empty($source['tag_id'])) {
                $tag = $tagRepository->fetchOneById($source['tag_id']);
                if ($tag) {
                    $tag['unread'] = $sourceRepository->countTagUnread([$tag['id']]);
                    $tagRepository->persist($tag);
                }
            }

            if ($config['debug']) {
                echo sprintf("%s\n", $worker->getErrors());
            }
        }
    }
}