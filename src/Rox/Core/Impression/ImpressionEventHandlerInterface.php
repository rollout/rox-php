<?php

namespace Rox\Core\Impression;

interface ImpressionEventHandlerInterface
{
    /**
     * @param ImpressionEventHandlerArgs $args
     */
    function handleEvent(ImpressionEventHandlerArgs $args);
}
