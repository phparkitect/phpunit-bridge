<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit\Tests;

use Arkitect\ClassSet;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\Implement;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\PHPUnit\ArchRuleCheckerConstraintAdapter;
use Arkitect\Rules\DSL\ArchRule;
use Arkitect\Rules\Rule;
use PHPUnit\Framework\TestCase;

class ArchRuleCheckerConstraintAdapterTest extends TestCase
{
    public function test_it_describes_itself(): void
    {
        $constraint = new ArchRuleCheckerConstraintAdapter(self::mvcClassSet());

        self::assertSame('satisfies all architectural constraints', $constraint->toString());
    }

    public function test_it_matches_a_satisfied_rule(): void
    {
        $constraint = new ArchRuleCheckerConstraintAdapter(self::mvcClassSet());

        self::assertTrue($constraint->evaluate(self::satisfiedRule(), '', true));
    }

    public function test_it_does_not_match_a_violated_rule(): void
    {
        $constraint = new ArchRuleCheckerConstraintAdapter(self::mvcClassSet());

        self::assertFalse($constraint->evaluate(self::violatedRule(), '', true));
    }

    public function test_it_reports_every_violation_grouped_by_class(): void
    {
        $constraint = new ArchRuleCheckerConstraintAdapter(self::mvcClassSet());

        try {
            $constraint->evaluate(self::violatedRule());
        } catch (\Throwable $e) {
            self::assertStringContainsString('App\Controller\ProductsController has 1 violations', $e->getMessage());
            self::assertStringContainsString('App\Controller\UserController has 1 violations', $e->getMessage());
            self::assertStringContainsString('should implement App\ContainerAwareInterface because i said so', $e->getMessage());

            return;
        }

        self::fail('The violated rule should have made the constraint fail.');
    }

    public function test_it_reports_parsing_errors_instead_of_violations(): void
    {
        $constraint = new ArchRuleCheckerConstraintAdapter(ClassSet::fromDir(__DIR__.'/_fixtures/parse_error'));

        try {
            $constraint->evaluate(self::violatedRule());
        } catch (\Throwable $e) {
            self::assertStringContainsString('parsing error:', $e->getMessage());
            self::assertStringContainsString('BrokenService.php', $e->getMessage());

            return;
        }

        self::fail('The unparsable fixture should have made the constraint fail.');
    }

    public function test_it_analyzes_the_code_with_the_given_target_php_version(): void
    {
        $constraint = new ArchRuleCheckerConstraintAdapter(self::mvcClassSet(), '8.0');

        self::assertTrue($constraint->evaluate(self::satisfiedRule(), '', true));
    }

    public function test_it_rejects_an_unsupported_target_php_version(): void
    {
        $this->expectException(\Arkitect\Exceptions\PhpVersionNotValidException::class);

        new ArchRuleCheckerConstraintAdapter(self::mvcClassSet(), '5.6');
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
