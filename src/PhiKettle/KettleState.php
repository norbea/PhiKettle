<?php

namespace PhiKettle;

class KettleState
{
    protected $temperature;
    protected $status;
    protected $lastActiveStatus;
    protected $message = '';
    protected $lastActiveMessage = '';

    /** @var \DateTime() */
    protected $dateTime;

    /**
     * @return mixed
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
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
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
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
     * @return mixed
     */
    public function getLastActiveStatus()
    {
        return $this->lastActiveStatus;
    }

    /**
     * @param mixed $lastActiveStatus
     *
     * @return $this
     */
    public function setLastActiveStatus($lastActiveStatus)
    {
        $this->lastActiveStatus = $lastActiveStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getLastActiveMessage()
    {
        return $this->lastActiveMessage;
    }

    /**
     * @param string $lastActiveMessage
     *
     * @return $this
     */
    public function setLastActiveMessage($lastActiveMessage)
    {
        $this->lastActiveMessage = $lastActiveMessage;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
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
