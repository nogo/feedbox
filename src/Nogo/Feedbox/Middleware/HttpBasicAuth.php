<?php
namespace Nogo\Feedbox\Middleware;

class HttpBasicAuth extends \Slim\Middleware
{
    /**
     * @var array
     */
    protected $credentials;
    /**
     * @var string
     */
    protected $realm;
    /**
     * @var string
     */
    protected $algorithm;

    /**
     * Constructor
     *
     * @param   array  $credentials An array of usernames and passwords
     * @param   string $realm       The HTTP Authentication realm
     * @param   string $algorithm    Password hash algorithm
     * @return  void
     */
    public function __construct($credentials, $realm = 'Protected Area', $algorithm = 'md5')
    {
        $this->credentials = $credentials;
        $this->realm = $realm;
        $this->algorithm = $algorithm;
    }

    /**
     * Call
     *
     * This method will check the HTTP request headers for previous authentication. If
     * the request has already authenticated, the next middleware is called. Otherwise,
     * a 401 Authentication Required response is returned to the client.
     *
     * @return void
     */
    public function call()
    {
        $req = $this->app->request();
        $res = $this->app->response();

        $reqUser = $req->headers('PHP_AUTH_USER');
        if ($this->algorithm == 'plaintext') {
            $reqPass = $req->headers('PHP_AUTH_PW');
        } else {
            $reqPass = hash($this->algorithm, $req->headers('PHP_AUTH_PW'));
        }

        if ($reqUser && $reqPass && isset($this->credentials[$reqUser]) && $this->credentials[$reqUser] === $reqPass) {
            $this->next->call();
        } else {
            $res->status(401);
            $res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm));
        }
    }
}