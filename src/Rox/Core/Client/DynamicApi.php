<?php

namespace Rox\Core\Client;

use InvalidArgumentException;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Entities\EntitiesProviderInterface;
use Rox\Core\Entities\FlagValueConverters;
use Rox\Core\Repositories\FlagRepositoryInterface;

class DynamicApi implements DynamicApiInterface
{
    /**
     * @var FlagRepositoryInterface $_flagRepository
     */
    private $_flagRepository;

    /**
     * @var EntitiesProviderInterface $_entitiesProvider
     */
    private $_entitiesProvider;

    /**
     * DynamicApi constructor.
     * @param FlagRepositoryInterface $flagRepository
     * @param EntitiesProviderInterface $entitiesProvider
     */
    public function __construct(
        FlagRepositoryInterface $flagRepository,
        EntitiesProviderInterface $entitiesProvider)
    {
        $this->_flagRepository = $flagRepository;
        $this->_entitiesProvider = $entitiesProvider;
    }

    /**
     * @inheritDoc
     */
    function isEnabled($name, $defaultValue, ContextInterface $context = null)
    {
        $this->checkName($name);
        $variant = $this->_flagRepository->getFlag($name);
        if ($variant == null) {
            $variant = $this->_entitiesProvider->createFlag($defaultValue);
            $this->_flagRepository->addFlag($variant, $name);
        }
        return $variant->getBooleanValue($context, FlagValueConverters::getInstance()
            ->getBool()->convertToString($defaultValue));
    }

    /**
     * @inheritDoc
     */
    function getValue($name, $defaultValue, $variations = [], ContextInterface $context = null)
    {
        $this->checkName($name);
        $variant = $this->_flagRepository->getFlag($name);
        if ($variant == null) {
            $variant = $this->_entitiesProvider->createString($defaultValue, $variations);
            $this->_flagRepository->addFlag($variant, $name);
        }
        return $variant->getStringValue($context, $defaultValue);
    }

    /**
     * @inheritDoc
     */
    function getInt($name, $defaultValue, $variations = [], ContextInterface $context = null)
    {
        $this->checkName($name);
        $variant = $this->_flagRepository->getFlag($name);
        if ($variant == null) {
            $variant = $this->_entitiesProvider->createInt($defaultValue, $variations);
            $this->_flagRepository->addFlag($variant, $name);
        }
        return $variant->getIntValue($context, FlagValueConverters::getInstance()
            ->getInt()->convertToString($defaultValue));
    }

    /**
     * @inheritDoc
     */
    function getDouble($name, $defaultValue, $variations = [], ContextInterface $context = null)
    {
        $this->checkName($name);
        $variant = $this->_flagRepository->getFlag($name);
        if ($variant == null) {
            $variant = $this->_entitiesProvider->createDouble($defaultValue, $variations);
            $this->_flagRepository->addFlag($variant, $name);
        }
        return $variant->getDoubleValue($context, FlagValueConverters::getInstance()->getDouble()
            ->convertToString($defaultValue));
    }

    private function checkName($name)
    {
        if (!$name) {
            throw new InvalidArgumentException("DynamicApi - name canot be null");
        }
    }
}
