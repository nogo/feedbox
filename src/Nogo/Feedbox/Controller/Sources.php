<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Api\Source as SourceApi;
use Nogo\Feedbox\Repository\Source as SourceRepository;

class Sources extends AbstractRestController
{
    /**
     * @var SourceRepository
     */
    protected $repository;

    /**
     * @var SourceApi
     */
    protected $apiDefinition;

    public function enable()
    {
        $this->app->get('/sources', array($this, 'listAction'));
        $this->app->get('/sources/:id', array($this, 'getAction'))->conditions(['id' => '\d+']);
        $this->app->post('/sources', array($this, 'postAction'));
        $this->app->put('/sources/:id', array($this, 'putAction'))->conditions(['id' => '\d+']);
        $this->app->delete('/sources/:id', array($this, 'deleteAction'))->conditions(['id' => '\d+']);
    }

    public function getRepository(AbstractConnection $connection = null)
    {
        if ($this->repository == null) {
            if ($connection == null) {
                $connection = $this->app->db;
            }
            $this->repository = new SourceRepository($connection);
        }
        return $this->repository;
    }

    /**
     * Api definition
     *
     * @return SourceApi
     */
    public function getApiDefinition()
    {
        if ($this->apiDefinition == null) {
            $this->apiDefinition = new SourceApi();
        }
        return $this->apiDefinition;
    }
}