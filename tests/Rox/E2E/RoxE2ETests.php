<?php

namespace Rox\E2E;

use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Rox\Core\Configuration\ConfigurationFetchedArgs;
use Rox\Core\Configuration\ConfigurationFetchedInvokerInterface;
use Rox\Core\Configuration\FetcherStatus;
use Rox\Core\Consts\Environment;
use Rox\Core\Context\ContextBuilder;
use Rox\Core\Impression\ImpressionArgs;
use Rox\Core\Impression\ImpressionInvokerInterface;
use Rox\Core\Logging\TestLoggerFactory;
use Rox\Core\Network\GuzzleHttpClientFactory;
use Rox\Core\Network\GuzzleHttpClientOptions;
use Rox\RoxTestCase;
use Rox\Server\Rox;
use Rox\Server\RoxOptions;
use Rox\Server\RoxOptionsBuilder;

class RoxE2ETests extends RoxTestCase
{
    /**
     * @var TestLoggerFactory $_staticLoggerFactory
     */
    private static $_staticLoggerFactory;

    public static function setUpBeforeClass()
    {
        $_ENV[Environment::ENV_VAR_NAME] = Environment::QA;

        self::$_staticLoggerFactory = new TestLoggerFactory();

        $options = new RoxOptions((new RoxOptionsBuilder())
            ->setConfigurationFetchedHandler(function (ConfigurationFetchedInvokerInterface $sender, ConfigurationFetchedArgs $args) {
                if ($args != null && $args->getFetcherStatus() == FetcherStatus::AppliedFromNetwork) {
                    TestVars::$configurationFetchedCount++;
                }
            })
            ->setImpressionHandler(function (ImpressionInvokerInterface $sender, ImpressionArgs $args) {
                if ($args != null && $args->getReportingValue() != null) {
                    if ($args->getReportingValue()->getName() == "flagForImpression") {
                        TestVars::$isImpressionRaised = true;
                    }
                }
                TestVars::$impressionReturnedArgs = $args;
            })
            ->setDevModeKey("01fcd0d21eeaed9923dff6d8")
            ->setDistinctId(self::class)
            ->setHttpClientFactory(
                new GuzzleHttpClientFactory(
                    (new GuzzleHttpClientOptions())
                        ->setLogCacheHitsAndMisses(true)
                        ->setNoCachePaths([Environment::getStateCdnPath()])
                        ->addMiddleware(new CacheMiddleware(
                            new GreedyCacheStrategy(null, 180)
                        ), 'cache'))
            )->setLoggerFactory(self::$_staticLoggerFactory));

        Rox::register("", Container::getInstance());
        TestCustomPropsCreator::createCustomProps();

        Rox::setup("5b3356d00d81206da3055bc0", $options);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->expectNoErrors();
        $this->expectNoWarnings();
    }

    public function testSimpleFlag()
    {
        $this->assertTrue(Container::getInstance()->simpleFlag->isEnabled());
    }

    public function testSimpleFlagOverwritten()
    {
        $this->assertFalse(Container::getInstance()->simpleFlagOverwritten->isEnabled());
    }

    public function testVariant()
    {
        $this->assertEquals(Container::getInstance()->variant->getValue(), "red");
    }

    public function testVariantOverwritten()
    {
        $this->assertEquals(Container::getInstance()->variantOverwritten->getValue(), "green");
    }

    public function testAllCustomProperties()
    {
        $this->assertTrue(Container::getInstance()->flagCustomProperties->isEnabled());

        $this->assertTrue(TestVars::$isComputedBooleanPropCalled);
        $this->assertTrue(TestVars::$isComputedDoublePropCalled);
        $this->assertTrue(TestVars::$isComputedIntPropCalled);
        $this->assertTrue(TestVars::$isComputedSemverPropCalled);
        $this->assertTrue(TestVars::$isComputedStringPropCalled);
    }

    public function testFetchWithinTimeout()
    {
        $numberOfConfigFetches = TestVars::$configurationFetchedCount;

        $time = time();
        Rox::fetch();
        $secondsPassed = time() - $time;

        $this->assertTrue($secondsPassed <= 5);
        $this->assertTrue($numberOfConfigFetches < TestVars::$configurationFetchedCount);
    }

    public function testVariantWithContext()
    {
        $somePositiveContext = (new ContextBuilder())->build([
            "isDuckAndCover" => true
        ]);

        $someNegativeContext = (new ContextBuilder())->build([
            "isDuckAndCover" => false
        ]);

        $this->assertEquals(Container::getInstance()->variantWithContext->getValue(), "red");

        $this->assertEquals(Container::getInstance()->variantWithContext->getValue($somePositiveContext), "blue");
        $this->assertEquals(Container::getInstance()->variantWithContext->getValue($someNegativeContext), "red");
    }

    public function testTargetGroupsAllAnyNone()
    {
        TestVars::$targetGroup1 = TestVars::$targetGroup2 = true;

        $this->assertTrue(Container::getInstance()->flagTargetGroupsAll->isEnabled());
        $this->assertTrue(Container::getInstance()->flagTargetGroupsAny->isEnabled());
        $this->assertFalse(Container::getInstance()->flagTargetGroupsNone->isEnabled());

        TestVars::$targetGroup1 = false;
        $this->assertFalse(Container::getInstance()->flagTargetGroupsAll->isEnabled());
        $this->assertTrue(Container::getInstance()->flagTargetGroupsAny->isEnabled());
        $this->assertFalse(Container::getInstance()->flagTargetGroupsNone->isEnabled());

        TestVars::$targetGroup2 = false;
        $this->assertFalse(Container::getInstance()->flagTargetGroupsAll->isEnabled());
        $this->assertFalse(Container::getInstance()->flagTargetGroupsAny->isEnabled());
        $this->assertTrue(Container::getInstance()->flagTargetGroupsNone->isEnabled());
    }

    public function testImpressionHandler()
    {
        Container::getInstance()->flagForImpression->isEnabled();
        $this->assertTrue(TestVars::$isImpressionRaised);
        TestVars::$isImpressionRaised = false;

        $context = (new ContextBuilder())->build(["var" => "val"]);
        $flagImpressionValue = Container::getInstance()->flagForImpressionWithExperimentAndContext->isEnabled($context);
        $this->assertNotNull(TestVars::$impressionReturnedArgs);
        $this->assertNotNull(TestVars::$impressionReturnedArgs->getReportingValue());
        $this->assertEquals("true", TestVars::$impressionReturnedArgs->getReportingValue()->getValue());
        $this->assertTrue($flagImpressionValue);
        $this->assertEquals("flagForImpressionWithExperimentAndContext", TestVars::$impressionReturnedArgs->getReportingValue()->getName());

        $this->assertNotNull(TestVars::$impressionReturnedArgs);
        $this->assertNotNull(TestVars::$impressionReturnedArgs->getExperiment());
        $this->assertEquals("5b3cc569f452c215921a4a9c", TestVars::$impressionReturnedArgs->getExperiment()->getIdentifier());
        $this->assertEquals("flag for impression with experiment and context", TestVars::$impressionReturnedArgs->getExperiment()->getName());

        $this->assertEquals("val", TestVars::$impressionReturnedArgs->getContext()->get("var"));
    }

    public function testFlagDependency()
    {
        TestVars::$isPropForTargetGroupForDependency = true;
        $this->assertTrue(Container::getInstance()->flagForDependency->isEnabled());
        $this->assertFalse(Container::getInstance()->flagDependent->isEnabled());

        TestVars::$isPropForTargetGroupForDependency = false;
        $this->assertTrue(Container::getInstance()->flagDependent->isEnabled());
        $this->assertFalse(Container::getInstance()->flagForDependency->isEnabled());
    }

    public function testVariantDependencyWithContext()
    {
        $somePositiveContext = (new ContextBuilder())->build(["isDuckAndCover" => true]);

        $someNegativeContext = (new ContextBuilder())->build(["isDuckAndCover" => false]);

        $this->assertEquals("White", Container::getInstance()->flagColorDependentWithContext->getValue());
        $this->assertEquals("White", Container::getInstance()->flagColorDependentWithContext->getValue($someNegativeContext));
        $this->assertEquals("Yellow", Container::getInstance()->flagColorDependentWithContext->getValue($somePositiveContext));
    }

    public function testShouldUseCacheForConfig()
    {
        Rox::fetch();

        $this->assertTrue(self::$_staticLoggerFactory->getLogger()->hasDebugThatPasses(function ($record) {
            return strpos($record['message'], Environment::getCdnPath()) !== false &&
                strpos($record['message'], 'HIT') !== false;
        }));
    }

    public function testShouldNotUseCacheForSendingState()
    {
        Rox::fetch();

        $this->assertFalse(self::$_staticLoggerFactory->getLogger()->hasDebugThatPasses(function ($record) {
            return strpos($record['message'], Environment::getStateCdnPath()) !== false &&
                (strpos($record['message'], 'HIT') !== false ||
                    strpos($record['message'], 'MISS') !== false);
        }));
    }
}
