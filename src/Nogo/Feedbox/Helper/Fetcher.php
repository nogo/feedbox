<?php
namespace Nogo\Feedbox\Helper;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

/**
 * Class Fetcher
 * @package Nogo\Feedbox\Helper
 */
class Fetcher
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $timeout = 10;

    /**
     * @param $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = intval($timeout);
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function get($uri)
    {
        /**
         * @var $request Request
         */
        $request = $this->client->get($uri, array(), array(
            'timeout' => $this->timeout
        ));

        /**
         * @var $resonse Response
         */
        $response = $request->send();

        return $response->getBody(true);
    }

}