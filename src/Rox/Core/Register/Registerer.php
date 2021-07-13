<?php

namespace Rox\Core\Register;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Rox\Core\Entities\RoxStringBase;
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
     * @param object $container
     * @param string $ns
     */
    public function registerInstance($container, $ns)
    {
        if ($ns === null) {
            throw new InvalidArgumentException("A namespace cannot be null");
        }

        if (in_array($ns, $this->_namespaces)) {
            throw new InvalidArgumentException(sprintf("A container with the given namespace (%s) has already been registered", $ns));
        } else {
            $this->_namespaces[] = $ns;
        }

        // Use get_object_vars() to get most properties (including ones dynamically set)
        $properties = get_object_vars($container);
        try {
            $reflect = new ReflectionClass($container);
            // Use a ReflectionClass to get properties not picked up by get_object_vars() (private & protected ones)
            foreach ($reflect->getProperties() as $prop) {
                $prop->setAccessible(true);
                $properties[$prop->getName()] = $prop->getValue($container);
            }
        } catch (ReflectionException $e) {
            $type = get_class($container);
            $this->_log->error("Failed to obtain properties of class ${type}", [
                'exception' => $e
            ]);
        }

        foreach ($properties as $name => $value) {
            if ($value instanceof RoxStringBase) {
                if ($ns) {
                    $name = "${ns}.${name}";
                }
                $this->_flagRepository->addFlag($value, $name);
            }
        }
    }
}
