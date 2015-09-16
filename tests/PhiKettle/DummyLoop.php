<?php

/**
 * This file is part of PhiKettle.
 *
 * (c) 2015, Loft Digital <http://www.weareloft.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhiKettle;

use React\EventLoop\LoopInterface;

/**
 * Class DummyLoop
 *
 * @package PhiKettle
 * @author Lukas Hajdu <lukas@loftdigital.com>
 * @copyright 2015, Loft Digital <http://www.weareloft.com>
 */
class DummyLoop implements LoopInterface
{
    public function addReadStream($stream, callable $listener)
    {
    }

    public function addWriteStream($stream, callable $listener)
    {
    }

    public function removeReadStream($stream)
    {
    }

    public function removeWriteStream($stream)
    {
    }

    public function removeStream($stream)
    {
    }

    public function addTimer($interval, callable $callback)
    {
    }

    public function addPeriodicTimer($interval, callable $callback)
    {
    }

    public function cancelTimer(\React\EventLoop\Timer\TimerInterface $timer)
    {
    }

    public function isTimerActive(\React\EventLoop\Timer\TimerInterface $timer)
    {
    }

    public function nextTick(callable $listener)
    {
    }

    public function futureTick(callable $listener)
    {
    }

    public function tick()
    {
    }

    public function run()
    {
    }

    public function stop()
    {
    }
}