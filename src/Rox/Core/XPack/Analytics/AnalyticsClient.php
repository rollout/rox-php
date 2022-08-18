<?php

namespace Rox\Core\XPack\Analytics;

use Psr\Log\LoggerInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Consts\Environment;
use Rox\Core\Consts\PropertyType;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Network\HttpClientInterface;
use Rox\Core\Utils\TimeUtils;
use Rox\Core\XPack\Analytics\Model\Event;

class AnalyticsClient implements ClientInterface
{
    /**
     * @var DevicePropertiesInterface $_deviceProperties
     */
    private $_deviceProperties;

    /**
     * @var HttpClientInterface
     */
    private $_httpClient;

    /**
     * @var LoggerInterface
     */
    private $_log;

    /**
     * @var array $_queue
     */
    private $_queue = array();

    /**
     * @var int $_max_queue_size
     */
    private $_max_queue_size = 1000;

    /**
     * @var int $_batch_size
     */
    private $_batch_size = 100;

    /**
     * @var int $_maximum_backoff_duration
     */
    private $_maximum_backoff_duration = 10000;    // Set maximum waiting limit to 10s

    /**
     * AnalyticsClient constructor.
     *
     * @param DevicePropertiesInterface $deviceProperties
     * @param InternalFlagsInterface $internalFlags
     * @param HttpClientInterface $httpClient
     */
    public function __construct(
        DevicePropertiesInterface $deviceProperties,
        InternalFlagsInterface $internalFlags,
        HttpClientInterface $httpClient)
    {
        $this->_deviceProperties = $deviceProperties;
        $this->_httpClient = $httpClient;
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $maxQueueSize = $internalFlags->getIntValue("rox.internal.analytics.max_queue_size");
        if ($maxQueueSize > 0) {
            $this->_max_queue_size = $maxQueueSize;
        }
        $batchSize = $internalFlags->getIntValue("rox.internal.analytics.batch_size");
        if ($batchSize > 0) {
            $this->_batch_size = $batchSize;
        }
    }

    public function __destruct()
    {
        $this->flush();
    }

    /**
     * @inheritDoc
     */
    function track(Event $event)
    {
        return $this->enqueue($event);
    }

    /**
     * Flushes our queue of messages by batching them to the server
     */
    public function flush()
    {
        $count = count($this->_queue);
        $success = true;
        while ($count > 0 && $success) {
            $batch = array_splice($this->_queue, 0, min($this->_batch_size, $count));
            $success = $this->_flushBatch($batch);
            $count = count($this->_queue);
        }
        return $success;
    }

    /**
     * @param array $messages
     * @return boolean whether the request succeeded
     */
    private function _flushBatch(array $messages)
    {
        $batch = $this->_createBatch($messages);
        $url = Environment::getAnalyticsPath() . "/impression/" . $this->_deviceProperties->getRolloutKey();
        $response = $this->_httpClient->postJson($url, $batch);
        $statusCode = $response->getStatusCode();
        if (200 == $statusCode) {
            return true;
        }
        $this->_log->error("Failed to post data to ${url}: HTTP response code ${statusCode} ({$response->getContent()->readAsString()})");

        return false;
    }

    /**
     * @param Event $event
     * @return boolean whether call has succeeded
     */
    protected function enqueue(Event $event)
    {
        $count = count($this->_queue);
        if ($count > $this->_max_queue_size) {
            return false;
        }
        $count = array_push($this->_queue, $event);
        if ($count >= $this->_batch_size) {
            return $this->flush(); // return ->flush() result: true on success
        }
        return true;
    }

    /**
     * @param array $events
     * @return array
     */
    private function _createBatch($events)
    {
        return array(
            'analyticsVersion' => '1.0.0',
            'sdkVersion' => $this->_deviceProperties->getLibVersion(),
            'time' => (int)TimeUtils::currentTimeMillis(),
            'platform' => $this->_deviceProperties->getAllProperties()[PropertyType::getPlatform()->getName()],
            'rolloutKey' => $this->_deviceProperties->getRolloutKey(),
            'events' => $events
        );
    }
}
