<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Api\Item as ItemApi;
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
     * @var ItemApi
     */
    protected $apiDefinition;

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

    /**
     * Api definition
     *
     * @return ItemApi
     */
    public function getApiDefinition()
    {
        if ($this->apiDefinition == null) {
            $this->apiDefinition = new ItemApi();
        }
        return $this->apiDefinition;
    }

    public function listAction()
    {
        $params = $this->getParameter($this->allowed_params);

        $result = $this->getRepository()->findAllFiltered($params, true);
        $this->app->response()->header('X-Items-Total', $result[0]['count(*)']);
        $result = $this->getRepository()->findAllFiltered($params);

        $readable = $this->getApiDefinition()->readableFields();

        $output = [];
        foreach ($result as $data) {
            $output[] = $this->getApiDefinition()->serializeData($data, $readable);
        }

        $this->renderJson($output);
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
            $item = $this->getRepository()->find($id);
            if ($item !== false) {
                $item['read'] = $dt;
                $item['updated_at'] = $dt;
                $this->getRepository()->persist($item);
                $updated_items[] = $item;
            }
        }

        $readable = $this->getApiDefinition()->readableFields();
        $output = [];
        foreach ($updated_items as $data) {
            $output[] = $this->getApiDefinition()->serializeData($data, $readable);
        }

        $this->renderJson($output);
    }
}