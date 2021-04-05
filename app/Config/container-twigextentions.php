<?php

declare(strict_types=1);

use App\Database\User;

use Slim\Flash\Messages;

use App\Authentication\Authentication;


return [
    $twig->getEnvironment()->addGlobal('auth', [
        'check' => Authentication::IsAuthentified(),
        'user' => Authentication::CurrentUserID()
    ]),
    //Notification for Flash
    $twig->getEnvironment()->addGlobal('notification', $container->get(Messages::class)),

    $twig->getEnvironment()->addGlobal('user', [
        'username' => Authentication::CurrentUserName(),
        'getuserid' => Authentication::CurrentUserID(),
        'darktheme' => User::isDarkTheme()
    ]) 
];
