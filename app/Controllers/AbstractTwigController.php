<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Stream;

abstract class AbstractTwigController extends AbstractController
{
    /**
     * @var Twig
     */
    protected $twig;

    /**
     * AbstractController constructor.
     *
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Render the template and write it to the response.
     *
     * @param Response $response
     * @param string   $template
     * @param array    $renderData
     *
     * @return Response
     */
    protected function render(Response $response, string $template, array $renderData = []): Response
    {
        return $this->twig->render($response, $template, $renderData);
    }
    /**
     * Return the payload as JSON
     * @author Pierre Christensen <pierre.christensen@gmail.com>
     * @param Response $response
     * @param array    $payload
     * @return Response
     */
    protected function withJSON(Response $response, array $payload = []): Response
    {
        $stream = (new StreamFactory())->createStream(json_encode($payload), 'rb');

        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-cache, must-revalidate')
            ->withHeader('Expires','Mon, 26 Jul 1997 05:00:00 GMT^')
            ->withBody($stream)
            ->withStatus(200);
        return $response;
    }

    protected function withRedirect(Response $response, string $path): Response
    {
        $response = $response
            ->withHeader('Location', $path)
            ->withStatus(302);
        return $response;
    }

    protected function withMP3(Response $response, string $mp3filepath): Response
    {
        $stream = (new StreamFactory())->createStreamFromFile($mp3filepath, 'rb');
        return $response->withHeader('Content-type', 'audio/mp3')->withBody($stream)
            ->withHeader('Cache-Control', 'no-cache, must-revalidate')
            ->withHeader('Expires','Mon, 26 Jul 1997 05:00:00 GMT^');
    }
}
