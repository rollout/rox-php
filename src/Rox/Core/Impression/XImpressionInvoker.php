<?php

namespace Rox\Core\Impression;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Consts\PropertyType;
use Rox\Core\Context\ContextInterface;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\Impression\Models\Experiment;
use Rox\Core\Impression\Models\ReportingValue;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;
use Rox\Core\XPack\Analytics\ClientInterface;
use Rox\Core\XPack\Analytics\Model\Event;

class XImpressionInvoker implements ImpressionInvokerInterface
{
    /**
     * @var LoggerInterface
     */
    private $_log;

    /**
     * @var CustomPropertyRepositoryInterface $_customPropertyRepository
     */
    private $_customPropertyRepository;

    /**
     * @var InternalFlagsInterface $_customPropertyRepository
     */
    private $_internalFlags;

    /**
     * @var ClientInterface $_analyticsClient
     */
    private $_analyticsClient;

    /**
     * @var callable[] $_eventHandlers
     */
    private $_eventHandlers = [];

    /**
     * XImpressionInvoker constructor.
     * @param InternalFlagsInterface $internalFlags
     * @param CustomPropertyRepositoryInterface|null $customPropertyRepository
     * @param ClientInterface|null $analyticsClient
     */
    public function __construct(
        InternalFlagsInterface $internalFlags,
        $customPropertyRepository,
        $analyticsClient)
    {
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $this->_customPropertyRepository = $customPropertyRepository;
        $this->_internalFlags = $internalFlags;
        $this->_analyticsClient = $analyticsClient;
    }

    /**
     * @param callable $handler
     */
    function register(callable $handler)
    {
        if ($handler == null) {
            return;
        }
        if (array_search($handler, $this->_eventHandlers) === false) {
            array_push($this->_eventHandlers, $handler);
        }
    }

    /**
     * @param ReportingValue $value
     * @param ExperimentModel|null $experiment
     * @param ContextInterface|null $context
     */
    function invoke(ReportingValue $value, $experiment, $context)
    {
        try {
            $internalExperiment = $this->_internalFlags->isEnabled('rox.internal.analytics');
            if ($internalExperiment && $experiment != null && $this->_analyticsClient != null) {
                $prop = $this->_customPropertyRepository->getCustomProperty($experiment->getStickinessProperty());
                if ($prop === null) {
                    $prop = $this->_customPropertyRepository->getCustomProperty('rox.' . PropertyType::getDistinctId()->getName());
                }
                $distinctId = '(null_distinct_id';
                if ($prop != null && $prop->getType() === CustomPropertyType::getString()) {
                    $propDistinctId = $prop->getValue()($context);
                    if ($propDistinctId !== null) {
                        $distinctId = $propDistinctId;
                    }
                }
                $this->_analyticsClient->track((new Event())
                    ->setFlag($value->getName())
                    ->setValue($value->getValue())
                    ->setDistinctId($distinctId)
                    ->setExperimentId($experiment->getId()));
            }
        } catch (Exception $e) {

            $this->_log->error("Failed to send analytics", [
                'exception' => $e
            ]);
        }

        $this->_fireImpression(new ImpressionArgs($value,
            $experiment != null
                ? new Experiment($experiment)
                : null, $context));
    }

    /**
     * @param ImpressionArgs $args
     */
    private function _fireImpression(ImpressionArgs $args)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler($this, $args);
        }
    }
}
