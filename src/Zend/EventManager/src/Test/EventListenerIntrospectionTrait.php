<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-eventmanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-eventmanager/blob/master/LICENSE.md
 */

namespace Zend\EventManager\Test;

use PHPUnit_Framework_Assert as Assert;
use Zend\EventManager\EventManager;
use Zend\Stdlib\PriorityQueue;

/**
 * Trait providing utility methods and assertions for use in PHPUnit test cases.
 *
 * This trait may be composed into a test case, and provides:
 *
 * - methods for introspecting events and listeners
 * - methods for asserting listeners are attached at a specific priority
 *
 * Some functionality in this trait duplicates functionality present in the
 * version 2 EventManagerInterface and/or EventManager implementation, but
 * abstracts that functionality for use in v3. As such, components or code
 * that is testing for listener registration should use the methods in this
 * trait to ensure tests are forwards-compatible between zend-eventmanager
 * versions.
 */
trait EventListenerIntrospectionTrait
{

    /**
     * Retrieve an interable list of listeners for an event.
     *
     * Given an event and an event manager, returns an iterator with the
     * listeners for that event, in priority order.
     *
     * If $withPriority is true, the key values will be the priority at which
     * the given listener is attached.
     *
     * Do not pass $withPriority if you want to cast the iterator to an array,
     * as many listeners will likely have the same priority, and thus casting
     * will collapse to the last added.
     *
     * @param string $event
     * @param EventManager $events
     * @param bool $withPriority
     * @return \Traversable
     */
    private function getListenersForEvent($event, EventManager $events, $withPriority = false)
    {
        $listeners = $events->getListeners($event);
        return $this->traverseListeners($listeners, $withPriority);
    }

    /**
     * Generator for traversing listeners in priority order.
     *
     * @param PriorityQueue $listeners
     * @param bool $withPriority When true, yields priority as key.
     */
    public function traverseListeners(PriorityQueue $queue, $withPriority = false)
    {
        foreach ($queue as $handler) {
            $listener = $handler->getCallback();
            if ($withPriority) {
                $priority = (int) $handler->getMetadatum('priority');
                yield $priority => $listener;
            } else {
                yield $listener;
            }
        }
    }
}
