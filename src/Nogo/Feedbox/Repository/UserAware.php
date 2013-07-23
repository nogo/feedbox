<?php
namespace Nogo\Feedbox\Repository;

use Aura\Sql\Connection\AbstractConnection;

/**
 * Class Repository
 * @package Nogo\Feedbox\Repository
 */
interface UserAware {

    /**
     * Is user scope activated.
     *
     * @return bool
     */
    public function hasUserScope();

    /**
     * Set user id for queries
     *
     * @param $user_id int
     */
    public function setUserScope($user_id);


    /**
     * Scope by user_id
     *
     * @param \Aura\Sql\Query\Select $select
     * @param array $bind
     * @return array bind
     */
    public function scopeByUserId(\Aura\Sql\Query\Select $select, array $bind = []);
}