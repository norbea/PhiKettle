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
 * Class ConfigTest
 *
 * @package PhiKettle
 * @author Lukas Hajdu <lukas@loftdigital.com>
 * @copyright 2015, Loft Digital <http://www.weareloft.com>
 */
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
