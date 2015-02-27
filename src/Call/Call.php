<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use ArrayIterator;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\CallEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Call\Event\TraversableEventInterface;
use Eloquent\Phony\Event\EventInterface;
use Exception;
use Generator;
use InvalidArgumentException;
use Iterator;
use Traversable;

/**
 * Represents a single call.
 *
 * @internal
 */
class Call implements CallInterface
{
    /**
     * Construct a new call.
     *
     * @param CalledEventInterface                          $calledEvent       The 'called' event.
     * @param ResponseEventInterface|null                   $responseEvent     The response event, or null if the call has not yet responded.
     * @param array<integer,TraversableEventInterface>|null $traversableEvents The traversable events.
     * @param ResponseEventInterface|null                   $endEvent          The end event, or null if the call has not yet completed.
     *
     * @throws InvalidArgumentException If the supplied calls respresent an invalid call state.
     */
    public function __construct(
        CalledEventInterface $calledEvent,
        ResponseEventInterface $responseEvent = null,
        array $traversableEvents = null,
        ResponseEventInterface $endEvent = null
    ) {
        $calledEvent->setCall($this);
        $this->calledEvent = $calledEvent;

        $this->traversableEvents = array();

        if ($responseEvent) {
            $this->setResponseEvent($responseEvent);
        }

        if (null !== $traversableEvents) {
            foreach ($traversableEvents as $traversableEvent) {
                $this->addTraversableEvent($traversableEvent);
            }
        }

        if ($endEvent) {
            $this->setEndEvent($endEvent);
        }
    }

    /**
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->calledEvent->sequenceNumber();
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time()
    {
        return $this->calledEvent->time();
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return true;
    }

    /**
     * Get the first event.
     *
     * @return EventInterface|null The first event, or null if there are no events.
     */
    public function firstEvent()
    {
        return $this;
    }

    /**
     * Get the last event.
     *
     * @return EventInterface|null The last event, or null if there are no events.
     */
    public function lastEvent()
    {
        if ($this->endEvent) {
            return $this->endEvent;
        }

        if ($this->traversableEvents) {
            return
                $this->traversableEvents[count($this->traversableEvents) - 1];
        }

        if ($this->responseEvent) {
            return $this->responseEvent;
        }

        return $this;
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator(array($this));
    }

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        return 1;
    }

    /**
     * Get the 'called' event.
     *
     * @return CalledEventInterface The 'called' event.
     */
    public function calledEvent()
    {
        return $this->calledEvent;
    }

    /**
     * Set the response event.
     *
     * @param ResponseEventInterface $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already responded.
     */
    public function setResponseEvent(ResponseEventInterface $responseEvent)
    {
        if ($this->responseEvent) {
            throw new InvalidArgumentException('Call already responded.');
        }

        $responseEvent->setCall($this);
        $this->responseEvent = $responseEvent;

        if (!$this->isGenerator()) {
            $this->endEvent = $responseEvent;
        }
    }

    /**
     * Get the response event.
     *
     * @return ResponseEventInterface|null The response event, or null if the call has not yet responded.
     */
    public function responseEvent()
    {
        return $this->responseEvent;
    }

    /**
     * Add a traversable event.
     *
     * @param TraversableEventInterface $traversableEvent The traversable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addTraversableEvent(
        TraversableEventInterface $traversableEvent
    ) {
        if (!$this->isTraversable()) {
            throw new InvalidArgumentException('Not a traversable call.');
        }
        if ($this->endEvent && $this->isGenerator()) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $traversableEvent->setCall($this);
        $this->traversableEvents[] = $traversableEvent;
    }

    /**
     * Get the traversable events.
     *
     * @return array<integer,TraversableEventInterface> The traversable events.
     */
    public function traversableEvents()
    {
        return $this->traversableEvents;
    }

    /**
     * Set the end event.
     *
     * @param ResponseEventInterface $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(ResponseEventInterface $endEvent)
    {
        if ($this->endEvent) {
            throw new InvalidArgumentException('Call already completed.');
        }

        $endEvent->setCall($this);

        if (!$this->responseEvent) {
            $this->responseEvent = $endEvent;
        }

        $this->endEvent = $endEvent;
    }

    /**
     * Get the end event.
     *
     * @return ResponseEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent()
    {
        return $this->endEvent;
    }

    /**
     * Get the events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function events()
    {
        $events = $this->traversableEvents();

        if ($this->responseEvent) {
            if ($this->endEvent && $this->isGenerator()) {
                $events[] = $this->endEvent;
            }

            array_unshift($events, $this->responseEvent);
        }

        array_unshift($events, $this->calledEvent);

        return $events;
    }

    /**
     * Returns true if this call has responded.
     *
     * @return boolean True if this call has responded.
     */
    public function hasResponded()
    {
        return $this->responseEvent && true;
    }

    /**
     * Returns true if this call has responded with a traversable.
     *
     * @return boolean True if this call has responded with a traversable.
     */
    public function isTraversable()
    {
        if (!$this->responseEvent instanceof ReturnedEventInterface) {
            return false;
        }

        $returnValue = $this->responseEvent->value();

        return is_array($returnValue) || $returnValue instanceof Traversable;
    }

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return boolean True if this call has responded with a generator.
     */
    public function isGenerator()
    {
        return $this->responseEvent instanceof ReturnedEventInterface &&
            $this->responseEvent->value() instanceof Generator;
    }

    /**
     * Returns true if this call has completed.
     *
     * @return boolean True if this call has completed.
     */
    public function hasCompleted()
    {
        return $this->endEvent && true;
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback()
    {
        return $this->calledEvent->callback();
    }

    /**
     * Get the received arguments.
     *
     * @return ArgumentsInterface The received arguments.
     */
    public function arguments()
    {
        return $this->calledEvent->arguments();
    }

    /**
     * Get the returned value.
     *
     * @return mixed The returned value.
     */
    public function returnValue()
    {
        if ($this->responseEvent instanceof ReturnedEventInterface) {
            return $this->responseEvent->value();
        }
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|null The thrown exception, or null if no exception was thrown.
     */
    public function exception()
    {
        if ($this->endEvent instanceof ThrewEventInterface) {
            return $this->endEvent->exception();
        }
    }

    /**
     * Get the time at which the call responded.
     *
     * @return float|null The time at which the call responded, in seconds since the Unix epoch, or null if the call has not yet responded.
     */
    public function responseTime()
    {
        if ($this->responseEvent) {
            return $this->responseEvent->time();
        }
    }

    /**
     * Get the time at which the call completed.
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime()
    {
        if ($this->endEvent) {
            return $this->endEvent->time();
        }
    }

    private $calledEvent;
    private $responseEvent;
    private $traversableEvents;
    private $endEvent;
}
