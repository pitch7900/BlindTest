<?php

declare(strict_types=1);

namespace App\Controllers;


use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\ServerRequest as Request;
use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Container\ContainerInterface;
class ErrorsController extends AbstractTwigController
{

    /**
     * @var DeezerApiInterface $deezer
     */
    private $deezer;

    /**
     * __construct
     *
     * @param  mixed $twig
     * @param  mixed $logger
     * @return void
     */
    public function __construct(ContainerInterface $container) {
        parent::__construct($container);

        $this->deezer = $container->get(DeezerApiInterface::class);
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
        $this->deezer->DBremoveTrack(intval($params['trackid']));
        return $response;
    }


}
