<?php

declare(strict_types=1);

namespace App\Controllers;

use App\MusicSources\Deezer\DeezerApi;
use App\MusicSources\Deezer\DeezerApiInterface;
use App\Preferences;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Config\StaticPlaylists;

class HomeController extends AbstractTwigController
{

    private $deezer;
    private $logger;
    private $staticplaylists;
    
    /**
     * __construct
     *
     * @param  mixed $twig
     * @param  mixed $logger
     * @param  mixed $deezer
     * @param  mixed $staticplaylists
     * @return void
     */
    public function __construct(Twig $twig,LoggerInterface $logger, DeezerApiInterface $deezer, StaticPlaylists $staticplaylists) {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->deezer = $deezer;
        $this->staticplaylists = $staticplaylists;
        $this->logger->debug("HomeController::_construct Constructor of HomeController called");
    }

         
    /**
     * home
     * Return the "Home" view 
     * @param  mixed $request
     * @param  mixed $response
     * @param  mixed $args
     * @return Response
     */
    public function home(Request $request, Response $response, array $args = []): Response {
        $arguments['dynamicplaylists'] = $this->deezer->searchPlaylist('blind test');
        $arguments['staticplaylists'] = $this->staticplaylists->getPlaylists();
        // die(var_dump($arguments['staticplaylists']));
        $this->logger->debug("HomeController::home arguments after mergin deezer " . var_export($arguments, true));

        $this->logger->debug("HomeController::home arguments global " . var_export($arguments, true));
        return $this->render($response, 'home.twig', $arguments);
    }


    /**
     * Return the spinning waiting icon defined in "waiting.twig"
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getWaitingIcons(Request $request, Response $response, array $args = []): Response  {
        return $this->render($response, 'waiting.twig');
    }
}
