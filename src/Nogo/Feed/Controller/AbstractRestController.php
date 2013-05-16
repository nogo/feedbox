<?php
namespace Nogo\Feed\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feed\Repository\Repository;

/**
 * Class AbstractRestController
 *
 * @package Nogo\Feed\Controller
 */
abstract class AbstractRestController extends AbstractController
{
    /**
     * Get Repository
     *
     * @param AbstractConnection $connection
     * @return Repository
     */
    abstract public function getRepository(AbstractConnection $connection = null);

    /**
     * GET /resource
     */
    public function listAction()
    {
        $result = $this->getRepository()->fetchAll();
        $this->renderJson($result);
    }

    /**
     * GET /resource/id
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

        $this->renderJson($result);
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

        $entity = [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $writable = $this->getRepository()->getFields();
        foreach ($writable as $field) {
            if (isset($request_data[$field])) {
                $entity[$field] = $request_data[$field];
            }
        }

        if (!empty($entity)) {
            $entity['id'] = $this->getRepository()->persist($entity);
            $this->renderJson($entity);
        }
    }

    /**
     * PUT /resource/id
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

        $dt = date('Y-m-d H:i:s', strtotime('now'));

        $entity = [
            'id' => $result['id'],
            'updated_at' => $dt
        ];
        $writable = $this->getRepository()->getFields();
        foreach ($writable as $field) {
            if (array_key_exists($field, $request_data) && $request_data[$field] != $result[$field]) {
                $entity[$field] = $request_data[$field];
                $result[$field] = $request_data[$field];
            }
        }

        if (count($entity) > 2) {
            $this->getRepository()->persist($entity);
            $result['updated_at'] = $dt;
        }

        $this->renderJson($result);
    }

    /**
     * DELETE /resource/id
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
}