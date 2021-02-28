<?php

declare(strict_types=1);

namespace App\Controllers;


use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Config\StaticPlaylists;
use App\Database\User;
use App\Database\Playlist;
use App\Authentication\Auth;
use Psr\Container\ContainerInterface;

class HomeController extends AbstractTwigController
{

    private $deezer;
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
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->deezer = $container->get(DeezerApiInterface::class);
        $this->staticplaylists = $container->get(StaticPlaylists::class);
       
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
    public function home(Request $request, Response $response, array $args = []): Response
    {
        //$arguments['dynamicplaylists'] = ;
        //$arguments['staticplaylists'] = $this->staticplaylists->getPlaylists();
        //$search=$this->deezer->searchPlaylist('blind test')['data'];
        foreach ($this->deezer->searchPlaylist('blind test')['data'] as $playlist) {
            $this->deezer->DBAddPlaylist($playlist['id']);
        }
        foreach ($this->staticplaylists->getPlaylists() as $playlist) {
            $this->deezer->DBAddPlaylist($playlist['id']);
        }
        //$arguments['playlists']=Playlist::orderBy('playlist_title','ASC')->get()->toArray();
        $arguments['playlists'] = Playlist::getPlaylists();
        //$this->deezer->DBAddPlaylist($this->staticplaylists->getPlaylists());
        $arguments['userpoints'] = User::getUserTotalPoints(Auth::getUserId());
        //$this->logger->debug("HomeController::home arguments  " . json_encode($arguments, JSON_PRETTY_PRINT));
        $arguments['homescreen'] = true;
        // $this->logger->debug("HomeController::home arguments global " . var_export($arguments, true));
        return $this->render($response, 'home.twig', $arguments);
    }


    /**
     * Return the spinning waiting icon defined in "waiting.twig"
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getWaitingIcons(Request $request, Response $response, array $args = []): Response
    {
        return $this->render($response, 'waiting.twig');
    }
}
