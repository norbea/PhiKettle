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
 * Represents state of kettle
 *
 * All handled responses from kettle returns this object
 *
 * @package PhiKettle
 * @author Lukas Hajdu <lukas@loftdigital.com>
 * @copyright 2015, Loft Digital <http://www.weareloft.com>
 */
class KettleState
{
    /** @var int Selected temperature */
    protected $temperature;

    /** @var string Status constant */
    protected $status;

    /** @var string Response message */
    protected $message;

    /** @var \DateTime() Date and time of status change */
    protected $dateTime;

    /**
     * Returns temperature
     *
     * @return mixed
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Sets selected temperature
     *
     * @param int $temperature
     *
     * @return $this
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Returns a kettle status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets a kettle status with a status constant
     *
     * @param mixed $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Returns response message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets response message
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns date and time for a latest state change
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Set date and time for a state change
     *
     * @param \DateTime $dateTime
     *
     * @return $this
     */
    public function setDateTime(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }
}
