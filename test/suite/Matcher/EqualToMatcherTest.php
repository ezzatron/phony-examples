<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use PHPUnit_Framework_TestCase;
use SebastianBergmann\Comparator\Factory;
use SebastianBergmann\Exporter\Exporter;

class EqualToMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->value = 'x';
        $this->comparatorFactory = new Factory();
        $this->exporter = new Exporter();
        $this->subject = new EqualToMatcher($this->value, $this->comparatorFactory, $this->exporter);
    }

    public function testConstructor()
    {
        $this->assertSame($this->value, $this->subject->value());
        $this->assertSame($this->comparatorFactory, $this->subject->comparatorFactory());
        $this->assertSame($this->exporter, $this->subject->exporter());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EqualToMatcher($this->value);

        $this->assertEquals($this->comparatorFactory, $this->subject->comparatorFactory());
        $this->assertEquals($this->exporter, $this->subject->exporter());
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches($this->value));
        $this->assertFalse($this->subject->matches('y'));
    }

    public function testDescribe()
    {
        $this->assertSame("<'x'>", $this->subject->describe());
    }

    public function testDescribeWithMultilineString()
    {
        $this->subject = new EqualToMatcher("line\nline");

        $this->assertSame("<'line\\nline'>", $this->subject->describe());
    }

    public function testDescribeWithNonString()
    {
        $this->subject = new EqualToMatcher(111);

        $this->assertSame("<111>", $this->subject->describe());
    }
}
