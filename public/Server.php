<?php
// Set the default timezone.
date_default_timezone_set('Europe/Zurich');


require __DIR__ . '/../vendor/autoload.php';

$server = new Hoa\Eventsource\Server();
$i=0;
//  var_dump($server,true);
// while (true) {
    // “tick” is the event name.
    $server->tick->send($i);
    $i++;
    
   
    $server->tick->send("test");
    $i++;
    $server->tick->send($i);
    $i++;
    $server->tick->send($i);
    $i++;
    $server->tick->send($i);
    $i++;
    $server->tick->send($i);
    $i++;
// }