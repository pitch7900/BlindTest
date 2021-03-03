<?php

declare(strict_types=1);


use Twig\TwigFunction;

use App\Database\User;

use Slim\Flash\Messages;

use App\Authentication\Auth;


return [
    $twig->getEnvironment()->addGlobal('auth', [
        'check' => Auth::IsAuthentified(),
        'user' => Auth::CurrentUserID()
    ]),
    //Notification for Flash
    $twig->getEnvironment()->addGlobal('notification', $container->get(Messages::class)),

    $twig->getEnvironment()->addGlobal('user', [
        'username' => Auth::CurrentUserName(),
        'getuserid' => Auth::CurrentUserID(),
        'darktheme' => User::isDarkTheme()
    ]) 
];
