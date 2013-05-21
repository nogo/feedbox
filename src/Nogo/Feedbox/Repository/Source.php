<?php

namespace Nogo\Feedbox\Repository;

class Source extends AbstractRepository
{
    protected $table = 'sources';
    protected $fields = ['name', 'uri', 'active'];

    public function getTable()
    {
        return $this->table;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function fetchAllActiveWithUri()
    {
        return $this->connection->fetchAll('SELECT * FROM ' . $this->getTable() . ' WHERE active = 1 AND uri IS NOT NULL');
    }
}