<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authentication\Recaptcha;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\AbstractTwigController;


/**
 * AuthController
 * @author : Pierre Christensen
 */
class ReCaptchaController extends AbstractTwigController
{

    /**
     * logger
     *
     * @var Psr\Log\LoggerInterface
     */
    private $logger;
    
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
    public function __construct(Twig $twig, LoggerInterface $logger,Recaptcha $recaptcha)
    {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->recaptcha = $recaptcha;
        $this->logger->debug("ReCaptchaController::_construct Constructor called");
    }


    /**
     * checkpage : Page to handle ReCaptcha
     *
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function checkpage(Request $request, Response $response, array $args = []): Response
    {
        $arguments['recaptcha_api_key']=$_ENV['GOOGLE_RECAPTCHA_SITE_KEY'];
        $this->logger->debug("ReCaptchaController::checkpage() Recaptcha Site Key is : ".$arguments['recaptcha_api_key']);
        return $this->render($response, 'auth/recaptcha.twig', $arguments);
    }


    public function postcheckpage(Request $request, Response $response, array $args = []): Response
    {
        $answer=$request->getParam('g-recaptcha-response');
        if (!is_null($answer)){
            $this->recaptcha->verifyResponseToken($answer);
        }
        return $response->withHeader('Location', '/')
        ->withStatus(302);
    }
}
