<?php
namespace Nogo\Feedbox\Middleware;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Repository\Access;

class Authentication extends \Slim\Middleware
{
    /**
     * @var Access
     */
    protected $accessRepository;

    /**
     * @var array
     */
    protected $credentials;

    /**
     * @var string
     */
    protected $algorithm;

    /**
     * Constructor
     *
     * @param AbstractConnection $connection
     * @param array  $credentials An array of usernames and passwords
     * @param string $algorithm    Password hash algorithm
     * @return void
     */
    public function __construct(AbstractConnection $connection, $credentials, $algorithm = 'md5')
    {
        $this->accessRepository = new Access($connection);
        $this->credentials = $credentials;
        $this->algorithm = $algorithm;
    }


    public function call()
    {
        $req = $this->app->request();
        $res = $this->app->response();

        $error = false;

        $user = $req->headers('AUTH_USER');
        $client = $req->headers('AUTH_CLIENT');
        $pass = $req->headers('AUTH_PASS');

        if (!empty($pass)) {
            if ($this->algorithm !== 'plaintext') {
                $pass = hash($this->algorithm, $pass);
            }

            if ($user && $client && isset($this->credentials[$user]) && $this->credentials[$user] === $pass) {
                $token = md5(uniqid($user . $pass . microtime(), true));
                $expire = date('Y-m-d H:i:s', strtotime($this->app->config('login.expire')));
                $this->accessRepository->persist(['user' => $user, 'client' => $client, 'token' => $token, 'expire' =>  $expire]);
                $res['NEXT_AUTH_TOKEN'] = $token;
            } else {
                $error = true;
            }
        } else {
            $token = $req->headers('AUTH_TOKEN');
            if (!empty($token)) {
                $access = $this->accessRepository->findByUserClient($user, $client);
                if ($access !== false && $access['token'] === $token && strtotime($access['expire']) >= strtotime('now')) {
                    // TODO check this later
//                    $access['expire'] = date('Y-m-d H:i:s', strtotime($this->app->config('login.expire')));
//                    $this->accessRepository->persist($access);
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
        }

        if ($error) {
            $res->status(401);
            $res->body('{"error": "Access denied."}');
        } else {
            $this->next->call();
        }
    }

}