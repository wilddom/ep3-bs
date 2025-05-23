<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Exception\ExceptionInterface as SessionException;
use Zend\Session\Storage\Factory;
use Zend\Session\Storage\StorageInterface;

class StorageFactory implements FactoryInterface
{
    /**
     * Create session storage object (v3 usage).
     *
     * Uses "session_storage" section of configuration to seed a StorageInterface
     * instance. That array should contain the key "type", specifying the storage
     * type to use, and optionally "options", containing any options to be used in
     * creating the StorageInterface instance.
     *
     * @param  ContainerInterface         $container
     * @return StorageInterface
     * @throws ServiceNotCreatedException if session_storage is missing, or the
     *         factory cannot create the storage instance.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');
        if (! isset($config['session_storage']) || ! is_array($config['session_storage'])) {
            throw new ServiceNotCreatedException(
                'Configuration is missing a "session_storage" key, or the value of that key is not an array'
            );
        }

        $config = $config['session_storage'];
        if (! isset($config['type'])) {
            throw new ServiceNotCreatedException(
                '"session_storage" configuration is missing a "type" key'
            );
        }
        $type = $config['type'];
        $options = $config['options'] ?? [];

        try {
            $storage = Factory::factory($type, $options);
        } catch (SessionException $e) {
            throw new ServiceNotCreatedException(sprintf(
                'Factory is unable to create StorageInterface instance: %s',
                $e->getMessage()
            ), $e->getCode(), $e);
        }

        return $storage;
    }

    /**
     * Create and return a storage instance (v2 usage).
     *
     * @param ServiceLocatorInterface $services
     * @param null|string $canonicalName
     * @param string $requestedName
     * @return StorageInterface
     */
    public function createService(
        ServiceLocatorInterface $services,
        $canonicalName = null,
        $requestedName = StorageInterface::class
    ) {
        return $this($services, $requestedName);
    }
}
