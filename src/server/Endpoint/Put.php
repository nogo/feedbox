<?php

namespace Nogo\Feedbox\Endpoint;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Put implements EndpointInterface
{
    public function __construct()
    {
    }

    public function putAction($identifier, $data)
    {
        // TODO find entity by id
        // TODO fill with data
        // TODO return entity array
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
        $response->getBody()->write('PUT');

        // TODO find requested model
        // TODO dispatch to action
        // TODO serialize action result to response

        return $response;
    }
}
