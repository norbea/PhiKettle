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

/** Get system status */
$kettle->getStream()->write(Config::F_REQUEST_STAT);

/** Create kettle stream listener and handle response data */
$kettle->getStream()->on('data', function ($data) use ($kettle) {
    $kettle->handleResponse($data);
    print_r($kettle->getState());
});

$loop->run();
