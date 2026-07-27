<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit\Tests;

use Arkitect\ClassSet;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\Implement;
use Arkitect\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\PHPUnit\ArchRuleAsserts;
use Arkitect\Rules\DSL\ArchRule;
use Arkitect\Rules\Rule;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ArchRuleAssertsTest extends TestCase
{
    use ArchRuleAsserts;

    public function test_assert_arch_rule_passes_on_a_satisfied_rule(): void
    {
        self::assertArchRule(self::satisfiedRule(), self::mvcClassSet());
    }

    public function test_assert_arch_rule_fails_on_a_violated_rule(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('should implement App\ContainerAwareInterface because i said so');

        self::assertArchRule(self::violatedRule(), self::mvcClassSet());
    }

    public function test_assert_arch_rule_prepends_the_custom_message(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('controllers must be container aware');

        self::assertArchRule(self::violatedRule(), self::mvcClassSet(), 'controllers must be container aware');
    }

    public function test_assert_arch_rules_passes_when_every_rule_is_satisfied(): void
    {
        self::assertArchRules(
            [
                self::satisfiedRule(),
                Rule::allClasses()
                    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
                    ->should(new NotHaveDependencyOutsideNamespace('App'))
                    ->because('controllers should not leak outside App'),
            ],
            self::mvcClassSet()
        );
    }

    public function test_assert_arch_rules_fails_on_the_first_violated_rule(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('should implement App\ContainerAwareInterface because i said so');

        self::assertArchRules([self::satisfiedRule(), self::violatedRule()], self::mvcClassSet());
    }

    public function test_assert_arch_rules_rejects_an_empty_rule_list(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('No architectural rule was given to assert.');

        self::assertArchRules([], self::mvcClassSet());
    }

    private static function mvcClassSet(): ClassSet
    {
        return ClassSet::fromDir(__DIR__.'/_fixtures/mvc');
    }

    private static function satisfiedRule(): ArchRule
    {
        return Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
            ->should(new HaveNameMatching('*Controller'))
            ->because('i said so');
    }

    private static function violatedRule(): ArchRule
    {
        return Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
            ->should(new Implement('App\ContainerAwareInterface'))
            ->because('i said so');
    }
}
