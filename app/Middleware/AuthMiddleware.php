<?php

declare(strict_types=1);

namespace App\Middleware;


use Slim\App;
use App\Authentication\Authentication;
use Slim\Psr7\Factory\ResponseFactory;
use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends AbstractController
{


    private $app;


    /**
     * @param App    $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $container = $this->app->getContainer();
        $this->auth = $container->get(Authentication::class);
    

        parent::__construct($container);

        $this->logger->debug("AuthMiddleware::__construct() Called");
    }

    /**
     * checkAuthentified : return true if user is well authentified
     *
     * @return bool
     */
    private function checkAuthentified(): bool
    {
        if (!Authentication::IsAuthentified()) {

            $this->logger->debug("AuthMiddleware::checkAuthentified() Not authentified. Should redirect to login page ");
            $this->logger->debug("AuthMiddleware::checkAuthentified() " . print_r($_SESSION, true));
            return false;
        } else {
            $this->logger->debug("AuthMiddleware::checkAuthentified() User " . Authentication::getUserId() . " Authentified. Continue");
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
        $this->logger->debug("AuthMiddleware::__invoke() SessionID is : " . session_id());
        if (!$this->checkAuthentified()) {
            $responseFactory = new ResponseFactory();
            $response = $responseFactory->createResponse();
            return $this->withRedirect($response,$this->getLoginPath(),303);
        }

        $response = $handler->handle($request);
        return $response;
        
    }
}
