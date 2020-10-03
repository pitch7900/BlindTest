<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class HomeController extends Controller
{

    private $log;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->log = new Logger('HomeController.php');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/debug.log', Logger::DEBUG));
    }



    private function DeezerArguments()
    {
        $userinfo = unserialize($_SESSION['deezerapi'])->getUserInformation();
        $this->log->debug("(DeezerArguments) : " . json_encode($userinfo));

        $arguments['deezerauthenticated'] = 1;

        $this->log->debug("(DeezerArguments) Deezer is set as source ");

        return $arguments;
    }



    /**
     * Return the "Home" view 
     * @param Request $request
     * @param Response $response
     * @return HTML
     */
    public function home(Request $request, Response $response)
    {


        $arguments['source'] = 'deezer';
        $arguments['playlists'] = getenv('playlistsids');

        $arguments = array_merge($arguments, $this->DeezerArguments());
        $this->log->debug("home) arguments after mergin deezer " . var_export($arguments, true));


        $this->log->debug("home) arguments global " . var_export($arguments, true));
        return $this->view->render($response, 'home.twig', $arguments);
    }


    /**
     * Return the spinning waiting icon defined in "waiting.twig"
     * @param Request $request
     * @param Response $response
     * @return type
     */
    public function getWaitingIcons(Request $request, Response $response)
    {
        return $this->view->render($response, 'waiting.twig');
    }
}
