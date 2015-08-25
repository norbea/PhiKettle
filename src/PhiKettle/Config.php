<?php

namespace PhiKettle;

/**
 * Class Config
 * @package LoftDigital\iKettle
 * @link http://www.awe.com/mark/blog/20140223.html
 */
class Config
{
    /**************************************************
     * Kettle discovery settings
     *
     * If the kettle is connected through a socket,
     * it should respond to {@link self::DISCOVERY_REQUEST}
     * with {@link self::DISCOVERY_RESPONSE} message
     **************************************************/

    /** Kettle discovery message */
    const D_REQUEST = "HELLOKETTLE\n";

    /** Response message to kettle discovery */
    const D_RESPONSE = "HELLOAPP\r";

    /**************************************************
     * Kettle request/response formats
     **************************************************/

    /**
     * Get initial kettle status. Kettle responds
     * with message in {@link self::F_RESPONSE_STAT} format
     */
    const F_REQUEST_STAT = "get sys status\n";

    /**
     * Response format for initial status request.
     * Status key is returned as an ASCII character
     * in a bit field format
     */
    const F_RESPONSE_STAT = "sys status key=%s\r";

    /**
     * Asynchronous status messages send by kettle
     * at state change
     */
    const F_ASYNC_RESPONSE_STAT = "sys status 0x%d\r";

    /** Action message format */
    const F_SET_STAT = "set sys output 0x%d\n";

    /**************************************************
     * Physical buttons on a kettle
     *
     * Values represent suffix of an action
     * message `set sys output 0xX\n`,
     * where X is value of a `B_*` constant.
     **************************************************/

    /** Represents "On" button */
    const B_ON = 4;

    /** Represents "Off" button */
    const B_OFF = 0;

    /** Represents "Keep warm" button */
    const B_WARM = 8;

    /** Represents "65°C" button */
    const B_65C = 200;

    /** Represents "80°C" button */
    const B_80C = 4000;

    /** Represents "95°C" button */
    const B_95C = 2;

    /** Represents "80°C" button */
    const B_100C = 80;

    /**************************************************
     * Initial status bit field
     *
     * Response data to {$link self::F_RESPONSE_STAT} will
     * consist of combination of this bit field represented
     * as an ASCII character
     *
     * ```
     * | Bit 6 | Bit 5 | Bit 4 | Bit 3 |Bit 2 | Bit 1 |
     * +-------+-------+-------+-------+------+-------+
     * | 100°C | 95°C  | 80°C  | 65°C  | Warm | On    |
     * ```
     **************************************************/

    /** Represents empty sys status key */
    const INIT_STAT_OFF = 'Off';

    /** Represents Bit 1 in the bit field */
    const INIT_STAT_BIT_1 = 'On';

    /** Represents Bit 2 in the bit field */
    const INIT_STAT_BIT_2 = 'Warm';

    /** Represents Bit 3 in the bit field */
    const INIT_STAT_BIT_3 = 65;

    /** Represents Bit 4 in the bit field */
    const INIT_STAT_BIT_4 = 80;

    /** Represents Bit 5 in the bit field */
    const INIT_STAT_BIT_5 = 95;

    /** Represents Bit 6 in the bit field */
    const INIT_STAT_BIT_6 = 100;

    const INIT_STAT_STATE = 0;
    const INIT_STAT_TEMPERATURE = 1;

    /** @var array Initial system status bit field to message mapping */
    public static $systemStatusKeys = [
        0 => self::INIT_STAT_OFF,
        1 => self::INIT_STAT_BIT_1,
        2 => self::INIT_STAT_BIT_2,
        3 => self::INIT_STAT_BIT_3,
        4 => self::INIT_STAT_BIT_4,
        5 => self::INIT_STAT_BIT_5,
        6 => self::INIT_STAT_BIT_6,
    ];

    /**************************************************
     * Asynchronous response status mapping
     **************************************************/

    public static $statusMessages = [
        0 => 'Turned off',
        1 => 'Kettle was removed (whilst on)',
        2 => 'Problem (boiled, dry?)',
        3 => 'Reached temperature',
        5 => 'Turned on',
        10 => 'Warm has ended',
        11 => 'Warm selected',
        65 => '65C selected',
        80 => '80C selected',
        95 => '95C selected',
        100 => '100C selected',
        8005 => 'Warm length is 5 minutes',
        8010 => 'Warm length is 10 minutes',
        8020 => 'Warm length is 20 minutes',
    ];

    /**************************************************
     * Experimental settings
     **************************************************/

    /** Keep kettle warm for 5 minutes */
    const K_WARM_5 = 8005;

    /** Keep kettle warm for 10 minutes */
    const K_WARM_10 = 8010;

    /** Keep kettle warm for 20 minutes */
    const K_WARM_20 = 8020;

    /**
     * @param $state
     *
     * @return int
     */
    public static function getStateType($state)
    {
        if (in_array($state, [0, 1, 2])) {
            return self::INIT_STAT_STATE;
        }

        return self::INIT_STAT_TEMPERATURE;
    }
}
