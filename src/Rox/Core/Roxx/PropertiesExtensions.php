<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextInterface;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\CustomProperties\DynamicPropertiesInterface;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;

class PropertiesExtensions
{
    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var CustomPropertyRepositoryInterface $_propertiesRepository
     */
    private $_propertiesRepository;

    /**
     * @var DynamicPropertiesInterface
     */
    private $_dynamicProperties;

    /**
     * PropertiesExtensions constructor.
     * @param ParserInterface $parser
     * @param CustomPropertyRepositoryInterface $propertiesRepository
     * @param DynamicPropertiesInterface $dynamicProperties
     */
    public function __construct(
        ParserInterface $parser,
        CustomPropertyRepositoryInterface $propertiesRepository,
        DynamicPropertiesInterface $dynamicProperties)
    {
        $this->_parser = $parser;
        $this->_propertiesRepository = $propertiesRepository;
        $this->_dynamicProperties = $dynamicProperties;
    }

    public function extend()
    {
        $this->_parser->addOperator("property",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $propName = (string)$stack->pop();
                $property = $this->_propertiesRepository->getCustomProperty($propName);

                if ($property == null) {
                    $dynamicPropertiesRule = $this->_dynamicProperties->getDynamicPropertiesRule();
                    if ($dynamicPropertiesRule != null) {
                        $value = $dynamicPropertiesRule->invoke($propName, $context);
                        if ($value != null) {
                            if (is_string($value) || is_bool($value)) {
                                $stack->push($value);
                                return;
                            } else if (is_int($value) || is_double($value)) {
                                $stack->push((double)($value));
                                return;
                            }
                        }
                    }
                    $stack->push(TokenType::getUndefined());
                    return;
                }

                if ($property->getType() === CustomPropertyType::getString()) {
                    $value = $property->getValue()->generate($context);
                    if ($value == null) {
                        $stack->push(TokenType::getUndefined());
                    }
                    if ($value != null) {
                        $stack->push($value);
                    }
                    return;
                }

                if ($property->getType() === CustomPropertyType::getInt() ||
                    $property->getType() === CustomPropertyType::getDouble() ||
                    $property->getType() === CustomPropertyType::getBool()) {
                    $value = $property->getValue()->generate($context);
                    if ($value != null) {
                        $stack->push($value);
                    }
                    return;
                }

                $stack->push(TokenType::getUndefined());
            });
    }
}
