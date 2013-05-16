<?php

namespace Nogo\Feed\Repository;

use Aura\Sql\Connection\AbstractConnection;

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

}