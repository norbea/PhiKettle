<?php

namespace PhiKettle;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \PhiKettle\Config::getInitStateType
     */
    public function testGetInitStateType()
    {
        $config = new Config();

        foreach (array_keys($config::$systemStatusKeys) as $bit) {
            if ($bit < 3) {
                $this->assertEquals(Config::INIT_STAT_STATE, $config->getInitStateType($bit));
            } else {
                $this->assertEquals(Config::INIT_STAT_TEMPERATURE, $config->getInitStateType($bit));
            }
        }
    }
}
