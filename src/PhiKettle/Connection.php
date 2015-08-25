<?php

namespace PhiKettle;

use React\EventLoop\LoopInterface;
use React\Stream\Stream;

class Connection
{
    /****************************************
     * Kettle network configuration
     *
     * Kettle event loop will be listening
     * and sending requests to this port
     * and IP address.
     ****************************************/

    /** Kettle port number */
    const PORT = 2000;

    /** @var string */
    protected $host;

    /** @var  LoopInterface */
    protected $loop;

    /** @var  Stream */
    protected $stream;

    /**
     * @param string $host Valid IP address
     *
     * @param LoopInterface $loop
     *
     * @throws KettleException
     */
    public function __construct($host, LoopInterface $loop)
    {
        if (!($host = filter_var($host, FILTER_VALIDATE_IP))) {
            throw new KettleException('Invalid IP address');
        }

        $this->host = $host;
        $this->loop = $loop;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return Stream
     * @throws KettleException
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
     * @return resource
     * @throws KettleException
     */
    public function getSocketResource()
    {
        $socket = stream_socket_client('tcp://' . $this->host . ':' . self::PORT, $errorNumber, $errorMessage, 30);
        if (!$socket) {
            throw new KettleException($errorMessage, $errorNumber);
        }

        return $socket;
    }
}