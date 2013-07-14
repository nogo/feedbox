<?php
namespace Nogo\Feedbox\Repository;

class User  extends AbstractRepository
{
    const ID = 'id';
    const TABLE = 'users';

    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'name' => FILTER_SANITIZE_STRING,
        'password' => FILTER_UNSAFE_RAW,
        'salt' => FILTER_UNSAFE_RAW,
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