<?php
namespace Nogo\Feedbox\Repository;

class Item extends AbstractRepository
{
    protected $table = 'items';
    protected $fields = ['source_id', 'read', 'starred', 'title', 'content', 'uid', 'uri'];

    public function getFields()
    {
        return $this->fields;
    }

    public function fetchAllWithFilter(array $filter = array(), $count = false)
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();

        $select
            ->from($this->getTable());

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

    public function getTable()
    {
        return $this->table;
    }

    public function countUnread(array $sourceIds = array())
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['count(*)'])
            ->from($this->table)
            ->where('read IS NULL');

        $bind = [];
        if (!empty($sourceIds)) {
            $select->where('source_id IN (:source_id)');
            $bind['source_id'] = $sourceIds;
        }

        return $result = $this->connection->fetchValue($select, $bind);

    }
}