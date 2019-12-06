<?php

namespace Rox\Core\XPack\Reporting;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Consts\Environment;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Network\HttpClientInterface;
use Rox\Core\Network\StringContent;
use Rox\Core\Reporting\ErrorReporterInterface;

class XErrorReporter implements ErrorReporterInterface
{
    const BUGSNAG_NOTIFY_URL = "https://notify.bugsnag.com";

    /**
     * @var DevicePropertiesInterface $_deviceProperties
     */
    private $_deviceProperties;

    /**
     * @var BUIDInterface $_buid
     */
    private $_buid;

    /**
     * @var HttpClientInterface $_request
     */
    private $_request;

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * XErrorReporter constructor.
     * @param HttpClientInterface $request
     * @param DevicePropertiesInterface $deviceProperties
     * @param BUIDInterface $buid
     */
    public function __construct(
        HttpClientInterface $request,
        DevicePropertiesInterface $deviceProperties,
        BUIDInterface $buid)
    {
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $this->_deviceProperties = $deviceProperties;
        $this->_buid = $buid;
        $this->_request = $request;
    }

    /**
     * @inheritDoc
     */
    function report($message, Exception $ex)
    {
        if ($this->_deviceProperties->getRolloutEnvironment() === Environment::LOCAL) {
            return;
        }

        $this->_log->error("Error report: " . $message, [
            'exception' => $ex
        ]);

        $payload = [];
        try {
            $payload = $this->_createPayload($message, $ex);
        } catch (Exception $e) {
            $this->_log->error("failed to create bugsnag json payload of the error", [
                'exception' => $e
            ]);
        }

        $this->_sendError($payload);
    }

    /**
     * @param array $payload
     */
    private function _sendError(array &$payload)
    {
        $this->_log->debug("Sending bugsnag error report...");

        try {
            $this->_request->postJson(self::BUGSNAG_NOTIFY_URL, $payload);
            $this->_log->debug("Bugsnag error report was sent");
        } catch (Exception $e) {
            $this->_log->error("Failed to send bugsnag error ", [
                'exception' => $e
            ]);
        }
    }

    /**
     * @param string $message
     * @param Exception $ex
     * @return array
     */
    private function _createPayload($message, Exception $ex)
    {
        $payload = [];
        $this->_addApiKey($payload);
        $this->_addNotifier($payload);
        $this->_addEvents($message, $ex, $payload);
        return $payload;
    }

    /**
     * @param string $message
     * @param array $ev
     */
    private function _addMetaData($message, array &$ev)
    {
        $metaData = [];
        $innerData = [
            "message" => $message,
            "deviceId" => $this->_deviceProperties->getDistinctId(),
            "buid" => (string)$this->_buid
        ];
        $metaData["data"] = $innerData;
        $ev["metaData"] = $metaData;
    }

    /**
     * @param array $payload
     */
    private function _addApiKey(array &$payload)
    {
        $payload["apiKey"] = "9569ec14f61546c6aa2a97856492bf4d";
    }

    /**
     * @param string $message
     * @param Exception $ex
     * @param array $payload
     */
    private function _addEvents($message, Exception $ex, array &$payload)
    {
        $evs = [];
        $this->_addEvent($message, $ex, $evs);
        $payload["events"] = $evs;
    }

    /**
     * @param string $message
     * @param Exception $ex
     * @param array $events
     */
    private function _addEvent($message, Exception $ex, array &$events)
    {
        $ev = $this->_addPayloadVersion();
        $this->_addExceptions($message, $ex, $ev);
        $this->_addUser("id", $this->_deviceProperties->getRolloutKey(), $ev);
        $this->_addMetaData($message, $ev);
        $this->_addApp($ev);
        $events[] = $ev;
    }

    /**
     * @return array
     */
    private function _addPayloadVersion()
    {
        $ev = ["payloadVersion" => "2"];
        return $ev;
    }

    /**
     * @param array $payload
     */
    private function _addNotifier(array &$payload)
    {
        $notifier = [
            "name" => "Rollout PHP SDK",
            "version" => $this->_deviceProperties->getLibVersion()
        ];
        $payload["notifier"] = $notifier;
    }

    /**
     * @param string $id
     * @param string $rolloutKey
     * @param array $ev
     */
    private function _addUser($id, $rolloutKey, array &$ev)
    {
        $user = [$id => $rolloutKey];
        $ev["user"] = $user;
    }

    /**
     * @param string $message
     * @param Exception $ex
     * @param array $ev
     */
    private function _addExceptions($message, Exception $ex, array &$ev)
    {
        $exceptions = [];
        $exception = [];

        if ($ex == null) {
            $exception["errorClass"] = $message;
            $exception["message"] = $message;
            $stacktraces = [];
            $exception["stacktrace"] = $stacktraces;
        } else {

            $exception["errorClass"] = $ex->getFile();
            $exception["message"] = $ex->getMessage();

            $stackTraceObject = null;
            $stackTraceArray = [];
            foreach ($ex->getTrace() as $r) {
                $stackTraceObject = [
                    "file" => isset($r["file"]) ? $r["file"] : null,
                    "line" => isset($r["line"]) ? $r["line"] : null,
                    "method" => isset($r["function"]) ? $r["function"] : null
                ];
                $stackTraceArray[] = $stackTraceObject;
            }
            $exception["stacktrace"] = $stackTraceArray;
        }

        $exceptions[] = $exception;
        $ev["exceptions"] = $exceptions;
    }

    /**
     * @param array $ev
     */
    private function _addApp(array &$ev)
    {
        $app = [
            "releaseStage" => (string)$this->_deviceProperties->getRolloutEnvironment(),
            "version" => $this->_deviceProperties->getLibVersion()
        ];
        $ev["app"] = $app;
    }
}
