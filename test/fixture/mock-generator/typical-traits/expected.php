<?php

namespace Phony\Test;

/**
 * A mock class generated by Phony.
 *
 * @uses \Eloquent\Phony\Test\TestClassB
 * @uses \Iterator
 * @uses \Countable
 * @uses \ArrayAccess
 * @uses \Eloquent\Phony\Test\TestTraitA
 * @uses \Eloquent\Phony\Test\TestTraitB
 *
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with the Phony source code.
 *
 * @link https://github.com/eloquent/phony
 */
class MockGeneratorTypicalTraits
extends \Eloquent\Phony\Test\TestClassB
implements \Eloquent\Phony\Mock\MockInterface,
           \Iterator,
           \Countable,
           \ArrayAccess
{
    use \Eloquent\Phony\Test\TestTraitA;
    use \Eloquent\Phony\Test\TestTraitB;

    const CONSTANT_A = 'constantValueA';
    const CONSTANT_B = 444;

    /**
     * Custom method 'methodA'.
     *
     * @param mixed $a0  Was 'first'.
     * @param mixed &$a1 Was 'second'.
     */
    public static function methodA(
        $a0,
        &$a1
    ) {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Custom method 'methodB'.
     *
     * @param mixed $a0 Was 'first'.
     * @param mixed $a1 Was 'second'.
     * @param mixed $a2 Was 'third'.
     * @param mixed $a3 Was 'fourth'.
     * @param mixed $a4 Was 'fifth'.
     */
    public static function methodB(
        $a0 = null,
        $a1 = 111,
        $a2 = array(),
        $a3 = array('valueA', 'valueB'),
        $a4 = array('keyA' => 'valueA', 'keyB' => 'valueB')
    ) {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAStaticMethodA'.
     *
     * @uses \Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA()
     *
     * @param mixed &$a0 Was 'first'.
     */
    public static function testClassAStaticMethodA(
        &$a0 = null
    ) {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAStaticMethodB'.
     *
     * @uses \Eloquent\Phony\Test\TestClassB::testClassAStaticMethodB()
     *
     * @param mixed $a0  Was 'first'.
     * @param mixed $a1  Was 'second'.
     * @param mixed &$a2 Was 'third'.
     */
    public static function testClassAStaticMethodB(
        $a0,
        $a1,
        &$a2 = null
    ) {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassBStaticMethodA'.
     *
     * @uses \Eloquent\Phony\Test\TestClassB::testClassBStaticMethodA()
     */
    public static function testClassBStaticMethodA()
    {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassBStaticMethodB'.
     *
     * @uses \Eloquent\Phony\Test\TestClassB::testClassBStaticMethodB()
     *
     * @param mixed $a0 Was 'first'.
     * @param mixed $a1 Was 'second'.
     */
    public static function testClassBStaticMethodB(
        $a0,
        $a1
    ) {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Construct a mock.
     */
    public function __construct()
    {
    }

    /**
     * Inherited method 'count'.
     *
     * @uses \Countable::count()
     */
    public function count()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'current'.
     *
     * @uses \Iterator::current()
     */
    public function current()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'key'.
     *
     * @uses \Iterator::key()
     */
    public function key()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Custom method 'methodC'.
     *
     * @param \Eloquent\Phony\Test\TestClassA      $a0 Was 'first'.
     * @param \Eloquent\Phony\Test\TestClassA|null $a1 Was 'second'.
     * @param array                                $a2 Was 'third'.
     * @param array|null                           $a3 Was 'fourth'.
     */
    public function methodC(
        \Eloquent\Phony\Test\TestClassA $a0,
        \Eloquent\Phony\Test\TestClassA $a1 = null,
        array $a2 = array(),
        array $a3 = null
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Custom method 'methodD'.
     */
    public function methodD()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'next'.
     *
     * @uses \Iterator::next()
     */
    public function next()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'offsetExists'.
     *
     * @uses \ArrayAccess::offsetExists()
     *
     * @param mixed $a0 Was 'offset'.
     */
    public function offsetExists(
        $a0
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'offsetGet'.
     *
     * @uses \ArrayAccess::offsetGet()
     *
     * @param mixed $a0 Was 'offset'.
     */
    public function offsetGet(
        $a0
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'offsetSet'.
     *
     * @uses \ArrayAccess::offsetSet()
     *
     * @param mixed $a0 Was 'offset'.
     * @param mixed $a1 Was 'value'.
     */
    public function offsetSet(
        $a0,
        $a1
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'offsetUnset'.
     *
     * @uses \ArrayAccess::offsetUnset()
     *
     * @param mixed $a0 Was 'offset'.
     */
    public function offsetUnset(
        $a0
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'rewind'.
     *
     * @uses \Iterator::rewind()
     */
    public function rewind()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAMethodA'.
     *
     * @uses \Eloquent\Phony\Test\TestClassA::testClassAMethodA()
     */
    public function testClassAMethodA()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAMethodB'.
     *
     * @uses \Eloquent\Phony\Test\TestTraitB::testClassAMethodB()
     *
     * @param mixed $a0  Was 'first'.
     * @param mixed $a1  Was 'second'.
     * @param mixed &$a2 Was 'third'.
     * @param mixed &$a3 Was 'fourth'.
     * @param mixed &$a4 Was 'fifth'.
     */
    public function testClassAMethodB(
        $a0,
        $a1,
        &$a2 = null,
        &$a3 = null,
        &$a4 = null
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassBMethodA'.
     *
     * @uses \Eloquent\Phony\Test\TestClassB::testClassBMethodA()
     */
    public function testClassBMethodA()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassBMethodB'.
     *
     * @uses \Eloquent\Phony\Test\TestClassB::testClassBMethodB()
     *
     * @param mixed &$a0 Was 'first'.
     * @param mixed &$a1 Was 'second'.
     */
    public function testClassBMethodB(
        &$a0,
        &$a1
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'valid'.
     *
     * @uses \Iterator::valid()
     */
    public function valid()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAStaticMethodC'.
     *
     * @uses \Eloquent\Phony\Test\TestClassA::testClassAStaticMethodC()
     */
    protected static function testClassAStaticMethodC()
    {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAStaticMethodD'.
     *
     * @uses \Eloquent\Phony\Test\TestClassA::testClassAStaticMethodD()
     *
     * @param mixed $a0 Was 'first'.
     * @param mixed $a1 Was 'second'.
     */
    protected static function testClassAStaticMethodD(
        $a0,
        $a1
    ) {
        if (isset(self::$_staticStubs[__FUNCTION__])) {
            return self::$_staticStubs[__FUNCTION__]
                ->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAMethodC'.
     *
     * @uses \Eloquent\Phony\Test\TestClassA::testClassAMethodC()
     */
    protected function testClassAMethodC()
    {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Inherited method 'testClassAMethodD'.
     *
     * @uses \Eloquent\Phony\Test\TestClassA::testClassAMethodD()
     *
     * @param mixed &$a0 Was 'first'.
     * @param mixed &$a1 Was 'second'.
     */
    protected function testClassAMethodD(
        &$a0,
        &$a1
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(func_get_args());
        }
    }

    /**
     * Call a static parent method.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

    /**
     * Call a parent method.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

    public static $propertyA = 'valueA';
    public static $propertyB = 222;
    public $propertyC = 'valueC';
    public $propertyD = 333;
    private static $_staticStubs = array();
    private $_stubs = array();
    private $_mockId;
}
