<?php

namespace Rox\Core\Client;

interface BUIDInterface
{
    /**
     * @return string
     */
    function getValue();

    /**
     * @return array
     */
    function getQueryStringParts();
}
