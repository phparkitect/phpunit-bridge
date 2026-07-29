<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit;

use Arkitect\ClassSet;
use Arkitect\Rules\DSL\ArchRule;
use PHPUnit\Framework\Assert;

/**
 * Adds architectural assertions to a test case.
 *
 * This is the entry point of the package: use it in any test class, whatever it
 * extends. It is a trait rather than a base test case on purpose, so it also works
 * where the parent class is already taken by a framework (KernelTestCase and friends).
 */
trait ArchRuleAsserts
{
    /**
     * Asserts that every class in $classSet satisfies $rule.
     *
     * There is deliberately no $message argument: an ArchRule cannot be built without
     * because(), so the reason is already part of the failure message. Anyone who really
     * needs to prepend something can call assertThat() with the constraint directly.
     */
    public static function assertArchRule(ArchRule $rule, ClassSet $classSet): void
    {
        Assert::assertThat($rule, new ArchRuleCheckerConstraintAdapter($classSet));
    }
}
