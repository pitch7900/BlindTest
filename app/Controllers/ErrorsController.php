<?php

declare(strict_types=1);

namespace App\Controllers;


use App\Database\Track;
use App\Database\TrackErrors;
use Psr\Container\ContainerInterface;
use Slim\Http\ServerRequest as Request;
use App\MusicSources\Deezer\DeezerApiInterface;
use Psr\Http\Message\ResponseInterface as Response;

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

        $track = Track::find(intval($params['trackid']));
        $this->logger->debug("ErrorsController::postplayer Moving track id ".intval($params['trackid'])." to trackerrors table");
        TrackErrors::updateOrCreate([
            'id' => $track->id,
            'track_title' => $track->track_title,
            'track_link' => $track->track_link,
            'track_preview' =>$track->track_preview,
            'track_artist' => $track->track_artist,
            'track_album' => $track->track_album,
            'track_duration' => $track->track_duration,
            'original_playlist' => intval($params['playlistid'])
        ]);
        $this->deezer->DBremoveTrack(intval($params['trackid']));
        
        return $response;
    }


}
