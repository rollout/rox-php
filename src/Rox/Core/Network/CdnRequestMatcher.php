<?php

namespace Rox\Core\Network;

use Kevinrob\GuzzleCache\Strategy\Delegate\RequestMatcherInterface;
use Psr\Http\Message\RequestInterface;
use Rox\Core\Consts\Environment;

class CdnRequestMatcher implements RequestMatcherInterface
{
    private $_environment;

    public function __construct($environment)
    {
        $this->_environment = $environment;
    }

    /**
     * @inheritDoc
     */
    public function matches(RequestInterface $request)
    {
        $uri = (string)$request->getUri();
        return false !== strpos($uri, $this->_environment->getConfigCDNPath()) ||
            false !== strpos($uri, $this->_environment->sendStateCDNPath());
    }
}
