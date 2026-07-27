<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit;

use Arkitect\ClassSet;
use Arkitect\Rules\DSL\ArchRule;
use PHPUnit\Framework\Assert;

/**
 * Adds architectural assertions to any test case.
 *
 * Use this trait when your test already extends a base class of your own;
 * otherwise extend ArchRuleTestCase, which pulls the trait in for you.
 */
trait ArchRuleAsserts
{
    /**
     * Asserts that every class in $classSet satisfies $rule.
     */
    public static function assertArchRule(ArchRule $rule, ClassSet $classSet, string $message = ''): void
    {
        Assert::assertThat($rule, new ArchRuleCheckerConstraintAdapter($classSet), $message);
    }

    /**
     * Asserts that every class in $classSet satisfies all the given rules.
     *
     * Each rule is checked against a fresh constraint, so the failure message
     * points at the first rule that is not satisfied.
     *
     * @param list<ArchRule> $rules
     */
    public static function assertArchRules(array $rules, ClassSet $classSet, string $message = ''): void
    {
        Assert::assertNotEmpty($rules, 'No architectural rule was given to assert.');

        foreach ($rules as $rule) {
            self::assertArchRule($rule, $classSet, $message);
        }
    }
}
