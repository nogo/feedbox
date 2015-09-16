<?php

namespace Nogo\Feedbox\Endpoint;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Get implements EndpointInterface
{
    public function __construct()
    {
        // TODO dependencies: database
    }

    public function listAction()
    {
        // TODO filter and fetch collection
        // TODO return array
    }

    public function getAction()
    {
        // TODO find requested model
        // TODO fetch entity
        // TODO return array
    }

    /**
     * Dispatch action.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param array                                    $args     Arguments
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $response->getBody()->write('GET');

        // TODO find requested model
        // TODO dispatch to action
        // TODO serialize action result to response

        return $response;
    }
}
