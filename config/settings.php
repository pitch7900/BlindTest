<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'staticplaylists' => [
            
            'database' => [
                'title'   => 'DRIVER={IBM DB2 ODBC DRIVER}',
                'nbtracks'     => 'iavsfpv',
                'host'     => 'i-dbavs-01',
                'port'     => '50001',
                'protocol' => 'TCPIP',
                'username' => 'db2inst1',
                'password' => 'db34sr9.',
                'schema'   => 'WEBAVSS',
                'charset'  => '',
                'flags'    => '',
            
            ],
        ],
    ]);
};
