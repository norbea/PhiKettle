<?php

namespace PhiKettle;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Stream\Stream;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Connection */
    protected $connection;

    /** @var string */
    protected $host;

    /** @var LoopInterface */
    protected $loop;

    public function setUp()
    {
        $this->host = '127.0.0.1';
        $this->loop = Factory::create();
        $this->connection = new Connection($this->host, $this->loop);
    }

    /**
     * @covers Connection::__construct
     * @expectedException \PhiKettle\KettleException
     * @expectedExceptionMessage Invalid IP address
     */
    public function testConstruct()
    {
        new Connection('test', $this->loop);
    }

    /**
     * @covers Connection::getHost
     */
    public function testGetHost()
    {
        $this->assertEquals($this->host, $this->connection->getHost());
    }

    /**
     * @covers Connection::getLoop
     */
    public function testGetLoop()
    {
        $this->assertEquals($this->loop, $this->connection->getLoop());
    }

    /**
     * @covers Connection::getStream
     * @expectedException \PhiKettle\KettleException
     */
    public function testGetStream()
    {
        $socketServer = stream_socket_server($this->host . ':' . Connection::PORT);
        $this->assertTrue($this->connection->getStream() instanceof Stream);
        fclose($socketServer);

        $connection = $this->getMockBuilder('\PhiKettle\Connection')
            ->setConstructorArgs([$this->host, $this->loop])
            ->setMethods(['getSocketResource'])
            ->getMock();

        $connection->method('getSocketResource')
            ->will($this->throwException(new KettleException('Test Exception')));

        /** @var Connection $connection */
        $connection->getStream();
    }
}
