<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\AbstractTwigController;
use App\Authentication\Auth;
use App\Authentication\UUID;
use App\Database\User;
use Carbon\Carbon;

/**
 * AuthController
 * @author : Pierre Christensen
 */
class AuthController extends AbstractTwigController
{

    /**
     * logger
     *
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * auth
     *
     * @var App\Authentication\Auth
     */
    private $auth;

    /**
     * __construct
     *
     * @param  mixed $twig
     * @param  mixed $logger
     * @return void
     */
    public function __construct(Twig $twig, LoggerInterface $logger, Auth $auth)
    {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->logger->debug("AuthController::_construct Constructor called");
        $this->auth = $auth;
    }


    /**
     * signin
     * Return the "Signin" view 
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function signin(Request $request, Response $response, array $args = []): Response
    {
        return $this->render($response, 'auth/signin.twig');
    }

    /**
     * checkmail : Page to handle the email validation token sent to user
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function checkmail(Request $request, Response $response, array $args = []): Response
    {
        $uuid = $args['uuid'];
        $validation = $this->auth->validateEmail($uuid);
        if ($validation) {
            $response = $response
                ->withHeader('Location', '/')
                ->withStatus(303);
        } else {
            $response = $this->render($response, 'auth/signin.twig');
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
    public function signout(Request $request, Response $response, array $args = []): Response
    {
        $this->auth->signout();
        return $this->render($response, 'auth/signout.twig');
    }


    /**
     * preferences : Display the user's preference page
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function preferences(Request $request, Response $response, array $args = []): Response
    {
        $arguments['userid'] = $this->auth->getUserId();
        $arguments['email'] = $this->auth->getUserEmail();
        $arguments['nickname'] = $this->auth->getUserNickName();
        return $this->render($response, 'user/preferences.twig', $arguments);
    }

    /**
     * postpreferences : Change user's preferences
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function postpreferences(Request $request, Response $response, array $args = []): Response
    {
        $nickname = $request->getParam('nickname');
        $this->auth->setNickname($nickname);
        return $this->withRedirect($response, "/user/preferences");
    }

    /**
     * changepassword : Display the Change Password page
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function changepassword(Request $request, Response $response, array $args = []): Response
    {     
        return $this->render($response, 'user/changepassword.twig');
    }

    /**
     * postchangepassword : Action to handle on change password request
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function postchangepassword(Request $request, Response $response, array $args = []): Response
    {
        $password = $request->getParam('password');
        $password = password_hash($password, PASSWORD_DEFAULT);
        $this->auth->changePassword($password);
        return $this->withRedirect($response, "/");
    }

    /**
     * resetpassword : Display the reset password page
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function resetpassword(Request $request, Response $response, array $args = []): Response
    {
        return $this->render($response, 'auth/resetpassword.twig');
    }

    /**
     * postresetpassword : Handle the post reset password passed from the reset password page
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function postresetpassword(Request $request, Response $response, array $args = []): Response
    {
        $password = $request->getParam('password');
        $uuid = $args['uuid'];
        $uuidvalidity = UUID::is_valid($uuid);
        $uuidexist = $this->auth->checkUUIDpasswordreset($uuid);
        $this->logger->debug("AuthController:postresetpassword UUID check validity is " . UUID::is_valid($uuid));
        if ($uuidvalidity && $uuidexist) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $this->auth->resetPassword($uuid, $password);
        }
        $response = $response
            ->withHeader('Location', '/')
            ->withStatus(303);
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
    public function login(Request $request, Response $response, array $args = []): Response
    {
        return $this->render($response, 'auth/login.twig');
    }

    public function postlogin(Request $request, Response $response, array $args = []): Response
    {
        $password = $request->getParam('password');
        $email = $request->getParam('email');

        $this->auth->checkPassword($email, $password);

        return $response
            ->withHeader('Location', '/')
            ->withStatus(303);
    }

    /**
     * forgotpassword : Display the Forgot password page
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function forgotpassword(Request $request, Response $response, array $args = []): Response
    {
        return $this->render($response, 'auth/forgotpassword.twig');
    }

    /**
     * postforgotpassword : Handle action for Forgot password (Send an email). 
     * This action has a volontary 2s delay to avoir flood
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function postforgotpassword(Request $request, Response $response, array $args = []): Response
    {
        $email = $request->getParam('email');
        sleep(2);
        $this->auth->sendResetPasswordLink($email);
        return $this->render($response, 'auth/validateemail.twig');
    }

    /**
     * postsignin : Handle the signin page (Create a user) and send an email
     * This action has a volontary 2s delay to avoir flood
     * Display the confirmation page when the email is sent
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function postsignin(Request $request, Response $response, array $args = []): Response
    {
        $password = $request->getParam('password');
        $email = $request->getParam('email');
        $nickname = $request->getParam('nickname');
        $password = password_hash($password, PASSWORD_DEFAULT);
        sleep(2);
        $this->auth->addUser($email, $password,$nickname);
        $arguments['approval'] = false;
        if (strcmp($_ENV['REGISTRATION_REQUIRE_APPROVAL'], "true") == 0) {
            $arguments['approval'] = true;
        }
        return $this->render($response, 'auth/validateemail.twig', $arguments);
    }
    
    /**
     * validateemail : Aknowledge the email sent to user
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function validateemail(Request $request, Response $response, array $args = []): Response
    {
        $uuid = $args['uuid'];
        $user = User::where([
            ['approvaleuuid', '=', $uuid]
        ])->first();
        if (!is_null($user)) {
            $arguments['email'] = $user->email;
            $user->adminapproved = true;
            $timestamp =  Carbon::createFromTimestamp(time() + 15 * 60);
            $user->emailchecklinktimeout = $timestamp;
            $user->save();
            $this->auth->sendValidationEmail($user->email, $user->emailchecklink);
            return $this->render($response, 'auth/validatoremail.twig', $arguments);
        }
        return $this->withRedirect($response, '/');
    }
}
