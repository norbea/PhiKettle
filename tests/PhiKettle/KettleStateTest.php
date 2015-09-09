<?php

namespace PhiKettle;

class KettleStateTest extends \PHPUnit_Framework_TestCase
{
    /** @var KettleState */
    protected $kettleState;

    public function setUp()
    {
        $this->kettleState = new KettleState();
    }

    /**
     * @covers \PhiKettle\KettleState::getTemperature
     * @covers \PhiKettle\KettleState::setTemperature
     */
    public function testTemperature()
    {
        $this->assertTrue($this->kettleState->setTemperature(100) instanceof KettleState);
        $this->assertEquals(100, $this->kettleState->getTemperature());
    }

    /**
     * @covers \PhiKettle\KettleState::getStatus
     * @covers \PhiKettle\KettleState::setStatus
     */
    public function testStatus()
    {
        $this->assertTrue($this->kettleState->setStatus(Config::B_ON) instanceof KettleState);
        $this->assertEquals(Config::B_ON, $this->kettleState->getStatus());
    }

    /**
     * @covers \PhiKettle\KettleState::getMessage
     * @covers \PhiKettle\KettleState::setMessage
     */
    public function testMessage()
    {
        $this->assertTrue($this->kettleState->setMessage('Message') instanceof KettleState);
        $this->assertEquals('Message', $this->kettleState->getMessage());
    }

    /**
     * @covers \PhiKettle\KettleState::getDateTime
     * @covers \PhiKettle\KettleState::setDateTime
     */
    public function testDateTime()
    {
        $date = new \DateTime('now');

        $this->assertTrue($this->kettleState->setDateTime($date) instanceof KettleState);
        $this->assertEquals($date, $this->kettleState->getDateTime());
    }
}
