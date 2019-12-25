<?php

namespace Rox\Core\Network;

use Kevinrob\GuzzleCache\Strategy\Delegate\RequestMatcherInterface;
use Psr\Http\Message\RequestInterface;
use Rox\Core\Consts\Environment;

class CdnRequestMatcher implements RequestMatcherInterface
{
    /**
     * @inheritDoc
     */
    public function matches(RequestInterface $request)
    {
        $uri = (string)$request->getUri();
        return false !== strpos($uri, Environment::getCdnPath()) ||
            false !== strpos($uri, Environment::getStateCdnPath());
    }
}
