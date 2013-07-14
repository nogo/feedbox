<?php
namespace Nogo\Feedbox\Feed;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\RequestException;

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
     * @var string
     */
    protected $error;

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * Set fetcher timeout
     *
     * @param $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = intval($timeout);
    }

    /**
     * Do get a request
     * @param $uri
     * @return null|string
     */
    public function get($uri)
    {
        try {
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

            $result = $response->getBody(true);
        } catch (RequestException $e) {
            $this->error = $e->getMessage();
            $result = null;
        }

        return $result;
    }

}