<?php

namespace Rox\Core\Impression;

interface ImpressionEventHandlerInterface
{
    /**
     * @param ImpressionArgs $args
     */
    function handleEvent(ImpressionArgs $args);
}
