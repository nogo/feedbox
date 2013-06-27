<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Repository\Item as ItemRepository;
use Nogo\Feedbox\Repository\Repository;

/**
 * Class Items
 * @package Nogo\Feedbox\Controller
 */
class Items extends AbstractRestController
{
    /**
     * @var ItemRepository
     */
    protected $repository;
    /**
     * @var array
     */
    protected $fields = [
        'id' => [
            'read' => true,
            'write' => false
        ],
        'source_id' => [
            'read' => true,
            'write' => false
        ],
        'read' => [
            'read' => true,
            'write' => true
        ],
        'starred' => [
            'read' => true,
            'write' => true
        ],
        'title' => [
            'read' => true,
            'write' => false
        ],
        'pubdate' => [
            'read' => true,
            'write' => false
        ],
        'content' => [
            'read' => true,
            'write' => false
        ],
        'uid' => [
            'read' => true,
            'write' => false
        ],
        'uri' => [
            'read' => true,
            'write' => false
        ],
        'created_at' => [
            'read' => true,
            'write' => false
        ],
        'updated_at' => [
            'read' => true,
            'write' => false
        ]
    ];
    protected $allowed_params = ['mode', 'page', 'limit', 'sortby', 'source', 'tag', 'search'];

    public function enable()
    {
        $this->app->get('/items', array($this, 'listAction'));
        $this->app->get('/items/:id', array($this, 'getAction'))->conditions(['id' => '\d+']);
        $this->app->post('/items', array($this, 'postAction'));
        $this->app->put('/items/:id', array($this, 'putAction'))->conditions(['id' => '\d+']);
        $this->app->delete('/items/:id', array($this, 'deleteAction'))->conditions(['id' => '\d+']);

        $this->app->put('/read', array($this, 'readAction'));
    }

    public function getApiDefinition()
    {
        return $this->fields;
    }

    public function listAction()
    {
        $params = $this->getParameter($this->allowed_params);

        $result = $this->getRepository()->fetchAllWithFilter($params, true);
        $this->app->response()->header('X-Items-Total', $result[0]['count(*)']);
        $result = $this->getRepository()->fetchAllWithFilter($params);

        $readable = $this->readableFields();

        $output = [];
        foreach ($result as $data) {
            $output[] = $this->serializeData($data, $readable);
        }

        $this->renderJson($output);
    }

    /**
     * Item repository
     *
     * @param AbstractConnection $connection
     *
     * @return ItemRepository|Repository
     */
    public function getRepository(AbstractConnection $connection = null)
    {
        if ($this->repository == null) {
            if ($connection == null) {
                $connection = $this->connection;
            }
            $this->repository = new ItemRepository($connection);
        }
        return $this->repository;
    }

    public function readAction()
    {
        $json = trim($this->app->request()->getBody());
        if (empty($json)) {
            $this->render('Data not valid', 400);
            return;
        }

        $request_data = json_decode($json, true);
        if (!is_array($request_data)) {
            $this->render('Data not valid', 400);
            return;
        }

        $dt = date('Y-m-d H:i:s');

        $updated_items = array();
        foreach ($request_data as $id) {
            $id = intval($id);
            $item = $this->getRepository()->fetchOneById($id);
            if ($item !== false) {
                $item['read'] = $dt;
                $item['updated_at'] = $dt;
                $this->getRepository()->persist($item);
                $updated_items[] = $item;
            }
        }

        $this->renderJson($updated_items);
    }
}