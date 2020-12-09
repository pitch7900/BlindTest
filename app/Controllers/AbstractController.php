<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\ServerRequest;
use Slim\Routing\RouteContext;

/**
 * This abstract class defines methods and properties used by all controllers.
 *
 * @package App\Controllers
 */
abstract class AbstractController {
    protected $container;
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(LoggerInterface::class);
    }
    
    /**
     * getUrlFor - Return url for named route
     *
     * @param  ServerRequest $request
     * @param  string $route
     * @return string
     */
    public function getUrlFor(ServerRequest $request,string $route):string {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();                                                                                                             
        $UrlFor = $routeParser->urlFor($route);
        return $UrlFor;
    }
}
