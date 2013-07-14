<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Api\User as UserApi;
use Nogo\Feedbox\Repository\Repository;
use Nogo\Feedbox\Repository\User as UserRepository;
/**
 * Class Users
 * @package Nogo\Feedbox\Controller
 */
class Users extends AbstractRestController
{
    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * @var UserApi
     */
    protected $apiDefinition;

    public function enable()
    {

    }

    /**
     * Item repository
     *
     * @param AbstractConnection $connection
     *
     * @return UserRepository|Repository
     */
    public function getRepository(AbstractConnection $connection = null)
    {
        if ($this->repository == null) {
            if ($connection == null) {
                $connection = $this->connection;
            }
            $this->repository = new UserRepository($connection);
        }
        return $this->repository;
    }

    /**
     * Api definition
     *
     * @return UserApi
     */
    public function getApiDefinition()
    {
        if ($this->apiDefinition == null) {
            $this->apiDefinition = new UserApi();
        }
        return $this->apiDefinition;
    }
}