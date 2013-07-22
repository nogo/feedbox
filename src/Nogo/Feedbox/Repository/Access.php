<?php
namespace Nogo\Feedbox\Repository;

/**
 * Class Access
 * @package Nogo\Feedbox\Repository
 */
class Access extends AbstractRepository
{
    const ID = 'id';
    const TABLE = 'access';

    protected $filter = array(
        'user_id' => FILTER_VALIDATE_INT,
        'client' => FILTER_SANITIZE_STRING,
        'token' => FILTER_SANITIZE_STRING,
        'expire' => array(
            'filter' => FILTER_CALLBACK,
            'options' => array('Nogo\Feedbox\Helper\Validator', 'datetime')
        )
    );

    public function identifier()
    {
        return self::ID;
    }

    public function tableName()
    {
        return self::TABLE;
    }

    public function validate(array $entity)
    {
        return filter_var_array($entity, $this->filter, false);
    }

    public function withRelations(array $entity)
    {
        return $entity;
    }

    /**
     * Find one access key by user_id and client
     *
     * @param $user_id
     * @param $client
     * @return array
     */
    public function findByUserClient($user_id, $client)
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['*'])
            ->from($this->tableName())
            ->where('user_id = :user_id AND client = :client');

        $result = $this->connection->fetchOne($select, ['user_id' => $user_id, 'client' => $client]);

        if (!empty($result)) {
            $result = $this->withRelations($result);
        }

        return $result;
    }

    /**
     * Remove UserClient
     *
     * @param $user_id
     * @param $client
     * @return \PDOStatement
     */
    public function removeUserClient($user_id, $client)
    {
        /**
         * @var $select \Aura\Sql\Query\Delete
         */
        $delete = $this->connection->newDelete();
        $delete->from($this->tableName())
            ->where('user_id = :user_id AND client = :client');

        return $this->connection->query($delete, ['user_id' => $user_id, 'client' => $client]);
    }
}