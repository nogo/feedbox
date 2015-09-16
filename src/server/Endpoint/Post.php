<?php

namespace Nogo\Feedbox\Endpoint;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Post implements EndpointInterface
{
    public function __construct()
    {
    }

    public function postAction($data)
    {
        // TODO create entity
        // TODO fill with data
        // TODO add user
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
        $response->getBody()->write('POST');

        // TODO find requested model
        // TODO dispatch to action
        // TODO serialize action result to response

        return $response;
    }
}
