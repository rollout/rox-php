<?php

namespace Rox\Core\Network;

interface HttpResponseContentInterface
{
    /**
     * @return string
     */
    function readAsString();
}