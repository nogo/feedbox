<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
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
     * @var array
     */
    protected $fields = [
        'id' => [
            'read' => true,
            'write' => false
        ],
        'key' => [
            'read' => true,
            'write' => true
        ],
        'value' => [
            'read' => true,
            'write' => true
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
                $connection = $this->connection;
            }
            $this->repository = new SettingRepository($connection);
        }
        return $this->repository;
    }

    public function getApiDefinition()
    {
        return $this->fields;
    }
}