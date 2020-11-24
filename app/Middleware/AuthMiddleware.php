<?php

declare(strict_types=1);

namespace App\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Psr\Log\LoggerInterface;
use App\Authentication\Auth;
use Slim\Psr7\Factory\ResponseFactory;

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
     * checkAuthentified : return true if user is well authentified
     *
     * @return bool
     */
    private function checkAuthentified(): bool
    {
        if (!$this->auth->getAuthentified()) {

            $this->logger->debug("AuthMiddleware::checkAuthentified() Not authentified. Should redirect to login page ");
            $this->logger->debug("AuthMiddleware::checkAuthentified() " . print_r($_SESSION, true));
            return false;
        } else {
            $this->logger->debug("AuthMiddleware::checkAuthentified() User " . $this->auth->getUserId() . " Authentified. Continue");
            return true;
        }
    }

    /**
     * getLoginPath : Return the login path
     *
     * @return string
     */
    private function getLoginPath(): string
    {
        $routeParser = $this->app->getRouteCollector()->getRouteParser();
        $signinroute = $routeParser->urlFor('auth.login');
        return $signinroute;
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
        $this->logger->debug("AuthMiddleware::__invoke() Called");
        $this->logger->debug("AuthMiddleware::__invoke() SessionID is : " . session_id() . " / " . $this->auth->getSessionId());
        
        if (!$this->checkAuthentified()) {
            $this->auth->setOriginalRequestedPage($_SERVER['REQUEST_URI']);
            $this->logger->debug("AuthMiddleware::__invoke() Original Requested page is : ". $this->auth->getOriginalRequestedPage());
            //Create a new response to break the current flow
            $responseFactory = new ResponseFactory();
            $response = $responseFactory->createResponse();
            return $response->withHeader('Location',  $this->getLoginPath())
                ->withStatus(303);
        } else {
            $response = $handler->handle($request);
            return $response;
        }
    }
}
