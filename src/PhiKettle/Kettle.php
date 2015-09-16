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
 * Represents kettle itself
 *
 * Class used for kettle interaction, sending requests and handling kettle responses. Example usage:
 * ```
 * $loop = React\EventLoop\Factory::create();
 *
 * try {
 *    $iKettle = new Kettle(new Connection(KETTLE_IP_ADDRESS, Config::PORT, $loop));
 * } catch (\PhiKettle\Exception $e) {
 *     // Handle kettle exception
 * } catch (\Exception $e) {
 *     // Handle other exceptions
 * }
 *
 * // Boil the kettle
 * $iKettle->boil(Config::B_95C);
 *
 * // Write other date to kettle stream
 * $iKettle->getStream()->write(Config::F_REQUEST_STAT);
 * ```
 *
 * @package PhiKettle
 * @author Lukas Hajdu <lukas@loftdigital.com>
 * @copyright 2015, Loft Digital <http://www.weareloft.com>
 */
class Kettle
{
    /** @var \React\Stream\Stream Kettle connection stream */
    protected $stream;

    /** @var KettleState Kettle status */
    protected $state;

    /**
     * Create a kettle object for passed connection object
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->state = new KettleState();
        $this->stream = $connection->getStream();
    }

    /**
     * Returns kettle connection stream
     *
     * @return \React\Stream\Stream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Returns kettle state object
     *
     * @return KettleState
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Handles responded data from kettle.
     *
     * All kettle events should be handled with this method. Method provides formatting of response data and processing
     * based on response message type (discovery, asynchronous status, initial status)
     *
     * @param $response
     *
     * @return null|false
     */
    public function handleResponse($response)
    {
        $response = $this->sanitizeResponse($response);

        $this->state->setDateTime(new \DateTime('now'));

        if ($this->isDiscoveryResponse($response)) {
            $this->state
                ->setStatus(Config::INIT_STAT_DISCOVERED);

            return null;
        }

        if ($this->isInitStatusResponse($response)) {
            $data = null;
            sscanf($response, Config::F_RESPONSE_STAT, $data);
            $this->setInitialState($data);

            return null;
        }

        if ($this->isAsyncStatusResponse($response)) {
            $data = null;
            sscanf($response, Config::F_ASYNC_RESPONSE_STAT, $data);
            $this->setAsyncState($data);

            return null;
        }

        $this->state
            ->setStatus(null)
            ->setTemperature(null);

        return false;
    }

    /**
     * Handle initial state response from the kettle and update kettle status
     *
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

        $values = array_filter(str_split(strrev(decbin($ascii))), function ($value) {
            return $value == 1;
        });

        $state = $this->state;
        array_walk($values, function (&$value, $key) use (&$state) {
            $value = Config::$systemStatusKeys[$key + 1];

            $config = new Config();

            if ($config->getInitStateType($key + 1) == Config::INIT_STAT_STATE) {
                $state->setStatus($value);
            }

            if ($config->getInitStateType($key + 1) == Config::INIT_STAT_TEMPERATURE) {
                $state->setTemperature($value);
            }
        });

        return;
    }

    /**
     * Handle asynchronous response from the kettle and update kettle state
     *
     * @param int $data
     *
     * @return mixed
     */
    public function setAsyncState($data)
    {
        $this->state
            ->setTemperature($data)
            ->setMessage(Config::$statusMessages[$data]);
    }

    /**
     * Boil the kettle
     *
     * Kettle temperature is set to 100°C by default. Method excepts one of these temperature constants:
     * {@see \PhiKettle\Config::B_100C}, {@see \PhiKettle\Config::B_95C}, {@see \PhiKettle\Config::B_80C}
     * and {@see \PhiKettle\Config::B_65C}
     *
     * @param int $temperature constant
     *
     * @return void
     */
    public function boil($temperature = Config::B_100C)
    {
        $this->stream->write($this->formatActionMessage(Config::B_ON));
        $this->stream->write($this->formatActionMessage($temperature));

        $this->state
            ->setDateTime(new \DateTime('now'))
            ->setStatus(Config::B_ON)
            ->setTemperature(Config::$temperatureButtonMapping[$temperature]);
    }

    /**
     * Boil the kettle and keep warm
     *
     * Kettle temperature is set to 100°C by default. Method excepts one of these temperature constants:
     * {@see \PhiKettle\Config::B_100C}, {@see \PhiKettle\Config::B_95C}, {@see \PhiKettle\Config::B_80C}
     * and {@see \PhiKettle\Config::B_65C}
     *
     * @param int $temperature
     *
     * @return void
     */
    public function keepWarm($temperature = Config::B_100C)
    {
        $this->boil($temperature);
        $this->stream->write($this->formatActionMessage(Config::B_WARM));

        $this->state
            ->setDateTime(new \DateTime('now'))
            ->setStatus(Config::B_WARM)
            ->setTemperature(Config::$temperatureButtonMapping[$temperature]);
    }

    /**
     * Turn off the kettle
     *
     * @return void
     */
    public function off()
    {
        $this->stream->write($this->formatActionMessage(Config::B_OFF));

        $this->state
            ->setDateTime(new \DateTime('now'))
            ->setStatus(Config::B_OFF)
            ->setTemperature(null);
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
     * Sanitize kettle response message
     *
     * @param $response
     *
     * @return string
     */
    public function sanitizeResponse($response)
    {
        return preg_split("/\r\n|\n|\r/", $response)[0];
    }

    /**
     * Checks if kettle response message is discovery message
     *
     * @param $response
     *
     * @return bool
     */
    public function isDiscoveryResponse($response)
    {
        return ($response == $this->sanitizeResponse(Config::D_RESPONSE));
    }

    /**
     * Checks if kettle response message is initial status message
     *
     * @param $response
     *
     * @return bool
     */
    public function isInitStatusResponse($response)
    {
        return (strpos($response, 'sys status key=') === 0);
    }

    /**
     * Checks if kettle response message is asynchronous status message
     *
     * @param $response
     *
     * @return bool
     */
    public function isAsyncStatusResponse($response)
    {
        return (strpos($response, 'sys status 0x') === 0);
    }
}
