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

/**
 * Class KettleTest
 *
 * @package PhiKettle
 * @author Lukas Hajdu <lukas@loftdigital.com>
 * @copyright 2015, Loft Digital <http://www.weareloft.com>
 */
class KettleTest extends \PHPUnit_Framework_TestCase
{
    /** @var Kettle */
    public $kettle;

    protected $socketServer;

    protected $host;

    protected $port;

    public function setUp()
    {
        $this->host = '127.0.0.1';
        $this->port = 2000;
        $this->socketServer = stream_socket_server($this->host . ':' . $this->port);
        $this->kettle = new Kettle(new Connection($this->host, $this->port, new DummyLoop()));
    }

    public function tearDown()
    {
        fclose($this->socketServer);
    }

    /**
     * @covers PhiKettle\Kettle::__construct
     * @covers PhiKettle\Kettle::getStream
     * @covers PhiKettle\Kettle::getState
     */
    public function testConstructor()
    {
        $connection = new Connection($this->host, $this->port, new DummyLoop());
        $kettle = new Kettle($connection);

        $this->assertEquals(new KettleState(), $kettle->getState());
        $this->assertEquals($connection->getStream(), $kettle->getStream());
    }

    /**
     * @covers PhiKettle\Kettle::handleResponse
     */
    public function testHandleResponse()
    {
        $responses = [
            /**
             * Initial status response without status key (kettle is off)
             * temperature = null
             * status = Off
             */
            sprintf(Config::F_RESPONSE_STAT, '') => (new KettleState())
                ->setStatus(Config::INIT_STAT_OFF),
            /**
             * Discovery response
             * temperature = null
             * status = Discovered
             */
            Config::D_RESPONSE => (new KettleState())
                ->setStatus(Config::INIT_STAT_DISCOVERED),
            /**
             * Initial status response with status key
             * temperature = 100
             * status = On
             */
            sprintf(Config::F_RESPONSE_STAT, chr(33)) => (new KettleState())
                ->setStatus(Config::INIT_STAT_BIT_1)
                ->setTemperature(100),
            /**
             * Asynchronous status response
             * temperature = 80
             * status = On
             */
            sprintf(Config::F_ASYNC_RESPONSE_STAT, 80) => (new KettleState())
                ->setStatus(Config::INIT_STAT_BIT_1)
                ->setTemperature(80),
            /**
             * Dummy response
             */
            'dummy' => (new KettleState())
                ->setStatus(null)
                ->setTemperature(null),
        ];

        /**
         * @var string $message
         * @var KettleState $kettleState
         */
        foreach ($responses as $message => $kettleState) {
            if ($message == 'dummy') {
                $this->assertFalse($this->kettle->handleResponse($message));
            } else {
                $this->assertNull($this->kettle->handleResponse($message));
            }

            $this->assertEquals($kettleState->getStatus(), $this->kettle->getState()->getStatus());
            $this->assertEquals($kettleState->getTemperature(), $this->kettle->getState()->getTemperature());
        }
    }

    /**
     * @covers \PhiKettle\Kettle::setInitialState
     */
    public function testSetInitialStatus()
    {
        $statuses = [
            0 => (new KettleState())
                ->setStatus(Config::INIT_STAT_OFF),
            5 => (new KettleState())
                ->setStatus(Config::INIT_STAT_BIT_1)
                ->setTemperature(Config::INIT_STAT_BIT_3),
            9 => (new KettleState())
                ->setStatus(Config::INIT_STAT_BIT_1)
                ->setTemperature(Config::INIT_STAT_BIT_4),
            17 => (new KettleState())
                ->setStatus(Config::INIT_STAT_BIT_1)
                ->setTemperature(Config::INIT_STAT_BIT_5),
            33 => (new KettleState())
                ->setStatus(Config::INIT_STAT_BIT_1)
                ->setTemperature(Config::INIT_STAT_BIT_6),
        ];

        foreach ($statuses as $status => $kettleState) {
            $this->kettle->setInitialState(chr($status));
            $this->assertEquals($kettleState, $this->kettle->getState());
        }
    }

    /**
     * @covers \PhiKettle\Kettle::setAsyncState
     */
    public function testSetAsyncState()
    {
        $data = 80;
        $this->kettle->setAsyncState($data);

        $this->assertEquals($data, $this->kettle->getState()->getTemperature());
        $this->assertEquals(Config::$statusMessages[$data], $this->kettle->getState()->getMessage());
    }

    /**
     * @covers \PhiKettle\Kettle::boil
     */
    public function testBoil()
    {
        $this->kettle->boil(Config::B_100C);
        $this->assertEquals(100, $this->kettle->getState()->getTemperature());
        $this->assertEquals(Config::B_ON, $this->kettle->getState()->getStatus());

        $this->kettle->boil(Config::B_65C);
        $this->assertEquals(65, $this->kettle->getState()->getTemperature());
        $this->assertEquals(Config::B_ON, $this->kettle->getState()->getStatus());
    }

    /**
     * @covers \PhiKettle\Kettle::keepWarm
     */
    public function testKeepWarm()
    {
        $this->kettle->keepWarm(Config::B_100C);
        $this->assertEquals(100, $this->kettle->getState()->getTemperature());
        $this->assertEquals(Config::B_WARM, $this->kettle->getState()->getStatus());

        $this->kettle->keepWarm(Config::B_65C);
        $this->assertEquals(65, $this->kettle->getState()->getTemperature());
        $this->assertEquals(Config::B_WARM, $this->kettle->getState()->getStatus());
    }

    /**
     * @covers \PhiKettle\Kettle::off
     */
    public function testOff()
    {
        $this->kettle->off();
        $this->assertNull($this->kettle->getState()->getTemperature());
        $this->assertEquals(Config::B_OFF, $this->kettle->getState()->getStatus());
    }

    /**
     * @covers \PhiKettle\Kettle::sanitizeResponse
     */
    public function testSanitizeResponse()
    {
        $lineEndings = ["\r", "\n", "\r\n",];

        foreach ($lineEndings as $key => $lineEnding) {
            $this->assertEquals(
                'Status-' . $key,
                $this->kettle->sanitizeResponse('Status-' . $key . $lineEnding)
            );
        }
    }

    /**
     * @covers \PhiKettle\Kettle::formatActionMessage
     */
    public function testFormatActionMessage()
    {
        $this->assertEquals("set sys output 0x80\n", $this->kettle->formatActionMessage(Config::B_100C));
    }

    /**
     * @covers \PhiKettle\Kettle::isDiscoveryResponse
     */
    public function testIsDiscoveryResponse()
    {
        $this->assertTrue(
            $this->kettle->isDiscoveryResponse(
                $this->kettle->sanitizeResponse(Config::D_RESPONSE)
            )
        );

        $responses = [
            sprintf(Config::F_RESPONSE_STAT, chr(0)),
            sprintf(Config::F_RESPONSE_STAT, chr(5)),
            sprintf(Config::F_ASYNC_RESPONSE_STAT, 5)
        ];

        foreach ($responses as $response) {
            $this->assertFalse(
                $this->kettle->isDiscoveryResponse(
                    $this->kettle->sanitizeResponse($response)
                )
            );
        }
    }

    /**
     * @covers \PhiKettle\Kettle::isInitStatusResponse
     */
    public function testIsInitStatusResponse()
    {
        $responses = [
            sprintf(Config::F_RESPONSE_STAT, chr(0)),
            sprintf(Config::F_RESPONSE_STAT, chr(5)),
        ];

        foreach ($responses as $response) {
            $this->assertTrue(
                $this->kettle->isInitStatusResponse(
                    $this->kettle->sanitizeResponse($response)
                )
            );
        }

        $responses = [
            Config::D_RESPONSE,
            sprintf(Config::F_ASYNC_RESPONSE_STAT, 5),
        ];

        foreach ($responses as $response) {
            $this->assertFalse(
                $this->kettle->isInitStatusResponse(
                    $this->kettle->sanitizeResponse($response)
                )
            );
        }
    }

    /**
     * @covers \PhiKettle\Kettle::isAsyncStatusResponse
     */
    public function testIsAsyncStatusResponse()
    {
        $this->assertTrue(
            $this->kettle->isAsyncStatusResponse(
                $this->kettle->sanitizeResponse(sprintf(Config::F_ASYNC_RESPONSE_STAT, 5))
            )
        );

        $responses = [
            Config::D_RESPONSE,
            sprintf(Config::F_RESPONSE_STAT, chr(0)),
            sprintf(Config::F_RESPONSE_STAT, chr(5)),
        ];

        foreach ($responses as $response) {
            $this->assertFalse(
                $this->kettle->isAsyncStatusResponse(
                    $this->kettle->sanitizeResponse($response)
                )
            );
        }
    }
}
