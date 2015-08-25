<?php

namespace PhiKettle;

class Kettle
{
    /** @var \React\Stream\Stream */
    protected $stream;

    /** @var KettleState */
    protected $state;

    public function __construct(Connection $connection)
    {
        $this->stream = $connection->getStream();
        $this->stream->on('data', [$this, 'onData']);
        $this->state = new KettleState();
    }

    /**
     * @param string $data ASCII character
     *
     * @return void
     */
    public function setInitialState($data)
    {
        $ascii = ord($data);
        if ($ascii === 0) {
            $this->state->setStatus(Config::$systemStatusKeys[0]);

            return;
        }

        $values = array_filter(str_split(strrev(decbin($ascii))), function($value) {
            return $value == 1;
        });

        $state = $this->state;
        array_walk($values, function (&$value, $key) use (&$state) {
            $value = Config::$systemStatusKeys[$key+1];

            if (Config::getStateType($key+1) == Config::INIT_STAT_STATE) {
                $state->setStatus($value);
            }

            if (Config::getStateType($key+1) == Config::INIT_STAT_TEMPERATURE) {
                $state->setTemperature($value);
            }
        });

        return;
    }

    public function onData($data)
    {
        $this->handleResponse($data);
        var_dump($this->getState());
    }

    /**
     * @param int $data
     *
     * @return mixed
     */
    public function setAsyncState($data)
    {
        $this->state->setMessage(Config::$statusMessages[$data]);
    }

    public function isConnected()
    {
        return true;
    }

    /**
     * @param $response
     *
     * @return null|string
     */
    public function handleResponse($response)
    {
        $response = $this->sanitizeResponse($response);

        $this->state->setDateTime(new \DateTime('now'));

        if ($this->isDiscoveryResponse($response)) {
            $this->state
                ->setStatus(Config::INIT_STAT_OFF)
                ->setMessage('Kettle is ready to use');
        }

        if ($this->isInitStatusResponse($response)) {
            $data = null;
            sscanf($response, Config::F_RESPONSE_STAT, $data);
            $this->setInitialState($data);
        }

        if ($this->isAsyncStatusResponse($response)) {
            $data = null;
            sscanf($response, Config::F_ASYNC_RESPONSE_STAT, $data);
            $this->setAsyncState($data);
        }
    }

    /**
     * @param int $temperature
     *
     * @return void
     */
    public function boil($temperature = Config::B_100C)
    {
        $this->stream->write($this->formatActionMessage(Config::B_ON));
        $this->stream->write($this->formatActionMessage($temperature));
    }

    /**
     * @param int $temperature
     *
     * @return void
     */
    public function keepWarm($temperature = Config::B_100C)
    {
        $this->boil($temperature);
        $this->stream->write($this->formatActionMessage(Config::B_WARM));
    }

    /**
     * @return void
     */
    public function off()
    {
        $this->stream->write($this->formatActionMessage(Config::B_OFF));
    }

    /**
     * Format action message
     *
     * Returns action message for given action
     *
     * @param int $action
     *
     * @return string Action message
     */
    public function formatActionMessage($action)
    {
        return sprintf(Config::F_SET_STAT, $action);
    }

    /**
     * @param $response
     *
     * @return string
     */
    public function sanitizeResponse($response)
    {
        return preg_split("/\r\n|\n|\r/", $response)[0];
    }

    /**
     * @param $response
     *
     * @return bool
     */
    public function isDiscoveryResponse($response)
    {
        return ($response == $this->sanitizeResponse(Config::D_RESPONSE));
    }

    /**
     * @param $response
     *
     * @return bool
     */
    public function isInitStatusResponse($response)
    {
        return (strpos($response, 'sys status key=') === 0);
    }

    public function isAsyncStatusResponse($response)
    {
        return (strpos($response, 'sys status 0x') === 0);
    }

    /**
     * @return KettleState
     */
    public function getState()
    {
        return $this->state;
    }
}
