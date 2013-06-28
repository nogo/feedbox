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
        'user' => FILTER_SANITIZE_STRING,
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
     * Find one entity by name and value.
     *
     * @param $name
     * @param $value
     * @return array | boolean
     */
    public function findByUserClient($user, $client)
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['*'])
            ->from($this->tableName())
            ->where('user = :user AND client = :client');

        $result = $this->connection->fetchOne($select, [ 'user' => $user, 'client' => $client ]);

        if (!empty($result)) {
            $result = $this->withRelations($result);
        }

        return $result;
    }
}