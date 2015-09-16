<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}
define('ROOT_DIR', realpath(__DIR__ . '/../'));

require_once ROOT_DIR . '/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

$app = new \Slim\App(require ROOT_DIR . '/app/settings.php');

// Dependencies
$container = $app->getContainer();

$container['db'] = function($c) {
  $settings = $c->get('settings');

  $capsule = new Capsule();
  $capsule->addConnection($settings['database']);
  $capsule->setAsGlobal();
  $capsule->bootEloquent();
  return $capsule;
};

// Load middleware
$authentication = new \Nogo\Feedbox\Middleware\Authentication($app);

// Load endpoints
$app->group('/item', function () {
    $this->post('', 'Nogo\Feedbox\Endpoint\Post:dispatch')->setName('item-post');
    $this->put('/{id}', 'Nogo\Feedbox\Endpoint\Put:dispatch')->setName('item-put');
    $this->delete('/{id}', 'Nogo\Feedbox\Endpoint\Delete:dispatch')->setName('item-delete');
})->add($authentication);

$app->group('/setting', function () {
    $this->get('', 'Nogo\Feedbox\Endpoint\Get:dispatch')->setName('setting-get');
    $this->post('', 'Nogo\Feedbox\Endpoint\Post:dispatch')->setName('setting-post');
    $this->put('/{id}', 'Nogo\Feedbox\Endpoint\Put:dispatch')->setName('setting-put');
    $this->delete('/{id}', 'Nogo\Feedbox\Endpoint\Delete:dispatch')->setName('setting-delete');
})->add($authentication);

$app->group('/source', function () {
    $this->get('', 'Nogo\Feedbox\Endpoint\Get:dispatch')->setName('source-get');
    $this->post('', 'Nogo\Feedbox\Endpoint\Post:dispatch')->setName('source-post');
    $this->put('/{id}', 'Nogo\Feedbox\Endpoint\Put:dispatch')->setName('source-put');
    $this->delete('/{id}', 'Nogo\Feedbox\Endpoint\Delete:dispatch')->setName('source-delete');
})->add($authentication);

$app->group('/user/{name}', function () use ($app, $authentication) {

  $this->put('', 'Nogo\Feedbox\Endpoint\Put:dispatch')->setName('user-update')->add($authentication);
  $this->delete('', 'Nogo\Feedbox\Endpoint\Delete:dispatch')->setName('user-delete')->add($authentication);

  // System tags unread, read, starred
  // $this->group('/{tag}', function () use ($app, $authentication) {
  //     $this->get('/')->setName('user-tag')->add($authentication);
  //     $this->put()->setName('user-tag-update')->add($authentication);
  //     $this->delete()->setName('user-tag-delete')->add($authentication);
  // });
});

$app->run();
