<?php
namespace Nogo\Feedbox\Repository;

/**
 * Class Item
 * @package Nogo\Feedbox\Repository
 */
class Item extends AbstractRepository
{
    const ID = 'id';
    const TABLE = 'items';

    protected $filter = array(
        'id' => FILTER_VALIDATE_INT,
        'source_id' => FILTER_VALIDATE_INT,
        'read' => array(
            'filter' => FILTER_CALLBACK,
            'options' => array('Nogo\Feedbox\Helper\Validator', 'datetime')
        ),
        'starred' => FILTER_VALIDATE_BOOLEAN,
        'title' => FILTER_SANITIZE_STRING,
        'pubdate' => array(
            'filter' => FILTER_CALLBACK,
            'options' => array('Nogo\Feedbox\Helper\Validator', 'datetime')
        ),
        'content' => FILTER_UNSAFE_RAW,
        'uid' => FILTER_SANITIZE_STRING,
        'uri' => FILTER_VALIDATE_URL,
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

    public function addRelations(array $entities)
    {
        return $entities;
    }

    public function fetchAllWithFilter(array $filter = array(), $count = false)
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();

        $select
            ->from($this->tableName());

        if ($count) {
            $select->cols(['count(*)']);
        } else {
            $select->cols(['*']);
        }

        if (!$count) {
            if (isset($filter['page']) && isset($filter['limit'])) {
                $select->setPaging(intval($filter['limit']));
                $select->page(intval($filter['page']));
            }
        }

        $bind = [];
        foreach ($filter as $key => $value) {
            if ($value === null) {
                continue;
            }

            switch ($key) {
                case 'sortby':
                    switch ($value) {
                        case 'oldest':
                            $select->orderBy(['pubdate ASC', 'id ASC']);
                            break;
                        case 'newest':
                            $select->orderBy(['pubdate DESC', 'id DESC']);
                            break;
                    }
                    break;
                case 'source':
                    $value = intval($value);
                    if ($value) {
                        $bind['source'] = $value;
                        $select->where('source_id = :source');
                    }
                    break;
                case 'starred':
                    if ($value) {
                        $select->where('starred = 1');
                    } else {
                        $select->where('starred = 0');
                    }
                    break;
                case 'unread':
                    if ($value == 'true' || $value == 1) {
                        $select->where('read IS NULL');
                    } else {
                        $select->where('read IS NOT NULL');
                    }
                    break;
            }
        }

        return $this->connection->fetchAll($select, $bind);
    }


    public function countUnread(array $sourceIds = array())
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['count(*)'])
            ->from($this->tableName())
            ->where('read IS NULL');

        $bind = [];
        if (!empty($sourceIds)) {
            $select->where('source_id IN (:source_id)');
            $bind['source_id'] = $sourceIds;
        }

        return $result = $this->connection->fetchValue($select, $bind);

    }
}