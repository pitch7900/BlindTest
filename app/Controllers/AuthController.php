<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\AbstractTwigController;
use App\Authentication\Auth;

class AuthController extends AbstractTwigController
{

    private $logger;

    private $auth;

    /**
     * __construct
     *
     * @param  mixed $twig
     * @param  mixed $logger
     * @return void
     */
    public function __construct(Twig $twig,LoggerInterface $logger, Auth $auth) {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->logger->debug("AuthController::_construct Constructor called");
        $this->auth=$auth;
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

    public function checkmail(Request $request, Response $response, array $args = []): Response {
        $uuid = $args['uuid'];
        $validation=$this->auth->validateEmail($uuid);
        if ($validation){
            $response = $response
            ->withHeader('Location', '/')
            ->withStatus(301);
            
        } else {
            $response=$this->render($response, 'auth/signin.twig');
        }
        return $response;
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
        $this->auth->signout();
        return $this->render($response, 'auth/signout.twig');
    }


    public function preferences(Request $request, Response $response, array $args = []): Response {
        $arguments['userid']=$this->auth->getUserId();
        $arguments['email']=$this->auth->getUserEmail();
        $arguments['nickname']=$this->auth->getUserNickName();
        return $this->render($response, 'user/preferences.twig',$arguments);
    }

    public function changepassword(Request $request, Response $response, array $args = []): Response {
 
        return $this->render($response, 'user/changepassword.twig');
    }

    public function resetpassword(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'auth/resetpassword.twig');
    }

    public function postresetpassword(Request $request, Response $response, array $args = []): Response {
        $password=$request->getParam('password');
        $uuid = $args['uuid'];
        $password = password_hash($password, PASSWORD_DEFAULT);
        $return  = $this->auth->resetPassword($uuid,$password);
        $response = $response
            ->withHeader('Location', '/')
            ->withStatus(301);
        return $response;
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

    public function postlogin(Request $request, Response $response, array $args = []): Response {
        $password=$request->getParam('password');
        $email=$request->getParam('email');
        
        $this->auth->checkPassword($email,$password);

        return $response
            ->withHeader('Location', '/')
            ->withStatus(301);

    }

    public function forgotpassword(Request $request, Response $response, array $args = []): Response {
        return $this->render($response, 'auth/forgotpassword.twig');
    }

    public function postforgotpassword(Request $request, Response $response, array $args = []): Response {
        
        $email=$request->getParam('email');
        $this->auth->sendResetPasswordLink($email);
        return $this->render($response, 'auth/validateemail.twig');
    }
    
    public function postsignin(Request $request, Response $response, array $args = []): Response {
        $password=$request->getParam('password');
        $email=$request->getParam('email');
        $password = password_hash($password, PASSWORD_DEFAULT);
        // die(var_dump($this->auth));
        $this->auth->addUser($email,$password);
        
        return $this->render($response, 'auth/validateemail.twig');
    }
    
}
