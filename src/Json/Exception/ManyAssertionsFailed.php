<?php

namespace Vcn\Pipette\Json\Exception;

use Throwable;

class ManyAssertionsFailed extends AssertionFailed
{
    private const TAB_CHARACTER = '    ';

    /**
     * @var AssertionFailed
     */
    private $firstFailedAssertion;

    /**
     * @var AssertionFailed[]
     */
    private $otherFailedAssertions;

    /**
     * @param string          $message
     * @param int             $code
     * @param null|Throwable  $previous
     * @param AssertionFailed $firstFailedAssertion
     * @param AssertionFailed ...$otherFailedAssertions
     */
    public function __construct(
        string $message,
        int $code,
        ?Throwable $previous,
        AssertionFailed $firstFailedAssertion,
        AssertionFailed ...$otherFailedAssertions
    ) {
        parent::__construct($message, $code, $previous);

        $this->firstFailedAssertion  = $firstFailedAssertion;
        $this->otherFailedAssertions = $otherFailedAssertions;
    }

    /**
     * @param AssertionFailed $firstFailedAssertion
     * @param AssertionFailed ...$otherFailedAssertions
     *
     * @return ManyAssertionsFailed
     */
    public static function fromFailedAssertions(
        AssertionFailed $firstFailedAssertion,
        AssertionFailed ...$otherFailedAssertions
    ): ManyAssertionsFailed {
        $failedAssertions = array_merge([$firstFailedAssertion], $otherFailedAssertions);
        $previous         = end($failedAssertions);
        $message          = self::formatMessage(0, $firstFailedAssertion, ...$otherFailedAssertions);

        return new self($message, 0, $previous, $firstFailedAssertion, ...$otherFailedAssertions);
    }

    /**
     * @param int             $nestingLevel
     * @param AssertionFailed $firstFailedAssertion
     * @param AssertionFailed ...$otherFailedAssertions
     *
     * @return string
     */
    private static function formatMessage(
        int $nestingLevel,
        AssertionFailed $firstFailedAssertion,
        AssertionFailed ...$otherFailedAssertions
    ): string {
        $failedAssertions = array_merge([$firstFailedAssertion], $otherFailedAssertions);

        usort(
            $failedAssertions,
            function (AssertionFailed $a, AssertionFailed $b) {
                if ($a instanceof ManyAssertionsFailed && !$b instanceof ManyAssertionsFailed) {
                    return 1;
                } elseif (!$a instanceof ManyAssertionsFailed && $b instanceof ManyAssertionsFailed) {
                    return -1;
                } else {
                    return 0;
                }
            }
        );

        return sprintf(
            "%s%sExpected any of the following:\n%s",
            str_repeat(self::TAB_CHARACTER, $nestingLevel),
            $nestingLevel > 0 ? '- ' : '',
            implode(
                "\n",
                array_map(
                    function (AssertionFailed $e) use ($nestingLevel) {
                        if ($e instanceof ManyAssertionsFailed) {
                            [$firstFailedAssertion, $otherFailedAssertions] = $e->unconsFailedAssertions();

                            return self::formatMessage(
                                $nestingLevel + 1,
                                $firstFailedAssertion,
                                ...$otherFailedAssertions
                            );
                        } else {
                            return sprintf("%s- %s", str_repeat(self::TAB_CHARACTER, $nestingLevel + 1), $e->getMessage());
                        }
                    },
                    $failedAssertions

                )
            )
        );
    }

    /**
     * @return AssertionFailed[]
     */
    public function getFailedAssertions(): array
    {
        return array_merge([$this->firstFailedAssertion], $this->otherFailedAssertions);
    }

    /**
     * @return array{AssertionFailed, AssertionFailed[]} [$firstFailedAssertion, $otherFailedAssertions]
     */
    public function unconsFailedAssertions(): array
    {
        return [$this->firstFailedAssertion, $this->otherFailedAssertions];
    }
}
