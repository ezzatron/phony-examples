<?php

/**
 * A mock class generated by Phony.
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
class MockGeneratorUndefinedTypeHint
implements Eloquent\Phony\Mock\MockInterface
{
    /**
     * Set the static stubs.
     *
     * @param array<string,Eloquent\Phony\Stub\StubInterface>|null $staticStubs The stubs to use.
     */
    public static function _setStaticStubs(array $staticStubs)
    {
        self::$_staticStubs = $staticStubs;
    }

    /**
     * Construct a mock.
     *
     * @param array<string,Eloquent\Phony\Stub\StubInterface>|null $stubs The stubs to use.
     */
    public function __construct(
        array $stubs = null
    ) {
        if (null === $stubs) {
            $stubs = array();
        }

        $this->_stubs = $stubs;
    }

    /**
     * Custom method 'methodA'.
     *
     * @param Non\Existent      $a0 Originally named 'first'.
     * @param Non\Existent|null $a1 Originally named 'second'.
     */
    public function methodA(
        Non\Existent $a0,
        Non\Existent $a1 = null
    ) {
        if (isset($this->_stubs[__FUNCTION__])) {
            return call_user_func_array(
                $this->_stubs[__FUNCTION__],
                func_get_args()
            );
        }
    }

    private static $_staticStubs = array();
    private $_stubs;
}
