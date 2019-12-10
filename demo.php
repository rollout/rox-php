<?php

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Rox\Core\Consts\Environment;
use Rox\Core\Entities\RoxContainerInterface;
use Rox\Core\Network\GuzzleHttpClientFactory;
use Rox\Core\Network\GuzzleHttpClientOptions;
use Rox\Server\Flags\RoxFlag;
use Rox\Server\Rox;
use Rox\Server\RoxOptions;
use Rox\Server\RoxOptionsBuilder;

const DEFAULT_API_KEY = '5b3356d00d81206da3055bc0';
const DEFAULT_DEV_MODE_KEY = '01fcd0d21eeaed9923dff6d8';

require __DIR__ . '/vendor/autoload.php';

class Container implements RoxContainerInterface
{
    public $demoFlag;

    public function __construct()
    {
        $this->demoFlag = new RoxFlag();
    }
}

if (!isset($_ENV[Environment::ENV_VAR_NAME])) {
    $_ENV[Environment::ENV_VAR_NAME] = Environment::QA;
}

$apiKey = isset($_ENV['ROLLOUT_API_KEY'])
    ? $_ENV['ROLLOUT_API_KEY']
    : DEFAULT_API_KEY;

$devModeKey = isset($_ENV['ROLLOUT_DEV_MODE_KEY'])
    ? $_ENV['ROLLOUT_DEV_MODE_KEY']
    : DEFAULT_DEV_MODE_KEY;

$roxOptionsBuilder = (new RoxOptionsBuilder())
    ->setDevModeKey($devModeKey);

if (!isset($_GET['nocache'])) {

    // Sample caching setup using Doctrine example from https://github.com/Kevinrob/guzzle-cache-middleware

    $roxOptionsBuilder
        ->setDistinctId('rox-php-demo') // Some app-specific ID that would stay unchanged between requests
        ->setHttpClientFactory(new GuzzleHttpClientFactory(
                (new GuzzleHttpClientOptions())
                    ->setLogCacheHitsAndMisses(true)
                    ->setNoCachePaths([Environment::getStateCdnPath()]) // Don't cache state report requests
                    ->addMiddleware(new CacheMiddleware(
                        new GreedyCacheStrategy(
                            new DoctrineCacheStorage(
                                new ChainCache([
                                    new ArrayCache(),
                                    new FilesystemCache('/tmp/rollout/cache'),
                                ])
                            ),
                            1800 // Default cache entry TTL
                        )
                    ), 'cache'))
        );

    echo "Using cache...\n";

} else {

    echo "NOT using cache...\n";

}

$con = new Container();
Rox::register('demo', $con);
Rox::setup($apiKey, new RoxOptions($roxOptionsBuilder));

if ($con->demoFlag->isEnabled()) {
    echo 'demo.demoFlag: FEATURE IS ON';
} else {
    echo 'demo.demoFlag: FEATURE IS OFF';
}
