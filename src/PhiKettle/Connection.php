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
use React\Stream\Stream;

/**
 * Creates stream from remote kettle socket resource
 *
 * @package PhiKettle
 * @author Lukas Hajdu <lukas@loftdigital.com>
 * @copyright 2015, Loft Digital <http://www.weareloft.com>
 */
class Connection
{
    /****************************************
     * Kettle network configuration
     *
     * Kettle event loop will be listening
     * and sending requests to this port
     * and IP address.
     ****************************************/

    /** @var int Port number */
    protected $port;

    /** @var string Host IP address */
    protected $host;

    /** @var  LoopInterface Event loop */
    protected $loop;

    /** @var  Stream Connection stream */
    protected $stream;

    /**
     * Create kettle connection and inject connection dependencies
     *
     * @param string $host Valid IP address
     * @param $port
     * @param LoopInterface $loop
     *
     * @throws Exception
     */
    public function __construct($host, $port, LoopInterface $loop)
    {
        if (!($host = filter_var($host, FILTER_VALIDATE_IP))) {
            throw new Exception('Invalid IP address');
        }

        $this->host = $host;
        $this->port = $port;
        $this->loop = $loop;
    }

    /**
     * Returns host IP address
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Returns port number
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns event loop
     *
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * Return connection stream
     *
     * @return Stream
     * @throws Exception
     */
    public function getStream()
    {
        if ($this->stream) {
            return $this->stream;
        }

        $this->stream = new Stream($this->getSocketResource(), $this->loop);

        return $this->stream;
    }

    /**
     * Return socket resource
     *
     * @return resource
     * @throws Exception
     */
    public function getSocketResource()
    {
        try {
            $socket = stream_socket_client('tcp://' . $this->host . ':' . $this->port, $errorNumber, $errorMessage, 30);
        } catch (\Exception $error) {
            throw new Exception($errorMessage, $errorNumber);
        }

        return $socket;
    }
}