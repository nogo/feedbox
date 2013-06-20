<?php
namespace Nogo\Feedbox\Repository;

use Aura\Sql\Connection\AbstractConnection;

interface Repository {

    public function __construct(AbstractConnection $connection);

    public function tableName();
    public function validate(array $data);

    public function fetchAll();
    public function fetchOneById($id);

    public function persist(array $entity);
    public function remove($id);
}