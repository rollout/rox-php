<?php

namespace Rox\Core\Utils;

class Debouncer
{
    /**
     * @var int $_cancelUntil
     */
    private $_cancelUntil;

    /**
     * @var int $_intervalInMilliseconds
     */
    private $_intervalInMilliseconds;

    /**
     * @var callable $_taskToRun
     */
    private $_taskToRun;

    /**
     * Debouncer constructor.
     * @param $interval
     * @param callable $action
     */
    public function __construct($interval, callable $action)
    {
        $this->_cancelUntil = TimeUtils::currentTimeMillis() + $interval;
        $this->_intervalInMilliseconds = $interval;
        $this->_taskToRun = $action;
    }

    public function invoke()
    {
        $now = TimeUtils::currentTimeMillis();
        if ($now < $this->_cancelUntil) {
            return;
        }

        $this->_cancelUntil = $now + $this->_intervalInMilliseconds;
        $taskToRun = $this->_taskToRun;
        $taskToRun();
    }
}
