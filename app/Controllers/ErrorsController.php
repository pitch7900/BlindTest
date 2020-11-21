<?php

declare(strict_types=1);

namespace App\Controllers;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Config\StaticPlaylists;

class ErrorsController extends AbstractTwigController
{

    private $deezer;
    private $logger;
    private $staticplaylists;
    
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

        $this->logger->debug("ErrorsController::_construct Constructor of HomeController called");
    }

         
    /**
     * postplayer - Log the errors 
     * 
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function postplayer(Request $request, Response $response, array $args = []): Response {
        $params = $request->getParams();
        $this->logger->debug("ErrorsController::postplayer Error logged : " . var_export($params, true));
        return $response;
    }


}
