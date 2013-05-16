<?php
namespace Nogo\Feed\Repository;

use Aura\Sql\Connection\AbstractConnection;

interface Repository {

    public function __construct(AbstractConnection $connection);

    public function getTable();
    public function getFields();

    public function fetchAll();
    public function fetchOneById($id);

    public function persist(array $entity);
    public function remove($id);
}