<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;

class CalledEventTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sequenceNumber = 111;
        $this->time = 1.11;
        $this->callback = 'implode';
        $this->arguments = array('a', 'b');
        $this->subject = new CalledEvent($this->sequenceNumber, $this->time, $this->callback, $this->arguments);

        $this->callFactory = new TestCallFactory();
    }

    public function testConstructor()
    {
        $this->assertSame($this->sequenceNumber, $this->subject->sequenceNumber());
        $this->assertSame($this->time, $this->subject->time());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertNull($this->subject->call());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame(array($this->subject), $this->subject->events());
        $this->assertSame($this->subject, $this->subject->firstEvent());
        $this->assertSame($this->subject, $this->subject->lastEvent());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CalledEvent($this->sequenceNumber, $this->time);

        $this->assertTrue(is_callable($this->subject->callback()));
        $this->assertNull(call_user_func($this->subject->callback()));
        $this->assertSame(array(), $this->subject->arguments());
    }

    public function testIteration()
    {
        $this->assertSame(array($this->subject), iterator_to_array($this->subject));
    }

    public function testSetCall()
    {
        $call = $this->callFactory->create();
        $this->subject->setCall($call);

        $this->assertSame($call, $this->subject->call());
    }
}
