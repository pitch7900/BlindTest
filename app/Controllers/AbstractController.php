<?php

declare(strict_types=1);

namespace App\Controllers;
use App\Actions\Action;
use JsonSerializable;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Authentication\Authentification;
/**
 * This abstract class defines methods and properties used by all controllers.
 *
 * @package App\Controllers
 */
abstract class AbstractController {
    protected $container;
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(LoggerInterface::class);
    }
}
