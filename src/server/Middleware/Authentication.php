<?php

namespace Nogo\Feedbox\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Authentication
{
    private $app;

    public function __construct(\Slim\App $app)
    {
      $this->app = $app;
    }

    /**
     * Example middleware closure
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
      $response->getBody()->write('authenticate -> ');
      $response = $next($request, $response);
      $response->getBody()->write(' -> successfull');
      return $response;
    }
}
