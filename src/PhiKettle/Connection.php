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

    /** @var int */
    protected $port;

    /** @var string */
    protected $host;

    /** @var  LoopInterface */
    protected $loop;

    /** @var  Stream */
    protected $stream;

    /**
     * @param string $host Valid IP address
     *
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
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
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