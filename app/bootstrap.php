<?php

define('ROOT_DIR', dirname(__FILE__) . '/..');
define('APP_DIR', ROOT_DIR . '/app');
define('VENDOR_DIR', ROOT_DIR . '/vendor');

require ROOT_DIR . '/vendor/autoload.php';

use Slim\Slim;
use Nogo\Feedbox\Helper\ConfigLoader;
use Symfony\Component\Yaml\Yaml;

// load config
// TODO cache
$configLoader = new ConfigLoader(APP_DIR . '/config/default.yml');
try {
    $configLoader->mergeLoad(APP_DIR . '/config/config.yml', true);
} catch (Exception $ex) {

}
$app = new Slim($configLoader->getConfig());
