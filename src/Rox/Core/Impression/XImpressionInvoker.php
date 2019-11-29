<?php

namespace Rox\Core\Impression;

use Exception;
use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Consts\PropertyType;
use Rox\Core\Context\ContextInterface;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\Impression\Models\Experiment;
use Rox\Core\Impression\Models\ReportingValue;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;
use Rox\Core\XPack\Analytics\ClientInterface;
use Rox\Core\XPack\Analytics\Model\Event;

class XImpressionInvoker implements ImpressionInvokerInterface
{
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
     * @param InternalFlagsInterface $_internalFlags
     * @param CustomPropertyRepositoryInterface|null $_customPropertyRepository
     * @param ClientInterface|null $_analyticsClient
     */
    public function __construct(
        InternalFlagsInterface $_internalFlags,
        $_customPropertyRepository,
        $_analyticsClient)
    {
        $this->_customPropertyRepository = $_customPropertyRepository;
        $this->_internalFlags = $_internalFlags;
        $this->_analyticsClient = $_analyticsClient;
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
     * @param ExperimentModel $experiment
     * @param ContextInterface $context
     */
    function invoke(ReportingValue $value, ExperimentModel $experiment, ContextInterface $context)
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
                    $propDistinctId = $prop->getValue()->generate($context);
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

            // FIXME: use some logging framework here?
            error_log("Failed to send analytics:: ${e}");
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
