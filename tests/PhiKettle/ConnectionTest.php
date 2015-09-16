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

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Stream\Stream;

/**
 * Class ConnectionTest
 *
 * @package PhiKettle
 * @author Lukas Hajdu <lukas@loftdigital.com>
 * @copyright 2015, Loft Digital <http://www.weareloft.com>
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Connection */
    protected $connection;

    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var LoopInterface */
    protected $loop;

    protected $socketServer;

    public function setUp()
    {
        $this->host = '127.0.0.1';
        $this->port = 2000;
        $this->loop = Factory::create();
        $this->connection = new Connection($this->host, $this->port, $this->loop);
        $this->socketServer = stream_socket_server($this->host . ':' . $this->port);
    }

    public function tearDown()
    {
        fclose($this->socketServer);
    }

    /**
     * @covers \PhiKettle\Connection::__construct
     * @expectedException \PhiKettle\Exception
     * @expectedExceptionMessage Invalid IP address
     */
    public function testConstructException()
    {
        new Connection('test', $this->port, $this->loop);
    }

    /**
     * @covers \PhiKettle\Connection::__construct
     * @covers \PhiKettle\Connection::getHost
     * @covers \PhiKettle\Connection::getPort
     * @covers \PhiKettle\Connection::getLoop
     */
    public function testConstruct()
    {
        $connection = new Connection($this->host, $this->port, $this->loop);

        $this->assertEquals($this->host, $connection->getHost());
        $this->assertEquals($this->port, $connection->getPort());
        $this->assertEquals($this->loop, $connection->getLoop());
    }

    /**
     * @covers \PhiKettle\Connection::getStream
     * @expectedException \PhiKettle\Exception
     */
    public function testGetStream()
    {
        $this->assertInstanceOf('\React\Stream\Stream', $this->connection->getStream());

        $stream = $this->connection->getStream();
        $this->assertEquals($this->connection->getStream(), $stream);

        $connection = $this->getMockBuilder('\PhiKettle\Connection')
            ->setConstructorArgs([$this->host, $this->port, $this->loop])
            ->setMethods(['getSocketResource'])
            ->getMock();

        $connection->method('getSocketResource')
            ->will($this->throwException(new Exception('Test Exception')));

        /** @var Connection $connection */
        $connection->getStream();
    }

    /**
     * @covers \PhiKettle\Connection::getSocketResource
     */
    public function testGetSocketResource()
    {
        $this->assertTrue(is_resource($this->connection->getSocketResource()));
    }

    /**
     * @covers \PhiKettle\Connection::getSocketResource
     * @expectedException \PhiKettle\Exception
     */
    public function testGetSocketResourceException()
    {
        $connection = new Connection($this->host, $this->port + 1, $this->loop);
        $connection->getSocketResource();
    }
}
