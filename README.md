# PHPArkitect PHPUnit bridge

> [!WARNING]
> **This project is not ready for use.** It is an early draft of the extraction discussed in
> [arkitect#661](https://github.com/phparkitect/arkitect/issues/661): nothing has been released,
> the package is not on Packagist, and the public API may still change without notice.

[![Latest Stable Version](https://poser.pugx.org/phparkitect/phpunit-bridge/v)](https://packagist.org/packages/phparkitect/phpunit-bridge)
[![Test](https://github.com/phparkitect/phpunit-bridge/actions/workflows/build.yml/badge.svg)](https://github.com/phparkitect/phpunit-bridge/actions/workflows/build.yml)
[![License](https://poser.pugx.org/phparkitect/phpunit-bridge/license)](https://packagist.org/packages/phparkitect/phpunit-bridge)

Check [PHPArkitect](https://github.com/phparkitect/arkitect) architectural rules from your test
suite instead of from the CLI: a broken rule becomes a failing test, with the violations in the
failure message.

```bash
composer require --dev phparkitect/phpunit-bridge
```

```php
<?php

declare(strict_types=1);

namespace App\Tests;

use Arkitect\ClassSet;
use Arkitect\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\PHPUnit\ArchRuleAsserts;
use Arkitect\Rules\Rule;
use PHPUnit\Framework\TestCase;

final class ArchitectureTest extends TestCase
{
    use ArchRuleAsserts;

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

That is the whole API: `assertArchRule(ArchRule $rule, ClassSet $classSet)`. There is no message
argument, because a rule cannot be built without `because()` — the reason is already in the failure
output. One rule per test method reads best: PHPUnit names the broken rule and still reports the
ones that pass. `ArchRuleAsserts` is a trait rather than a base test case so it also works when the
parent class is already taken, by `KernelTestCase` or your own.

## Writing rules

Rules, expressions and `ClassSet` all come from the core package, and its documentation is the
reference:

- [Available rules](https://github.com/phparkitect/arkitect/blob/main/docs/rules.md)
- [Writing custom rules](https://github.com/phparkitect/arkitect/blob/main/docs/custom-rules.md)
- [Core concepts and CLI](https://github.com/phparkitect/arkitect#readme)

Anything you can express in a `phparkitect.php` config file works here unchanged. Baselines and the
other CLI-only options do not — for those, keep using `vendor/bin/phparkitect check`.

## Performance

Every assertion parses the class set from scratch, so the cost grows linearly with the number of
rules: one pass over ~600 files takes about 2 seconds, so ten rules cost about 20. The CLI does not
work this way — it parses each file once and then checks every rule against it, which makes extra
rules essentially free.

Keep your class sets narrow (`ClassSet::fromDir(__DIR__.'/../src/Domain')` rather than the whole
`src/`), and reach for the CLI when you have many rules over a large codebase. Removing this
difference means teaching the core analyzer to reuse parsed files across runs, which belongs in
`phparkitect/phparkitect` rather than here.

## Compatibility

PHP 8.0–8.5, PHPUnit 9.6/10/11/12, PHPArkitect `^1.0`. Every combination is exercised in CI.

## Migrating from `Arkitect\PHPUnit` in the core package

The class name and namespace are unchanged, so migrating means requiring this package — no `use`
statement changes. `ArchRuleCheckerConstraintAdapter` gains one optional constructor argument, the
target PHP version to parse your code with, defaulting to the previous behaviour.

<details>
<summary>During the transition you may see an "Ambiguous class resolution" warning</summary>

PHPArkitect `^1.0` still ships its own copy of the class, so while both are installed
`composer dump-autoload --optimize` warns about it. It is harmless — the classes are equivalent and
Composer picks one — and you can silence it by ignoring the core copy in your `composer.json`:

```json
{
    "autoload-dev": {
        "exclude-from-classmap": ["/vendor/phparkitect/phparkitect/src/PHPUnit/"]
    }
}
```

Once the core package drops the class the warning goes away on its own.
</details>

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md); `make build` runs everything.

## License

MIT. See [LICENSE](LICENSE).
