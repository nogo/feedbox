<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Slim\Slim;

/**
 * Class AbstractController
 *
 * @package Nogo\Feedbox\Controller
 */
abstract class AbstractController
{
    /**
     * @var Slim
     */
    protected $app;
    /**
     * @var AbstractConnection
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param Slim $app
     * @param AbstractConnection $connection
     */
    public function __construct(Slim $app, AbstractConnection $connection)
    {
        $this->app = $app;
        $this->connection = $connection;
    }

    /**
     * Enable should set app routes
     *
     * @return void
     */
    abstract public function enable();

    protected function getParameter(array $filter = array())
    {
        if (empty($filter)) {
            $params = $this->app->request()->get();
        } else {
            $params = array();
            foreach ($filter as $name) {
                $params[$name] = $this->app->request()->get($name);
            }
        }

        return $params;
    }

    /**
     * Render array data as json with json_decode
     *
     * @param array $data
     */
    protected function renderJson(array $data, $status = 200)
    {
        $json = json_encode($data);
        $this->render($json, $status);
    }

    /**
     * Render content and status
     *
     * @param $body
     * @param int $status
     */
    protected function render($body, $status = 200)
    {
        $this->app->response()->status($status);
        $this->app->response()->body($body);
    }
}