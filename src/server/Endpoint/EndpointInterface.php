<?php

namespace Nogo\Feedbox\Endpoint;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface EndpointInterface
{
    /**
     * Dispatch action
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  array                                    $args     Arguments
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response, $args);
}
