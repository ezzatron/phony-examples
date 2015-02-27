<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Matcher\Driver\AbstractMatcherDriver;
use Eloquent\Phony\Matcher\Driver\MatcherDriverInterface;
use Eloquent\Phony\Matcher\MatcherInterface;

/**
 * A matcher driver for PHPUnit constraints.
 *
 * @internal
 */
class PhpunitMatcherDriver extends AbstractMatcherDriver
{
    /**
     * Get the static instance of this driver.
     *
     * @return MatcherDriverInterface The static driver.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the matcher class name.
     *
     * @return string The matcher class name.
     */
    protected function matcherClassName()
    {
        return 'PHPUnit_Framework_Constraint';
    }

    /**
     * Wrap the supplied matcher in a Phony matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return MatcherInterface The wrapped matcher.
     */
    protected function wrapMatcher($matcher)
    {
        return new PhpunitMatcher($matcher);
    }

    private static $instance;
}
