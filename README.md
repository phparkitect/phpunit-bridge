# PHPArkitect PHPUnit bridge

> [!WARNING]
> **This project is not ready for use.** It is an early draft of the extraction discussed in
> [arkitect#661](https://github.com/phparkitect/arkitect/issues/661): nothing has been released,
> the package is not on Packagist, and the public API — names, namespace, assertions — may still
> change without notice. Please do not depend on it yet.

[![Latest Stable Version](https://poser.pugx.org/phparkitect/phpunit-bridge/v)](https://packagist.org/packages/phparkitect/phpunit-bridge)
[![Test](https://github.com/phparkitect/phpunit-bridge/actions/workflows/build.yml/badge.svg)](https://github.com/phparkitect/phpunit-bridge/actions/workflows/build.yml)
[![License](https://poser.pugx.org/phparkitect/phpunit-bridge/license)](https://packagist.org/packages/phparkitect/phpunit-bridge)

Run [PHPArkitect](https://github.com/phparkitect/arkitect) architectural rules as PHPUnit assertions.

PHPArkitect normally checks your architecture from the CLI, through a `phparkitect.php` config file.
This package lets you do the same thing from inside your test suite instead: a broken architectural
rule becomes a failing test, with the violations rendered in the failure message.

```php
final class ArchitectureTest extends ArchRuleTestCase
{
    public function test_controllers_are_suffixed_properly(): void
    {
        $rule = Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
            ->should(new HaveNameMatching('*Controller'))
            ->because('it makes the codebase easier to navigate');

        self::assertArchRule($rule, ClassSet::fromDir(__DIR__.'/../src/Controller'));
    }
}
```

## Installation

```bash
composer require --dev phparkitect/phpunit-bridge
```

`phparkitect/phparkitect` and `phpunit/phpunit` are both real dependencies of this package, so
Composer pulls in whatever it needs.

## Usage

There are three ways to use the bridge, in increasing order of control.

### 1. Extend `ArchRuleTestCase`

The shortest path. `ArchRuleTestCase` extends PHPUnit's `TestCase` and adds the architectural
assertions:

```php
<?php

declare(strict_types=1);

namespace App\Tests;

use Arkitect\ClassSet;
use Arkitect\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\PHPUnit\ArchRuleTestCase;
use Arkitect\Rules\Rule;

final class ArchitectureTest extends ArchRuleTestCase
{
    public function test_the_domain_does_not_depend_on_the_framework(): void
    {
        $rule = Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
            ->should(new NotHaveDependencyOutsideNamespace('App\Domain'))
            ->because('the domain must stay framework agnostic');

        self::assertArchRule($rule, ClassSet::fromDir(__DIR__.'/../src'));
    }
}
```

### 2. Use the `ArchRuleAsserts` trait

If your tests already extend a base class of your own, pull the assertions in with the trait:

```php
final class ArchitectureTest extends MyProjectTestCase
{
    use ArchRuleAsserts;

    public function test_controllers_are_suffixed_properly(): void
    {
        self::assertArchRule($rule, ClassSet::fromDir(__DIR__.'/../src'));
    }
}
```

Both entry points expose the same two assertions:

| Assertion | Description |
| --- | --- |
| `assertArchRule(ArchRule $rule, ClassSet $classSet, string $message = '')` | Asserts that every class in the set satisfies the rule. |
| `assertArchRules(array $rules, ClassSet $classSet, string $message = '')` | Asserts a list of rules against the same set, failing on the first one that is violated. |

Checking several rules against one class set is the common case, and `assertArchRules` keeps it to a
single assertion:

```php
public function test_the_layers_are_respected(): void
{
    self::assertArchRules(
        [
            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
                ->should(new NotHaveDependencyOutsideNamespace('App\Domain'))
                ->because('the domain must stay framework agnostic'),
            Rule::allClasses()
                ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
                ->should(new HaveNameMatching('*Controller'))
                ->because('it makes the codebase easier to navigate'),
        ],
        ClassSet::fromDir(__DIR__.'/../src')
    );
}
```

### 3. Use the constraint directly

`ArchRuleCheckerConstraintAdapter` is a plain PHPUnit constraint, so it composes with
`assertThat()` and anything else that takes a `Constraint`:

```php
use Arkitect\PHPUnit\ArchRuleCheckerConstraintAdapter;

self::assertThat($rule, new ArchRuleCheckerConstraintAdapter($classSet));
```

The constraint is stateful — it holds the violations collected while matching so it can render
them in the failure message. Build a fresh one for every assertion.

## Failure output

Violations are printed grouped by class, using the same text formatting as the CLI:

```
App\Controller\ProductsController has 1 violations
  should implement App\ContainerAwareInterface because i said so

App\Controller\UserController has 1 violations
  should implement App\ContainerAwareInterface because i said so
```

If a file in the class set cannot be parsed, the parsing errors are reported instead of the
violations, since the analysis is incomplete:

```
 parsing error:
Syntax error, unexpected T_PUBLIC, expecting '{' on line 8 in file: BrokenService.php
```

## Choosing the target PHP version

By default the analyzer parses your code using the PHP version that is running the tests. Pass a
version explicitly when your test runner and your production runtime differ:

```php
new ArchRuleCheckerConstraintAdapter($classSet, '8.1');
```

Any version supported by PHPArkitect works; an unsupported one throws
`Arkitect\Exceptions\PhpVersionNotValidException`.

## Compatibility

| | Supported |
| --- | --- |
| PHP | 8.0 – 8.5 |
| PHPUnit | 9.6, 10, 11, 12 |
| PHPArkitect | ^1.0 |

Every combination in that matrix is exercised in CI.

## Relation to the PHPArkitect CLI

The bridge and the CLI are two front-ends over the same analysis engine, and they are not
mutually exclusive:

- The **CLI** (`vendor/bin/phparkitect check`) reads `phparkitect.php`, supports baselines,
  multiple output formats and `--stop-on-failure`. It is the right tool for a dedicated CI step.
- The **bridge** puts the rules next to your other tests, so architecture is checked by the same
  `phpunit` command as everything else. Baselines and the other CLI-only options are not available
  here.

Pick whichever fits your workflow; some projects run both.

## Migrating from `Arkitect\PHPUnit` in the core package

This package keeps the original `Arkitect\PHPUnit\ArchRuleCheckerConstraintAdapter` class name and
namespace, so migrating is just a matter of requiring the package — no `use` statement changes:

```bash
composer require --dev phparkitect/phpunit-bridge
```

The only addition to the constraint is the optional second constructor argument for the target PHP
version, which defaults to the previous behaviour.

> **During the transition:** PHPArkitect `^1.0` still ships its own copy of the class. While both
> are installed, `composer dump-autoload --optimize` reports an *"Ambiguous class resolution"*
> warning. It is harmless — the two classes are equivalent and Composer picks one — but you can
> silence it by ignoring the core copy in your own `composer.json`:
>
> ```json
> {
>     "autoload-dev": {
>         "exclude-from-classmap": ["/vendor/phparkitect/phparkitect/src/PHPUnit/"]
>     }
> }
> ```
>
> Once the core package drops the class, the warning goes away on its own and this snippet can be
> removed.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). The whole build is one command:

```bash
make build
```

## License

MIT. See [LICENSE](LICENSE).
