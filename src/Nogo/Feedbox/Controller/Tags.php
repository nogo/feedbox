<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Repository\Repository;
use Nogo\Feedbox\Repository\Tag as TagRepository;

/**
 * Class Tags
 * @package Nogo\Feedbox\Controller
 */
class Tags extends AbstractRestController
{
    /**
     * @var TagRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $fields = [
        'id' => [
            'read' => true,
            'write' => false
        ],
        'name' => [
            'read' => true,
            'write' => true
        ],
        'color' => [
            'read' => true,
            'write' => true
        ],
        'sources' => [
            'read' => true,
            'write' => false
        ],
        'created_at' => [
            'read' => true,
            'write' => false
        ],
        'updated_at' => [
            'read' => true,
            'write' => false
        ]
    ];

    public function enable()
    {
        $this->app->get('/tags', array($this, 'listAction'));
        $this->app->get('/tags/:id', array($this, 'getAction'))->conditions(['id' => '\d+']);
        $this->app->post('/tags', array($this, 'postAction'));
        $this->app->put('/tags/:id', array($this, 'putAction'))->conditions(['id' => '\d+']);
        $this->app->delete('/tags/:id', array($this, 'deleteAction'))->conditions(['id' => '\d+']);
    }

    /**
     * Item repository
     *
     * @param AbstractConnection $connection
     *
     * @return TagRepository|Repository
     */
    public function getRepository(AbstractConnection $connection = null)
    {
        if ($this->repository == null) {
            if ($connection == null) {
                $connection = $this->connection;
            }
            $this->repository = new TagRepository($connection);
        }
        return $this->repository;
    }

    public function getApiDefinition()
    {
        return $this->fields;
    }
}