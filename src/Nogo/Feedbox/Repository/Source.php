<?php

namespace Nogo\Feedbox\Repository;

class Source extends AbstractRepository
{
    const ID = 'id';
    const TABLE = 'sources';

    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'name' => FILTER_SANITIZE_STRING,
        'uri' => FILTER_VALIDATE_URL,
        'icon' => FILTER_UNSAFE_RAW,
        'active' => FILTER_VALIDATE_BOOLEAN,
        'unread' => FILTER_VALIDATE_INT,
        'errors' => FILTER_SANITIZE_STRING,
        'period' => array(
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => array(
                'regexp' => '/everytime|hourly|daily|weekly|yearly/'
            )
        ),
        'last_update' => array(
            'filter' => FILTER_CALLBACK,
            'options' => array('Nogo\Feedbox\Helper\Validator', 'datetime')
        ),
        'created_at' => array(
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

    public function fetchAllActiveWithUri()
    {
        return $this->connection->fetchAll('SELECT * FROM ' . $this->tableName() . ' WHERE active = 1 AND uri IS NOT NULL');
    }
}