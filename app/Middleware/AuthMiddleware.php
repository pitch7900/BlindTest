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
    
    /**
     * __invoke
     *
     * @param  mixed $request
     * @param  mixed $handler
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface  $request, RequestHandlerInterface  $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->logger->debug("AuthMiddleware::__invoke() Called");
        $this->logger->debug("AuthMiddleware::__invoke() SessionID is : ".session_id() . " / " . $this->auth->getSessionId());
        if (!$this->auth->getAuthentified()) {
            $routeParser = $this->app->getRouteCollector()->getRouteParser();
            $signinroute = $routeParser->urlFor('auth.login');
            $this->logger->debug("AuthMiddleware::__invoke() Not authentified. Should redirect to login page " . $signinroute);
            $this->logger->debug("AuthMiddleware::__invoke() " . print_r($_SESSION,true));
            $response = $handler->handle($request)
            ->withHeader('Location',  $signinroute)
            ->withStatus(303);
            return $response;
        } else {
            $this->logger->debug("AuthMiddleware::__invoke() User ".$this->auth->getUserId()." Authentified. Continue");
        }

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
