<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link           http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright      Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license        http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Hydrator\Filter;

use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Zend\Hydrator\Exception\InvalidArgumentException;

/**
 * Filter that includes methods which have no parameters or only optional parameters
 */
class OptionalParametersFilter implements FilterInterface
{
    /**
     * Map of methods already analyzed
     * by {@see OptionalParametersFilter::filter()},
     * cached for performance reasons
     *
     * @var bool[]
     */
    protected static $propertiesCache = [];

    /**
     * {@inheritDoc}
     */
    public function filter($property)
    {
        if (isset(static::$propertiesCache[$property])) {
            return static::$propertiesCache[$property];
        }

        try {
            $reflectionMethod = new ReflectionMethod($property);
        } catch (ReflectionException) {
            throw new InvalidArgumentException(sprintf('Method %s doesn\'t exist', $property));
        }

        $mandatoryParameters = array_filter(
            $reflectionMethod->getParameters(),
            function (ReflectionParameter $parameter) {
                return ! $parameter->isOptional();
            }
        );

        return static::$propertiesCache[$property] = empty($mandatoryParameters);
    }
}
