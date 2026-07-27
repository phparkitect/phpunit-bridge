<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit\Tests;

use Arkitect\ClassSet;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\Implement;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\PHPUnit\ArchRuleTestCase;
use Arkitect\Rules\Rule;
use PHPUnit\Framework\ExpectationFailedException;

class ArchRuleTestCaseTest extends ArchRuleTestCase
{
    public function test_it_inherits_the_architectural_assertions(): void
    {
        $rule = Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
            ->should(new HaveNameMatching('*Controller'))
            ->because('i said so');

        self::assertArchRule($rule, ClassSet::fromDir(__DIR__.'/_fixtures/mvc'));
    }

    public function test_a_violation_fails_the_test(): void
    {
        $rule = Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
            ->should(new Implement('App\ContainerAwareInterface'))
            ->because('i said so');

        $this->expectException(ExpectationFailedException::class);

        self::assertArchRule($rule, ClassSet::fromDir(__DIR__.'/_fixtures/mvc'));
    }
}
