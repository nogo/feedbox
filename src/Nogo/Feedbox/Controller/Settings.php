<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Api\Setting as SettingApi;
use Nogo\Feedbox\Repository\Repository;
use Nogo\Feedbox\Repository\Setting as SettingRepository;

/**
 * Class Settings
 * @package Nogo\Feedbox\Controller
 */
class Settings extends AbstractRestController
{
    /**
     * @var SettingRepository
     */
    protected $repository;

    /**
     * @var SettingApi
     */
    protected $apiDefinition;

    public function enable()
    {
        $this->app->get('/settings', array($this, 'listAction'));
        $this->app->get('/settings/:id', array($this, 'getAction'))->conditions(['id' => '\d+']);
        $this->app->post('/settings', array($this, 'postAction'));
        $this->app->put('/settings/:id', array($this, 'putAction'))->conditions(['id' => '\d+']);
        $this->app->delete('/settings/:id', array($this, 'deleteAction'))->conditions(['id' => '\d+']);
    }

    /**
     * Item repository
     *
     * @param AbstractConnection $connection
     *
     * @return SettingRepository|Repository
     */
    public function getRepository(AbstractConnection $connection = null)
    {
        if ($this->repository == null) {
            if ($connection == null) {
                $connection = $this->app->db;
            }
            $this->repository = new SettingRepository($connection);
            $this->repository->setUserScope($this->app->user['id']);
        }
        return $this->repository;
    }

    /**
     * Api definition
     *
     * @return SettingApi
     */
    public function getApiDefinition()
    {
        if ($this->apiDefinition == null) {
            $this->apiDefinition = new SettingApi();
        }
        return $this->apiDefinition;
    }
}