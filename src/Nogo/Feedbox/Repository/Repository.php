<?php
namespace Nogo\Feedbox\Repository;

use Aura\Sql\Connection\AbstractConnection;

/**
 * Class Repository
 * @package Nogo\Feedbox\Repository
 */
interface Repository {

    public function __construct(AbstractConnection $connection);

    /**
     * @return string table identifier
     */
    public function identifier();

    /**
     * @return string table name
     */
    public function tableName();

    /**
     * Add relations to entity.
     *
     * @param array $entity can be one entity array or an array of entities
     * @return array
     */
    public function withRelations(array $entity);

    /**
     * Validate data.
     *
     * @param array $data
     * @return array
     */
    public function validate(array $data);

    /**
     * Find one entity by id.
     *
     * @param $id
     * @return array | boolean
     */
    public function find($id);

    /**
     * Find one entity by name and value.
     *
     * @param $name
     * @param $value
     * @return array | boolean
     */
    public function findBy($name, $value);

    /**
     * Find all entities.
     *
     * @return array | boolean
     */
    public function findAll();

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function findAllBy($name, $value);

    /**
     * Insert or update entity.
     *
     * @param array $entity
     * @return int last insert id or updated row count
     */
    public function persist(array $entity);

    /**
     * Delete entity.
     *
     * @param $id
     * @return int deleted row count
     */
    public function remove($id);


}