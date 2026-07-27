<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit;

use PHPUnit\Framework\TestCase;

/**
 * Base test case for architectural tests.
 *
 * Extend it to get assertArchRule() and assertArchRules() out of the box:
 *
 *     final class ArchitectureTest extends ArchRuleTestCase
 *     {
 *         public function test_controllers_are_suffixed(): void
 *         {
 *             self::assertArchRule($rule, ClassSet::fromDir(__DIR__.'/../src'));
 *         }
 *     }
 */
abstract class ArchRuleTestCase extends TestCase
{
    use ArchRuleAsserts;
}
