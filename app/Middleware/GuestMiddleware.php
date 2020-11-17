<?php

declare(strict_types=1);

namespace App\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Psr\Log\LoggerInterface;
use App\Authentication\Auth;


class GuestMiddleware
{

    /**
     * Logger interface
     * @var LoggerInterface;
     */
    private $logger;

    private $container;

    private $app;

    private $auth;


    /**
     * @param App    $app
     *
     * @return AuthMiddleware
     */
    public static function createFromContainer(App $app): self
    {
        return new self(
            $app
        );
    }

    /**
     * @param App    $app
     *
     * @return AuthMiddleware
     */
    public static function create(App $app): self
    {
        return new self(
            $app
        );
    }

    /**
     * @param App    $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->container = $this->app->getContainer();
        $this->auth = $this->container->get(Auth::class);
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->logger->debug("GuestMiddleware::__construct() Called");
        
    }

    public function __invoke(ServerRequestInterface  $request, RequestHandlerInterface  $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->logger->debug("GuestMiddleware::__invoke() Called");
        
        $response = $handler->handle($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        
        return $handler->handle($request);
    }
}
