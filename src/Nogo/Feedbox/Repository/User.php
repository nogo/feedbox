<?php
namespace Nogo\Feedbox\Repository;

class User  extends AbstractRepository
{
    const ID = 'id';
    const TABLE = 'users';

    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'name' => FILTER_SANITIZE_STRING,
        'email' => FILTER_SANITIZE_EMAIL,
        'password' => FILTER_UNSAFE_RAW,
        'active' => FILTER_VALIDATE_BOOLEAN,
        'superadmin' => FILTER_VALIDATE_BOOLEAN,
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