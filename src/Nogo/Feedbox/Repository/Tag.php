<?php
namespace Nogo\Feedbox\Repository;

/**
 * Class Tag
 * @package Nogo\Feedbox\Repository
 */
class Tag extends AbstractRepository
{
    const ID = 'id';
    const TABLE = 'tags';

    /**
     * @var array input validation
     */
    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'name' => FILTER_SANITIZE_STRING,
        'color' => FILTER_SANITIZE_STRING,
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

    public function addRelations(array $entities)
    {
        if (array_key_exists('id', $entities)) {
            $entities = [$entities];
        }

        $result = [];
        foreach ($entities as $entity) {
            $tag['sources'] = $this->connection->fetchCol(
                "SELECT source_id FROM source_tags WHERE tag_id = :id",
                ['id' => $entity['id']]
            );
            $result[] = $entity;
        }
        return $result;
    }
}