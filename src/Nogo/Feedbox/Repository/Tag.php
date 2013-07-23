<?php
namespace Nogo\Feedbox\Repository;

/**
 * Class Tag
 * @package Nogo\Feedbox\Repository
 */
class Tag extends AbstractUserAwareRepository
{
    const ID = 'id';
    const TABLE = 'tags';

    /**
     * @var array input validation
     */
    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'user_id' => FILTER_VALIDATE_INT,
        'name' => FILTER_SANITIZE_STRING,
        'color' => FILTER_SANITIZE_STRING,
        'unread' => FILTER_VALIDATE_INT,
        'created_at'  => array(
            'filter' => FILTER_CALLBACK,
            'options' => array('Nogo\Feedbox\Helper\Validator', 'datetime')
        ),
        'updated_at' => array(
            'filter' => FILTER_CALLBACK,
            'options' => array('Nogo\Feedbox\Helper\Validator', 'datetime')
        )
    );

    public function identifier()
    {
        return self::ID;
    }

    /**
     * @return string table name
     */
    public function tableName()
    {
        return self::TABLE;
    }

    /**
     * validate input
     *
     * @param array $entity
     * @return mixed
     */
    public function validate(array $entity)
    {
        return filter_var_array($entity, $this->filter, false);
    }

    public function withRelations(array $entities)
    {
        if (array_key_exists('id', $entities)) {
            $entities['sources'] = $this->connection->fetchCol(
                "SELECT id FROM sources WHERE tag_id = :id",
                ['id' => $entities['id']]
            );
            return $entities;
        } else {
            $result = [];
            foreach ($entities as $entity) {
                $entity['sources'] = $this->connection->fetchCol(
                    "SELECT id FROM sources WHERE tag_id = :id",
                    ['id' => $entity['id']]
                );
                $result[] = $entity;
            }
            return $result;
        }
    }

    public function remove($id)
    {
        return $this->connection->delete($this->tableName(), $this->identifier() . ' = :id', ['id' => $id]);
    }
}