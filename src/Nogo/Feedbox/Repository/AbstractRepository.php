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
        $result = $this->connection->fetchAll("SELECT * FROM " . $this->tableName());

        if (!empty($result)) {
            $result = $this->addRelations($result);
        }

        return $result;
    }

    public function fetchOneById($id)
    {
        return $this->fetchOneBy($this->identifier(), $id);
    }

    public function fetchOneBy($name, $value)
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['*'])
            ->from($this->tableName())
            ->where($name . ' = :' . $name);

        $result = $this->connection->fetchOne($select, [ $name => $value ]);

        if (!empty($result)) {
            $result = $this->addRelations($result);
        }

        return $result;
    }

    public function persist(array $entity)
    {
        $entity = $this->validate($entity);
        $id_key = $this->identifier();

        if (isset($entity[$id_key])) {
            $id = $entity[$id_key];
            unset($entity[$id_key]);

            return $this->connection->update($this->tableName(), $entity, $id_key . ' = :id', ['id' => $id]);
        } else {
            $this->connection->insert($this->tableName(), $entity);

            return $this->connection->lastInsertId();
        }
    }

    public function remove($id)
    {
        return $this->connection->delete($this->tableName(), $this->identifier() . ' = :id', ['id' => $id]);
    }
}