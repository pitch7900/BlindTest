<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'staticplaylists' => [  
            '0' => [
                'id' => '1913917022',
                'title'   => 'Blind Test Série & Dessins Animés Années 80',
                'nbtracks'     => '58',
                'picture'   => 'https://e-cdns-images.dzcdn.net/images/cover/fac5eba46748d21de28ba133684d6a01-010b90d9d190e5b329c9f034dbab0a5a-c96902cd833d619904ca47f3e3caed33-b37028c7c7e259dd69b3b46f05a29eb6/1000x1000-000000-80-0-0.jpg'
            ],
            '1' => [
                'id' => '7708037842',
                "title" => "Blind test : Rap Français Année 90-2000",
                "nb_tracks" => 60,
                "picture_xl" => "https://cdns-images.dzcdn.net/images/playlist/bb02208aa616668b3f29618ec4b12701/1000x1000-000000-80-0-0.jpg",
            ]


        ],
    ]);
};
