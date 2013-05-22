<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Repository\Repository;
use Nogo\Feedbox\Repository\Item as ItemRepository;

/**
 * Class Items
 * @package Nogo\Feedbox\Controller
 */
class Items extends AbstractRestController
{
    /**
     * @var ItemRepository
     */
    protected $repository;

    protected $allowed_params = ['page', 'limit', 'unread', 'starred', 'source', 'sortby'];

    public function enable()
    {
        $this->app->get('/items', array($this, 'listAction'));
        $this->app->get('/items/:id', array($this, 'getAction'))->conditions(['id' => '\d+']);
        $this->app->post('/items', array($this, 'postAction'));
        $this->app->put('/items/:id', array($this, 'putAction'))->conditions(['id' => '\d+']);
        $this->app->delete('/items/:id', array($this, 'deleteAction'))->conditions(['id' => '\d+']);
    }

    /**
     * Item repository
     *
     * @param AbstractConnection $connection
     *
     * @return ItemRepository|Repository
     */
    public function getRepository(AbstractConnection $connection = null)
    {
        if ($this->repository == null) {
            if ($connection == null) {
                $connection = $this->connection;
            }
            $this->repository = new ItemRepository($connection);
        }
        return $this->repository;
    }

    public function listAction()
    {
        $params = $this->getParameter($this->allowed_params);

        $result = $this->getRepository()->fetchAllWithFilter($params, true);
        $this->app->response()->header('X-Items-Total', $result[0]['count(*)']);
        $result = $this->getRepository()->fetchAllWithFilter($params);

        $this->renderJson($result);
    }
}