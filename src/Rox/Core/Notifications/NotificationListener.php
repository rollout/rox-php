<?php

namespace Rox\Core\Notifications;

class NotificationListener
{
    /**
     * @var string $_listenUrl
     */
    private $_listenUrl;

    /**
     * @var string $_appKey
     */
    private $_appKey;

    /**
     * NotificationListener constructor.
     * @param string $_listenUrl
     * @param string $_appKey
     */
    public function __construct($_listenUrl, $_appKey)
    {
        $this->_listenUrl = $_listenUrl;
        $this->_appKey = $_appKey;
    }

    /**
     * @param string $eventName
     * @param callable $handler
     */
    public function on($eventName, callable $handler)
    {
        // TODO: implement
    }

    public function start()
    {
        // TODO: implement
    }

    public function stop()
    {
        // TODO: implement
    }
}
