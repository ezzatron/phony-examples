<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Matcher\MatcherDriverInterface;

/**
 * A matcher driver for PHPUnit constraints.
 */
class PhpunitMatcherDriver implements MatcherDriverInterface
{
    /**
     * If the supplied matcher is supported, replace it with an equivalent Phony
     * matcher.
     *
     * @param object &$matcher The matcher to adapt.
     *
     * @return boolean True if the matcher is supported.
     */
    public function adapt(&$matcher)
    {
        if (is_a($matcher, 'PHPUnit_Framework_Constraint')) {
            $matcher = new PhpunitMatcher($matcher);

            return true;
        }

        return false;
    }
}
