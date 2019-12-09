<?php

namespace Rox\Core\Network;

interface HttpClientFactoryInterface
{
    /**
     * @return HttpClientInterface
     */
    function createHttpClient();
}
