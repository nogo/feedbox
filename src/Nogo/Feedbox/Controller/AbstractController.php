<?php
namespace Nogo\Feedbox\Controller;

use Slim\Slim;
use Hampel\Json\Json;
use Hampel\Json\JsonException;

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
     * Constructor.
     *
     * @param Slim $app
     */
    public function __construct(Slim $app)
    {
        $this->app = $app;
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
     * Decode json data from request
     * @return mixed|null
     */
    protected function jsonRequest()
    {
        $input = null;

        try {
            $input = Json::decode(trim($this->app->request()->getBody()), true);
        } catch (JsonException $ex) {
            $input = null;
        }

        return $input;
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

    /**
     * Render array data as json with json_decode
     *
     * @param array $data
     */
    protected function renderJson(array $data, $status = 200)
    {
        try {
            $json = Json::encode($data);
            $this->render($json, $status);
        } catch (JsonException $ex) {
            $this->render('Data not valid', 400);
        }
    }
}