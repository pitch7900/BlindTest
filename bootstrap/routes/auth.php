<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;

return function (App $app) {

        //Public pages for authentication
        $app->group('/auth', function (RouteCollectorProxy $group) {
                $group->get('/signin', AuthController::class . ':signin')
                        ->setName('auth.signin');
                $group->get('/signinconfirmation.html', AuthController::class . ':signinconfirmation')
                        ->setName('auth.signinconfirmation');
                $group->post('/signinconfirmation', AuthController::class . ':signinconfirmation')
                        ->setName('auth.post.signinconfirmation');
                $group->get('/login', AuthController::class . ':login')
                        ->setName('auth.login');
                $group->get('/forgotpassword', AuthController::class . ':forgotpassword')
                        ->setName('auth.forgotpassword');
                $group->get('/checkmail/{uuid}', AuthController::class . ':checkmail')
                        ->setName('auth.checkmail');
                $group->get('/validate/{uuid}', AuthController::class . ':validateemail')
                        ->setName('auth.validatemail');
                $group->get('/resetpassword/{uuid}', AuthController::class . ':resetpassword')
                        ->setName('auth.resetpassword');
                $group->post('/resetpassword/{uuid}', AuthController::class . ':postresetpassword')
                        ->setName('auth.resetpassword.post');
                $group->post('/login', AuthController::class . ':postlogin')
                        ->setName('auth.login');
                $group->post('/forgotpassword', AuthController::class . ':postforgotpassword')
                        ->setName('auth.forgotpassword.post');
                $group->post('/signin', AuthController::class . ':postsignin')
                        ->setName('auth.signin.post');
        });
};
