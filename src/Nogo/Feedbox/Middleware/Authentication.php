<?php
namespace Nogo\Feedbox\Middleware;

use Nogo\Feedbox\Repository\Access;
use Nogo\Feedbox\Repository\User;

class Authentication extends \Slim\Middleware
{
    /**
     * @var Access
     */
    protected $accessRepository;

    /**
     * @var User
     */
    protected $userRepository;

    /**
     * @param Access $repository
     */
    public function setAccessRepository(Access $repository)
    {
        $this->accessRepository = $repository;
    }

    /**
     * @param User $repository
     */
    public function setUserRepository(User $repository)
    {
        $this->userRepository = $repository;
    }

    public function call()
    {
        $req = $this->app->request();
        $res = $this->app->response();

        $access_granted = false;

        $auth_user = filter_var($req->headers('Auth-User'), FILTER_SANITIZE_STRING);
        $auth_pass = filter_var($req->headers('Auth-Pass'), FILTER_SANITIZE_STRING);
        $auth_client = filter_var($req->headers('Auth-Client'), FILTER_SANITIZE_STRING);;

        // find corrensponding user
        $user = $this->userRepository->findBy('name', $auth_user);
        if (empty($user)) {
            $user = $this->checkConfigUser($auth_user, $auth_pass);
        }

        if (!empty($auth_pass)) {
            if (!empty($user) && $auth_client && password_verify($auth_pass, $user['password'])) {
                $token = md5(uniqid($auth_user . $auth_pass . microtime(), true));
                $expire = date('Y-m-d H:i:s', strtotime($this->app->config('login.expire')));
                $this->accessRepository->persist(['user_id' => $user['id'], 'client' => $auth_client, 'token' => $token, 'expire' =>  $expire]);
                $res['Next-Auth-Token'] = $token;
                $access_granted = true;
            }
        } else {
            $token = filter_var($req->headers('Auth-Token'), FILTER_SANITIZE_STRING);
            if (!empty($user) && !empty($token)) {
                $access = $this->accessRepository->findByUserClient($user['id'], $auth_client);
                if ($access !== false && $access['token'] === $token && strtotime($access['expire']) >= strtotime('now')) {
                    $access_granted = true;
                }
            }
        }

        if ($access_granted) {
            $this->app->user = $user;
            $this->next->call();
        } else {
            $res->status(401);
            $res->body('{"error": "Access denied."}');
        }
    }

    /**
     * Check user in config and insert them into database
     *
     * @param $auth_user
     * @param $auth_pass
     * @return array|null
     */
    protected function checkConfigUser($auth_user, $auth_pass)
    {
        $algorithm = $this->app->config('login.algorithm');
        $credentials = $this->app->config('login.credentials');

        $password = $auth_pass;
        if (!empty($algorithm) && $algorithm !== 'plaintext') {
            $password = hash($algorithm, $auth_pass);
        }

        if (!empty($auth_user) && isset($credentials[$auth_user]) && $credentials[$auth_user] === $password) {
            $user = [
                'name' => $auth_user,
                'password' => password_hash($auth_pass, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' =>  date('Y-m-d H:i:s')
            ];
            $user['id'] = $this->userRepository->persist($user);

            $this->app->db->query('UPDATE tags SET user_id = :user_id WHERE user_id = 0', ['user_id' => $user['id']]);
            $this->app->db->query('UPDATE items SET user_id = :user_id WHERE user_id = 0', ['user_id' => $user['id']]);
            $this->app->db->query('UPDATE sources SET user_id = :user_id WHERE user_id = 0', ['user_id' => $user['id']]);

            return $user;
        }

        return null;
    }

}