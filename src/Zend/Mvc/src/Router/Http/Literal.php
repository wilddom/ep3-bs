<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router\Http;

use Traversable;
use Zend\Mvc\Router\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Literal route.
 */
class Literal implements RouteInterface
{
    /**
     * Priority used for route stacks.
     *
     * @var int
     */
    public $priority;

    /**
     * RouteInterface to match.
     *
     * @var string
     */
    protected $route;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Create a new literal route.
     *
     * @param  string $route
     * @param  array  $defaults
     */
    public function __construct($route, array $defaults = [])
    {
        $this->route    = $route;
        $this->defaults = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Literal
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = [];
        }

        return new static($options['route'], $options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @param  Request      $request
     * @param  integer|null $pathOffset
     *
     * @return void
     *@see    \Zend\Mvc\Router\RouteInterface::match()
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset !== null) {
            if ($pathOffset >= 0 && strlen($path) >= $pathOffset && !empty($this->route)) {
                if (strpos($path, $this->route, $pathOffset) === $pathOffset) {
                    return new RouteMatch($this->defaults, strlen($this->route));
                }
            }

            return;
        }

        if ($path === $this->route) {
            return new RouteMatch($this->defaults, strlen($this->route));
        }

        return;
    }

    /**
     * assemble(): Defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = [], array $options = [])
    {
        return $this->route;
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return [];
    }
}
