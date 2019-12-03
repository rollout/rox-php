<?php

namespace Rox\Core\Register;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Rox\Core\Entities\RoxContainerInterface;
use Rox\Core\Entities\Variant;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Repositories\FlagRepositoryInterface;

class Registerer
{
    /**
     * @var FlagRepositoryInterface $_flagRepository
     */
    private $_flagRepository;

    /**
     * @var array $_namespaces
     */
    private $_namespaces = [];

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * Registerer constructor.
     * @param FlagRepositoryInterface $_flagRepository
     */
    public function __construct(FlagRepositoryInterface $_flagRepository)
    {
        $this->_flagRepository = $_flagRepository;
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
    }

    /**
     * @param RoxContainerInterface $container
     * @param string $ns
     */
    public function registerInstance(RoxContainerInterface $container, $ns)
    {
        if ($ns === null) {
            throw new InvalidArgumentException("A namespace cannot be null");
        }

        if (in_array($ns, $this->_namespaces)) {
            throw new InvalidArgumentException(sprintf("A container with the given namespace (%s) has already been registered", $ns));
        } else {
            $this->_namespaces[] = $ns;
        }

        try {
            $reflect = new ReflectionClass($container);
            foreach ($reflect->getProperties() as $prop) {
                $prop->setAccessible(true);
                $value = $prop->getValue($container);
                if ($value instanceof Variant) {
                    $name = $prop->getName();
                    if ($ns) {
                        $name = "${ns}.${name}";
                    }
                    $this->_flagRepository->addFlag($value, $name);
                }
            }
        } catch (ReflectionException $e) {
            $type = get_class($container);
            $this->_log->error("Failed to obtain properties of class ${type}", [
                'exception' => $e
            ]);
        }
    }
}
