<?php
namespace Nogo\Feedbox\Repository;

class Item extends AbstractRepository
{
    protected $table = 'items';
    protected $fields = ['source_id', 'read', 'title', 'content', 'uid', 'uri'];

    public function getFields()
    {
        return $this->fields;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function fetchAllWithFilter(array $filter = array())
    {
        /**
         * @var $select \Aura\Sql\Query\Select
         */
        $select = $this->connection->newSelect();
        $select->cols(['*'])
            ->from($this->getTable())
            ->orderBy(['updated_at DESC', 'id ASC'])
            ->setPaging(50)
            ->page(1);


        $bind = [];
        foreach ($filter as $key => $value) {
            switch ($key) {
                case 'unread':
                    if ($value) {
                        $select->where('read IS NULL');
                    } else {
                        $select->where('read IS NOT NULL');
                    }
                    break;
            }
        }

        return $this->connection->fetchAll($select);
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