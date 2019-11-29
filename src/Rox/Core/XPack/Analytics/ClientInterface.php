<?php

namespace Rox\Core\XPack\Analytics;

use Rox\Core\XPack\Analytics\Model\Event;

interface ClientInterface
{
    function track(Event $event);
}
