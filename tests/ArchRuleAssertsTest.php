<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit\Tests;

use Arkitect\ClassSet;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\Implement;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\PHPUnit\ArchRuleAsserts;
use Arkitect\Rules\DSL\ArchRule;
use Arkitect\Rules\Rule;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ArchRuleAssertsTest extends TestCase
{
    use ArchRuleAsserts;

    public function test_it_passes_on_a_satisfied_rule(): void
    {
        self::assertArchRule(self::satisfiedRule(), self::mvcClassSet());
    }

    public function test_it_fails_on_a_violated_rule(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('should implement App\ContainerAwareInterface because i said so');

        self::assertArchRule(self::violatedRule(), self::mvcClassSet());
    }

    public function test_it_prepends_the_custom_message_to_the_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('controllers must be container aware');

        self::assertArchRule(self::violatedRule(), self::mvcClassSet(), 'controllers must be container aware');
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
