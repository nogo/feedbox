<?php
namespace Nogo\Feedbox\Repository;

class Setting extends AbstractRepository
{
    const ID = 'id';
    const TABLE = 'settings';

    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'key' => FILTER_SANITIZE_STRING,
        'value' => FILTER_SANITIZE_STRING,
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

    public function tableName()
    {
        return self::TABLE;
    }

    public function validate(array $entity)
    {
        return filter_var_array($entity, $this->filter, false);
    }

    public function withRelations(array $entities)
    {
        return $entities;
    }
}