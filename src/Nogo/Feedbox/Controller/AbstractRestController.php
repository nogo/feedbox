<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Repository\Repository;

/**
 * Class AbstractRestController
 *
 * @package Nogo\Feedbox\Controller
 */
abstract class AbstractRestController extends AbstractController
{
    /**
     * @var array
     */
    private $readable = array();

    /**
     * @var array
     */
    private $writable = array();

    /**
     * Get Repository
     *
     * @param AbstractConnection $connection
     * @return Repository
     */
    abstract public function getRepository(AbstractConnection $connection = null);

    /**
     * Api definition
     *
     * @return array
     */
    abstract public function getApiDefinition();

    /**
     * GET /resource
     */
    public function listAction()
    {
        $result = $this->getRepository()->fetchAll();

        $readable = $this->readableFields();
        $output = [];
        foreach($result as $data) {
            $output[] = $this->serializeData($data, $readable);
        }

        $this->renderJson($output);
    }

    /**
     * GET /resource/:id
     *
     * @param $id
     */
    public function getAction($id)
    {
        $result = $this->getRepository()->fetchOneById($id);

        if ($result === false) {
            $this->render('Not found', 404);
            return;
        }

        $output = $this->serializeData($result, $this->readableFields());
        $this->renderJson($output);
    }

    /**
     * POST /resource
     */
    public function postAction()
    {
        $json = trim($this->app->request()->getBody());
        if (empty($json)) {
            $this->render('Data not valid', 400);
            return;
        }

        $request_data = json_decode($json, true);

        $entity = $this->deserializeData($request_data, $this->writableFields());

        if (!empty($entity)) {
            $entity['created_at'] = date('Y-m-d H:i:s');
            $entity['updated_at'] = $entity['created_at'];
            $entity['id'] = $this->getRepository()->persist($entity);

            $result = $this->getRepository()->fetchOneById($entity['id']);
            $output = $this->serializeData($result, $this->readableFields());
            $this->renderJson($output);
        } else {
            $this->render('Data not valid', 400);
        }
    }

    /**
     * PUT /resource/:id
     *
     * @param $id
     */
    public function putAction($id)
    {
        $result = $this->getRepository()->fetchOneById($id);

        if ($result === false) {
            $this->render('Not found', 404);
            return;
        }

        $json = trim($this->app->request()->getBody());
        if (empty($json)) {
            $this->render('Data not valid', 400);
            return;
        }

        $request_data = json_decode($json, true);

        $writable = $this->writableFields();
        $entity = $this->deserializeData($request_data, $writable);

        if (!empty($entity)) {
            $entity['id'] = $result['id'];
            $entity['updated_at'] = date('Y-m-d H:i:s');
            $this->getRepository()->persist($entity);

            // fetch entity
            $result = $this->getRepository()->fetchOneById($id);
        }

        $output = $this->serializeData($result, $this->readableFields());
        $this->renderJson($output);
    }

    /**
     * DELETE /resource/:id
     *
     * @param $id
     */
    public function deleteAction($id)
    {
        $deleted = $this->getRepository()->remove($id);
        if ($deleted <= 0) {
            $this->render('Not found', 404);
        } else {
            $this->render('Item deleted');
        }
    }

    /**
     * Array of readable fields defined by api
     * @return array
     */
    protected function readableFields()
    {
        if (empty($this->readable)) {
            $api = $this->getApiDefinition();
            $this->readable = [];
            foreach($api as $key => $param) {
                if (isset($param['read']) && $param['read']) {
                    $name = $key;
                    if (array_key_exists('name', $param)) {
                        if (!empty($param['name'])) {
                            $name = $param['name'];
                        }
                    }
                    $this->readable[$key] = $name;
                }
            }
        }

        return $this->readable;
    }

    /**
     * Array of writable fields defined by api
     * @return array
     */
    protected function writableFields()
    {
        if (empty($this->writable)) {
            $api = $this->getApiDefinition();
            $this->writable = [];
            foreach($api as $key => $param) {
                if (isset($param['write']) && $param['write']) {
                    $name = $key;
                    if (array_key_exists('name', $param)) {
                        if (!empty($param['name'])) {
                            $name = $param['name'];
                        }
                    }
                    $this->writable[$key] = $name;
                }
            }
        }

        return $this->writable;
    }

    /**
     * Deserialize data array with api definition
     *
     * @param array $data [ name => value ]
     * @param array $api [ key => name ]
     * @param array $result
     * @return array [ key => value ]
     */
    protected function deserializeData(array $data, array $api, array $result = [])
    {
        foreach ($api as $key => $name) {
            if (array_key_exists($name, $data)) {
                if (array_key_exists($key, $result)) {
                    if ($data[$name] != $result[$key]) {
                        $result[$key] = $data[$name];
                    }
                } else {
                    $result[$key] = $data[$name];
                }
            }
        }
        return $result;
    }

    /**
     * Serialize data array with api definition
     *
     * @param array $data [ key => value ]
     * @param array $api [ key => name ]
     * @param array $result
     * @return array [ name => value ]
     */
    protected function serializeData(array $data, array $api, array $result = [])
    {
        foreach ($api as $key => $name) {
            if (array_key_exists($key, $data)) {
               $result[$name] = $data[$key];
            }
        }
        return $result;
    }
}