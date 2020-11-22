<?php

declare(strict_types=1);

namespace App\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Psr\Log\LoggerInterface;
use App\Authentication\Recaptcha;

class ReCaptchaMiddleware
{

    /**
     * Logger interface
     * @var LoggerInterface;
     */
    private $logger;

    private $container;

    private $app;


    private $recaptcha;

    /**
     * @param App    $app
     *
     * @return ReCaptchaMiddleware
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
     * @return ReCaptchaMiddleware
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
        $this->recaptcha = $this->container->get(Recaptcha::class);
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->logger->debug("ReCaptchaMiddleware::__construct() Called");
        
    }

    public function __invoke(ServerRequestInterface  $request, RequestHandlerInterface  $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->logger->debug("ReCaptchaMiddleware::__invoke() Called");
        if (!$this->recaptcha->getNoRobot()) {
            //Cpatcha status failed
            $routeParser = $this->app->getRouteCollector()->getRouteParser();
            $signinroute = $routeParser->urlFor('recaptacha.check');
            $this->logger->debug("ReCaptchaMiddleware::__invoke() Not check. Should redirect to cpatcha page " . $signinroute);
            $this->logger->debug("ReCaptchaMiddleware::__invoke() " . print_r($_SESSION,true));
            $response = $handler->handle($request)
            ->withHeader('Location',  $signinroute)
            ->withStatus(303);
            return $response;
        } else {
            //Captcha Status succes - Not a robot according to google
            $this->logger->debug("ReCaptchaMiddleware::__invoke() Not a robot. Continue");
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
