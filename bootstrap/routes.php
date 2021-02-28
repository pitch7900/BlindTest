<?php

use Slim\App;

return function (App $app) {
        // Register routes
        $auth = require __DIR__ . '/routes/auth.php';
        $auth($app);

        $home = require __DIR__ . '/routes/home.php';
        $home($app);

        $deezer = require __DIR__ . '/routes/deezer.php';
        $deezer($app);

        $blindtest = require __DIR__ . '/routes/blindtest.php';
        $blindtest($app);

        $user = require __DIR__ . '/routes/user.php';
        $user($app);

        $errors = require __DIR__ . '/routes/errors.php';
        $errors($app);

};
