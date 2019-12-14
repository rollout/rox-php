<?php

namespace Rox\Core\Client;

use Rox\Core\Context\ContextInterface;
use Rox\Core\Entities\EntitiesProviderInterface;
use Rox\Core\Entities\Flag;
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
     * @param string $name
     * @param string $defaultValue
     * @param ContextInterface|null $context
     * @return bool
     */
    function isEnabled($name, $defaultValue, ContextInterface $context = null)
    {
        $variant = $this->_flagRepository->getFlag($name);
        if ($variant == null) {
            $variant = $this->_entitiesProvider->createFlag($defaultValue);
            $this->_flagRepository->addFlag($variant, $name);
        }

        $flag = $variant;
        if (!$flag instanceof Flag) {
            return $defaultValue;
        }

        $isEnabled = $flag->isEnabled($context, true);
        return $isEnabled !== null ? $isEnabled : $defaultValue;
    }

    /**
     * @param string $name
     * @param string $defaultValue
     * @param array $options
     * @param ContextInterface|null $context
     * @return string
     */
    function getValue($name, $defaultValue, $options = [], ContextInterface $context = null)
    {
        $variant = $this->_flagRepository->getFlag($name);
        if ($variant == null) {
            $variant = $this->_entitiesProvider->createVariant($defaultValue, $options);
            $this->_flagRepository->addFlag($variant, $name);
        }

        $value = $variant->getValue($context, true);
        return $value !== null ? $value : $defaultValue;
    }
}
