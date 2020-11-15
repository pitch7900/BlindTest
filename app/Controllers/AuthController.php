<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\AbstractTwigController;

class AuthController extends AbstractTwigController
{

    private $logger;

    /**
     * __construct
     *
     * @param  mixed $twig
     * @param  mixed $logger
     * @return void
     */
    public function __construct(Twig $twig,LoggerInterface $logger) {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->logger->debug("AuthController::_construct Constructor called");
    }

         
    /**
     * signin
     * Return the "Signin" view 
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function signin(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'auth/signin.twig');
    }

    /**
     * signout
     * Return the "signout" view 
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function signout(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'auth/signout.twig');
    }

    /**
     * login
     * Return the "login" view 
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function login(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'auth/login.twig');
    }

    public function checklogin(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'auth/login.twig');
    }
    public function forgotpassword(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'auth/forgotpassword.twig');
    }


    
}
