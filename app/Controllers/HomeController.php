<?php

declare(strict_types=1);

namespace App\Controllers;


use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Config\StaticPlaylists;
use App\Database\User;
use App\Database\Playlist;
use App\Authentication\Auth;

class HomeController extends AbstractTwigController
{

    private $deezer;
    private $logger;
    private $staticplaylists;
    private $auth;

    /**
     * __construct
     *
     * @param  mixed $twig
     * @param  mixed $logger
     * @param  mixed $deezer
     * @param  mixed $staticplaylists
     * @return void
     */
    public function __construct(Twig $twig,LoggerInterface $logger, DeezerApiInterface $deezer, StaticPlaylists $staticplaylists, Auth $auth) {
        parent::__construct($twig);
        $this->logger = $logger;
        $this->deezer = $deezer;
        $this->staticplaylists = $staticplaylists;
        $this->auth=$auth;
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
        //$arguments['dynamicplaylists'] = ;
        //$arguments['staticplaylists'] = $this->staticplaylists->getPlaylists();
        //$search=$this->deezer->searchPlaylist('blind test')['data'];
        foreach ($this->deezer->searchPlaylist('blind test')['data'] as $playlist ) {
            $this->deezer->DBAddPlaylist($playlist['id']);
        }
        foreach ($this->staticplaylists->getPlaylists() as $playlist ) {
            $this->deezer->DBAddPlaylist($playlist['id']);
        }
        $arguments['playlists']=Playlist::orderBy('playlist_title','ASC')->get()->toArray();
        //$this->deezer->DBAddPlaylist($this->staticplaylists->getPlaylists());
        $arguments['userpoints'] = User::getCurrentUserTotalPoints($this->auth->getUserId());
        //$this->logger->debug("HomeController::home arguments  " . json_encode($arguments, JSON_PRETTY_PRINT));

        // $this->logger->debug("HomeController::home arguments global " . var_export($arguments, true));
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
