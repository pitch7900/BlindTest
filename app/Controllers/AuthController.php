<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\ServerRequest as Request;
use App\Controllers\AbstractTwigController;
use App\Authentication\Auth;
use App\Authentication\UUID;
use App\Database\User;
use Carbon\Carbon;
use App\Authentication\Recaptcha;
use Psr\Container\ContainerInterface;


/**
 * AuthController
 * @author : Pierre Christensen
 */
class AuthController extends AbstractTwigController
{


    /**
     * recaptcha
     *
     * @var Recaptcha
     */
    private $recaptcha;

    /**
     * __construct
     *
     * @param  mixed $twig
     * @param  mixed $logger
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->recaptcha = $container->get(Recaptcha::class);
        $this->logger->debug("AuthController::__construct() Constructor called");  
        
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
        $arguments['recaptcha_api_key'] = $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'];
        return $this->render($response, 'auth/signin.twig', $arguments);
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
        $arguments['recaptcha_api_key'] = $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'];
        $validation = Auth::validateEmail($uuid);
        if ($validation) {
            // $response = $response
            //     ->withHeader('Location', $this->getUrlFor($request,"home"))
            //     ->withStatus(303);
                $response = $this->withRedirect($response, $this->getUrlFor($request,"home",303));
        } else {
            $response = $this->render($response, 'auth/signin.twig', $arguments);
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
        Auth::signout();
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
        $arguments['userid'] = Auth::getUserId();
        $arguments['email'] = Auth::getUserEmail();
        $arguments['nickname'] = Auth::getUserNickName();
        $this->logger->debug("AuthController::preferences() Page called");
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
        Auth::setNickname($nickname);
        

        return $this->withRedirect($response, $this->getUrlFor($request,"user.preferences"));
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
        Auth::changePassword($password);
        return $this->withRedirect($response, $this->getUrlFor($request,"home"));
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
        $arguments['recaptcha_api_key'] = $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'];
        return $this->render($response, 'auth/resetpassword.twig', $arguments);
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
        $uuidexist = Auth::checkUUIDpasswordreset($uuid);
        $this->logger->debug("AuthController:postresetpassword UUID check validity is " . UUID::is_valid($uuid));
        if ($uuidvalidity && $uuidexist) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            Auth::resetPassword($uuid, $password);
        }
        // $response = $response
        //         ->withHeader('Location', $this->getUrlFor($request,"home"))
        //         ->withStatus(303);
                $response = $this->withRedirect($response, $this->getUrlFor($request,"home",303));
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
        if (Auth::IsAuthentified()){
            // $response = $response
            //     ->withHeader('Location', $this->getUrlFor($request,"home"))
            //     ->withStatus(303);
                $response = $this->withRedirect($response, $this->getUrlFor($request,"home",303));
                return $response;
        }
        $arguments['recaptcha_api_key'] = $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'];
        return $this->render($response, 'auth/login.twig', $arguments);
    }

    public function postlogin(Request $request, Response $response, array $args = []): Response
    {
        $password = $request->getParam('password');
        $email = $request->getParam('email');

        $answer = $request->getParam('g-recaptcha-response');
        $auth=false;
        if (!is_null($answer)) {
            $this->recaptcha->verifyResponseToken($answer);
        }
        if ($this->recaptcha->getNoRobot()) {
            $this->logger->debug("AuthController::postlogin Recaptcha is success. Authentifying using password and login.");
            $auth=Auth::checkPassword($email, $password);
        } else {
            $this->logger->debug("AuthController::postlogin Recaptcha is not success.");
        }
        $this->logger->debug("AuthController::postlogin Authentififed is : $auth");
        if ($auth) {
        $redirectTo=$this->getUrlFor($request,"home");

        if (strlen(Auth::getOriginalRequestedPage())!=0) {
            $redirectTo=Auth::getOriginalRequestedPage();
        } 
        } else {
            $redirectTo=$this->getUrlFor($request,"auth.signin");
        }
        $this->logger->debug("AuthController::postlogin Should now redirect user to : ". $redirectTo);
        // $this->logger->debug("AuthController::postlogin ".var_export($_SESSION,true));
        return $this->withJSON($response, ['redirectTo'=>$redirectTo]);
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
        $arguments['recaptcha_api_key'] = $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'];
        return $this->render($response, 'auth/forgotpassword.twig', $arguments);
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
        $answer = $request->getParam('g-recaptcha-response');

        if (!is_null($answer)) {
            $this->recaptcha->verifyResponseToken($answer);
        }
        if ($this->recaptcha->getNoRobot()) {
            $this->logger->debug("AuthController::postforgotpassword Recaptcha is success. Sending reset link");
            Auth::sendResetPasswordLink($email);
        } else {
            $this->logger->debug("AuthController::postforgotpassword Recaptcha is not success");
        }
        return $this->withJSON($response, ['redirectTo'=>$this->getUrlFor($request,"auth.signinconfirmation")]);
       
    }

    public function signinconfirmation(Request $request, Response $response, array $args = []): Response
    {
        $arguments['approval'] = false;
        if (strcmp($_ENV['REGISTRATION_REQUIRE_APPROVAL'], "true") == 0) {
            $arguments['approval'] = true;
        }
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
        
       
        $answer = $request->getParam('g-recaptcha-response');
        if (!is_null($answer)) {
            $this->recaptcha->verifyResponseToken($answer);
        }
        if ($this->recaptcha->getNoRobot()) {
            $this->logger->debug("AuthController::postsignin Recaptcha is susccess. Continue with account creation");
            $password = $request->getParam('password');
            $email = $request->getParam('email');
            $nickname = $request->getParam('nickname');
            $password = password_hash($password, PASSWORD_DEFAULT);
            sleep(2);
            Auth::addUser($email, $password, $nickname);
            
           
           
        } else {
            $this->logger->debug("AuthController::postsignin Recaptcha is not success");

        }
        return $this->withJSON($response, ['redirectTo'=>$this->getUrlFor($request,"auth.signinconfirmation")]);
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
            Auth::sendValidationEmail($user->email, $user->emailchecklink);
            return $this->render($response, 'auth/validatoremail.twig', $arguments);
        }
        return $this->withRedirect($response, $this->getUrlFor($request,"home"));
    }
}
