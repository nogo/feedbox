<?php
namespace Nogo\Feedbox\Repository;

use Aura\Sql\Connection\AbstractConnection;

abstract class AbstractRepository implements Repository
{
    /**
     * @var AbstractConnection
     */
    protected $connection;

    public function __construct(AbstractConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return AbstractConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function fetchAll()
    {
        return $this->connection->fetchAll("SELECT * FROM " . $this->tableName());
    }

    public function fetchOneById($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        return $this->connection->fetchOne("SELECT * FROM " . $this->tableName() . " WHERE id = :id", ['id' => $id]);
    }

    public function fetchOneBy($name, $value)
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['*'])
            ->from($this->tableName())
            ->where($name . '= :' . $name);

        return $this->connection->fetchOne($select, [$name => $value]);
    }

    public function persist(array $entity)
    {
        $entity = $this->validate($entity);

        if (isset($entity['id'])) {
            $id = $entity['id'];
            unset($entity['id']);

            return $this->connection->update($this->tableName(), $entity, 'id = :id', ['id' => $id]);
        } else {
            $this->connection->insert($this->tableName(), $entity);

            return $this->connection->lastInsertId();
        }
    }

    public function remove($id)
    {
        return $this->connection->delete($this->tableName(), 'id = :id', ['id' => $id]);
    }


}