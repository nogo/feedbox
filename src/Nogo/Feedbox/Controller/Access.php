<?php
namespace Nogo\Feedbox\Controller;

use Nogo\Feedbox\Repository\Access as AccessRepository;
use Nogo\Feedbox\Repository\User as UserRepository;

/**
 * Class Login
 *
 * @package Nogo\Feedbox\Controller
 */
class Access extends AbstractController
{
    public function enable()
    {
        $this->app->post('/login', array($this, 'signinAction'));
        $this->app->post('/logout', array($this, 'signoutAction'));
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

        $userRepository = new UserRepository($this->app->db);
        $user = $userRepository->findBy('name', $request_data['user']);
        if (!$user) {
            $this->renderJson(array('error' => 'User not found'), 404);
        }

        $accessRepository = new AccessRepository($this->app->db);
        $accessRepository->removeUserClient($user['id'], $request_data['client']);

        $this->renderJson(array('logout' => 'successful'));
    }
}