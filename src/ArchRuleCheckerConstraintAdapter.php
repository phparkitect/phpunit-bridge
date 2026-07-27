<?php

declare(strict_types=1);

namespace Arkitect\PHPUnit;

use Arkitect\Analyzer\FileParser;
use Arkitect\Analyzer\FileParserFactory;
use Arkitect\Analyzer\ParsingErrors;
use Arkitect\ClassSet;
use Arkitect\ClassSetRules;
use Arkitect\CLI\Printer\Printer;
use Arkitect\CLI\Printer\PrinterFactory;
use Arkitect\CLI\Progress\VoidProgress;
use Arkitect\CLI\Runner;
use Arkitect\CLI\TargetPhpVersion;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * Runs an ArchRule against a ClassSet and reports the outcome as a PHPUnit constraint.
 *
 * The constraint is stateful: it keeps the violations and the parsing errors collected
 * during matches() so that failureDescription() can render them. Use a fresh instance
 * for every assertion.
 */
class ArchRuleCheckerConstraintAdapter extends Constraint
{
    private ClassSet $classSet;

    private Violations $violations;

    private Runner $runner;

    private FileParser $fileparser;

    private ParsingErrors $parsingErrors;

    private Printer $printer;

    /**
     * @param string|null $targetPhpVersion the PHP version the analyzed code targets,
     *                                      or null to use the PHP version running the tests
     */
    public function __construct(ClassSet $classSet, ?string $targetPhpVersion = null)
    {
        $this->runner = new Runner();
        $this->fileparser = FileParserFactory::createFileParser(TargetPhpVersion::create($targetPhpVersion));
        $this->classSet = $classSet;
        $this->violations = new Violations();
        $this->parsingErrors = new ParsingErrors();
        $this->printer = PrinterFactory::create(Printer::FORMAT_TEXT);
    }

    public function toString(): string
    {
        return 'satisfies all architectural constraints';
    }

    /**
     * @param mixed $other the ArchRule to check against the class set
     *
     * The parameter is left untyped on purpose: PHPUnit 9 declares it untyped while
     * PHPUnit 10+ declares it as `mixed`, and an untyped parameter is compatible with both
     */
    protected function matches($other): bool
    {
        $this->runner->check(
            ClassSetRules::create($this->classSet, $other),
            new VoidProgress(),
            $this->fileparser,
            $this->violations,
            $this->parsingErrors,
            false
        );

        return 0 === $this->violations->count() && 0 === $this->parsingErrors->count();
    }

    /**
     * @param mixed $other the ArchRule that was checked
     */
    protected function failureDescription($other): string
    {
        if ($this->parsingErrors->count() > 0) {
            $result = "\n parsing error: ";
            foreach ($this->parsingErrors as $parsingError) {
                $result .= "\n$parsingError";
            }

            return $result;
        }

        return "\n".$this->printer->print($this->violations->groupedByFqcn());
    }
}
