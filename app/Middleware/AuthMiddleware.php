<?php

declare(strict_types=1);

namespace App\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Psr\Log\LoggerInterface;
use App\Config\Auth;


class AuthMiddleware
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
        $this->logger->debug("AuthMiddleware::__construct() Called");
        
    }

    public function __invoke(ServerRequestInterface  $request, RequestHandlerInterface  $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->logger->debug("AuthMiddleware::__invoke() Called");
        $this->logger->debug("AuthMiddleware::__invoke() SessionID is : ".session_id() . " / " . $this->auth->getSessionId());
        if (!$this->auth->check()) {
            $routeParser = $this->app->getRouteCollector()->getRouteParser();
            // $this->logger->debug("AuthMiddleware::__construct() ".print_r($routeParser,true));
            // die(var_dump($routeParser,true));
            $signinroute = $routeParser->urlFor('auth.login');
            $this->logger->debug("AuthMiddleware::__construct() Not authentified. Should redirect to login page " . $signinroute);
            $response = $handler->handle($request)
            ->withHeader('Location',  $signinroute)
            ->withStatus(302);
            return $response;
        } else {
            $this->logger->debug("AuthMiddleware::__construct() Authentified ");
        }

        die("Called invoke");

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
