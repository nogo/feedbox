<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Hampel\Json\Json;
use Hampel\Json\JsonException;
use Nogo\Feedbox\Api\AbstractApi;
use Nogo\Feedbox\Repository\Repository;

/**
 * Class AbstractRestController
 *
 * @package Nogo\Feedbox\Controller
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
     * Get Repository
     *
     * @param AbstractConnection $connection
     * @return AbstractApi
     */
    abstract public function getApiDefinition();

    /**
     * GET /resource
     */
    public function listAction()
    {
        $result = $this->getRepository()->findAll();

        $output = [];
        foreach ($result as $data) {
            $output[] = $this->getApiDefinition()->serializeData($data);
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
        $result = $this->getRepository()->find($id);

        if ($result === false) {
            $this->render('Not found', 404);
            return;
        }

        $output = $this->getApiDefinition()->serializeData($result);
        $this->renderJson($output);
    }

    /**
     * POST /resource
     */
    public function postAction()
    {
        $request_data = $this->jsonRequest();
        if (empty($request_data)) {
            $this->render('Data not valid', 400);
            return;
        }

        $entity = $this->getApiDefinition()->deserializeData($request_data);

        if (!empty($entity)) {
            $entity['created_at'] = date('Y-m-d H:i:s');
            $entity['updated_at'] = $entity['created_at'];
            $entity['id'] = $this->getRepository()->persist($entity);

            $result = $this->getRepository()->find($entity['id']);
            $output = $this->getApiDefinition()->serializeData($result);
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
        $result = $this->getRepository()->find($id);

        if ($result === false) {
            $this->render('Not found', 404);
            return;
        }

        $request_data = $this->jsonRequest();
        if (empty($request_data)) {
            $this->render('Data not valid', 400);
            return;
        }

        $entity = $this->getApiDefinition()->deserializeData($request_data);

        if (!empty($entity)) {
            $entity['id'] = $result['id'];
            $entity['updated_at'] = date('Y-m-d H:i:s');
            $this->getRepository()->persist($entity);

            // find entity
            $result = $this->getRepository()->find($id);
        }

        $output = $this->getApiDefinition()->serializeData($result);
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
}