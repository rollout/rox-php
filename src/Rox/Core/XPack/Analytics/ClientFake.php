<?php

namespace Rox\Core\XPack\Analytics;

use Rox\Core\XPack\Analytics\Model\Event;

class ClientFake implements ClientInterface
{
    function track(Event $event)
    {
        // Stub
    }
}