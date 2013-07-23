<?php

namespace Nogo\Feedbox\Repository;

class Source extends AbstractUserAwareRepository
{
    const ID = 'id';
    const TABLE = 'sources';
    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'user_id' => FILTER_VALIDATE_INT,
        'name' => FILTER_SANITIZE_STRING,
        'uri' => FILTER_VALIDATE_URL,
        'icon' => FILTER_UNSAFE_RAW,
        'active' => FILTER_VALIDATE_BOOLEAN,
        'unread' => FILTER_VALIDATE_INT,
        'tag_id' => FILTER_VALIDATE_INT,
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

    public function withRelations(array $entity)
    {
        return $entity;
    }

    public function validate(array $entity)
    {
        return filter_var_array($entity, $this->filter, false);
    }

    public function findAllActiveWithUri()
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['*'])
            ->from($this->tableName())
            ->where("active = 1 AND uri IS NOT NULL");

        $result = $this->connection->fetchAll($select);
        if (!empty($result)) {
            $result = $this->withRelations($result);
        }

        return $result;
    }

    public function countTagUnread(array $tagIds = array())
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['SUM(unread)'])
            ->from($this->tableName());

        $bind = [];
        if (!empty($tagIds)) {
            $select->where('tag_id IN (:tag_id)');
            $bind['tag_id'] = $tagIds;
        }

        return $this->connection->fetchValue($select, $bind);
    }
}