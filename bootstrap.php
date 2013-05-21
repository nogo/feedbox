<?php

use Nogo\Feedbox\Helper\ConfigLoader;
use Slim\Extras\Log\DateTimeFileWriter;
use Slim\Slim;

define('ROOT_DIR', dirname(__FILE__));

require_once ROOT_DIR . '/vendor/autoload.php';

// Load config files
// TODO cache
$configLoader = new ConfigLoader(
    ROOT_DIR . '/src/Nogo/Feedbox/Resources/config/default.yml',
    ROOT_DIR . '/data/config.yml'
);

$app = new Slim($configLoader->getConfig());
$app->config(
    'log.writer',
    new DateTimeFileWriter(
        array(
            'path' => $app->config('log_dir')
        )
    )
);