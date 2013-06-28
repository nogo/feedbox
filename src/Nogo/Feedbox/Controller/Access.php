<?php
namespace Nogo\Feedbox\Controller;

use Nogo\Feedbox\Repository\Access as AccessRepository;

/**
 * Class Login
 *
 * @package Nogo\Feedbox\Controller
 */
class Access extends AbstractController
{
    public function enable()
    {
        $this->app->get('/login', array($this, 'signinAction'));
        $this->app->get('/logout', array($this, 'signoutAction'));
    }

    public function signinAction()
    {
        $this->renderJson(array('access' => 'grant'));
    }

    public function signoutAction()
    {
        $request_data = $this->jsonRequest();
        if (empty($request_data) || (!array_key_exists('user', $request_data) && !array_key_exists('client', $request_data))) {
            $this->renderJson(array('error' => 'User and client are missing'), 404);
        }

        $accessRepository = new AccessRepository($this->connection);
        $accessRepository->removeUserClient($request_data['user'], $request_data['client']);

        $this->renderJson(array('logout' => 'successful'));
    }
}