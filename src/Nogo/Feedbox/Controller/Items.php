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
        $result = $this->getRepository()->fetchAllWithFilter(['unread' => true]);
        $this->renderJson($result);
    }
}