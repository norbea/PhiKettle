<?php

require_once './vendor/autoload.php';

use PhiKettle\Connection;
use PhiKettle\Kettle;
use PhiKettle\Config;

$loop = React\EventLoop\Factory::create();

try {
    $kettle = new Kettle(new Connection('192.168.3.132', Config::PORT, $loop));
} catch (\PhiKettle\Exception $error) {
    // handle exception
} catch (\Exception $error) {
    // handle exception
}

/** Create kettle stream listener and handle response data */
$kettle->getStream()->on('data', function ($data) use ($kettle) {
    $kettle->handleResponse($data);
    print_r($kettle->getState());
});

/** Create a socket listener */
$socket = new React\Socket\Server($loop);
$socket->on('connection', function (React\Socket\Connection $connection) use ($kettle) {
    echo "\n==== New conenction ====\n\n";
    $connection->write("\nKettle commands:\n- boil\n- off\n\n");

    /** Create socket data listener */
    $connection->on('data', function ($data, $conn) use ($kettle) {
        switch ($kettle->sanitizeResponse($data)) {
            case 'boil':
                $connectionMessage = 'Kettle was boiled';
                $kettle->boil();
                break;
            case 'off':
                $connectionMessage = 'Kettle was turned off';
                $kettle->off();
                break;
            default:
                $connectionMessage = 'Command was not recognized';
                break;
        }

        $conn->write("=> $connectionMessage\n\n");
    });
});

$socket->listen(1336);
$loop->run();
