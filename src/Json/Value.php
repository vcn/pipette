<?php

namespace Vcn\Pipette\Json;

use DateTime;
use DateTimeImmutable;
use JsonSerializable;
use stdClass;
use Vcn\Lib\Enum;
use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Exception;
use Vcn\Pipette\Json\Validators\Validator;

class Value implements JsonSerializable
{
    /**
     * A JSON value anywhere in some JSON structure.
     *
     * @var mixed
     */
    private $value;

    /**
     * A JSONPath pointer to where in the JSON structure this JSON value resides.
     *
     * @var string
     */
    private $pointer;

    /**
     * @param mixed  $value
     * @param string $pointer
     *
     * @internal
     */
    public function __construct($value, string $pointer)
    {
        $this->value   = $value;
        $this->pointer = $pointer;
    }

    /**
     * Assert this value is an object, assert the given field is present, then return the value in that field.
     *
     * @param string $name
     *
     * @return Value
     * @throws Exception\AssertionFailed If this value is not an object, or if $name is not a present field.
     */
    public function field(string $name)
    {
        if (!$this->hasField($name)) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s.%s to be present, none given.",
                    $this->pointer,
                    $name
                )
            );
        }

        return new Value($this->value->$name, sprintf("%s.%s", $this->pointer, $name));
    }

    /**
     * Assert this value is an object, if the given field is not present or null, have any chain of operations result in
     * null, otherwise return that field.
     *
     * @param string $name
     *
     * @return OptionalValue
     * @throws Exception\AssertionFailed If this value is not an object nor null.
     */
    public function ¿field(string $name): OptionalValue
    {
        return !$this->hasField($name)
            ? new OptionalValue(null, sprintf("%s.%s", $this->pointer, $name))
            : new OptionalValue($this->field($name), sprintf("%s.%s", $this->pointer, $name));
    }

    /**
     * Assert this value is an array, assert a value exists in the array at the given index, then return the value at that index.
     *
     * @param int $n
     *
     * @return Value
     * @throws Exception\AssertionFailed If this value is not an array, or if $n is not a valid index in this array.
     */
    public function nth(int $n): Value
    {
        if (!$this->hasNth($n)) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s[%d] to be present, none given.",
                    $this->pointer,
                    $n
                )
            );
        }

        return new Value($this->value[$n], sprintf("%s[%d]", $this->pointer, $n));
    }

    /**
     * Assert this value is an array, if no value exists in the array at the given index or the value is null, have any
     * chain of operations result in null, otherwise return the value at given index.
     *
     * @param int $n
     *
     * @return OptionalValue
     * @throws Exception\AssertionFailed If this value is not an array nor null.
     */
    public function ¿nth(int $n): OptionalValue
    {
        return !$this->hasNth($n)
            ? new OptionalValue(null, sprintf("%s[%d]", $this->pointer, $n))
            : new OptionalValue($this->nth($n), sprintf("%s[%d]", $this->pointer, $n));
    }

    /**
     * Is this value an object?
     *
     * @return bool
     */
    public function isObject(): bool
    {
        return $this->value instanceof stdClass;
    }

    /**
     * Assert this value is an object, then return whether the given field exists.
     *
     * @param string $name
     *
     * @return bool
     * @throws Exception\AssertionFailed If this value is not an object.
     */
    public function hasField(string $name): bool
    {
        if (!$this->isObject()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be an object, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return property_exists($this->value, $name);
    }

    /**
     * Assert this value is an array, then return whether the value at the given index exists.
     *
     * @param int $n
     *
     * @return bool
     * @throws Exception\AssertionFailed If this value is not an array.
     */
    public function hasNth(int $n): bool
    {
        if (!$this->isArray()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be an array, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return array_key_exists($n, $this->value);
    }

    /**
     * Assert this value is an array, then map $f over the values, returning the results.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Value -> a
     *
     * @return array [a]
     * @throws Exception\AssertionFailed If this value is not an array. Also rethrows any exception thrown by $f.
     */
    public function arrayMap(callable $f): array
    {
        $g = function ($k, $v) use ($f) {
            return $f($v);
        };

        return $this->arrayMapWithIndex($g);
    }

    /**
     * Assert this value is an array or null, then map $f over the values, returning the results, or return null.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Value -> a
     *
     * @return null|array [a] or null
     * @throws Exception\AssertionFailed If this value is not an array nor null. Also rethrows any exception thrown by
     *                                   $f.
     */
    public function ¿arrayMap(callable $f): ?array
    {
        $g = function ($k, $v) use ($f) {
            return $f($v);
        };

        return $this->¿arrayMapWithIndex($g);
    }

    /**
     * Assert this value is an array, then map $f over the values paired with their index, returning the results.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Int -> Value -> a
     *
     * @return array [a]
     * @throws Exception\AssertionFailed If this value is not an array. Also rethrows any exception thrown by $f.
     */
    public function arrayMapWithIndex(callable $f): array
    {
        if (!$this->isArray()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be an array, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        $buffer = [];

        foreach ($this->value as $key => $value) {
            $buffer[] = $f($key, new self($value, sprintf("%s[%d]", $this->pointer, $key)));
        }

        return $buffer;
    }

    /**
     * Assert this value is an array or null, then map $f over the values paired with their index, returning the
     * results, or return null.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Int -> Value -> a
     *
     * @return null|array [a] or null
     * @throws Exception\AssertionFailed If this value is not an array nor null. Also rethrows any exception thrown by
     *                                   $f.
     */
    public function ¿arrayMapWithIndex(callable $f): ?array
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->arrayMapWithIndex($f);
    }

    /**
     * Is this value an array?
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return is_array($this->value);
    }

    /**
     * Assert this value is an object, then map $f over the values, returning the results as an associative array,
     * preserving the original keys.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Value -> a
     *
     * @return array [a]
     * @throws Exception\AssertionFailed If this value is not an object. Also rethrows any exception thrown by $f.
     */
    public function objectMap(callable $f): array
    {
        $g = function ($k, $v) use ($f) {
            return $f($v);
        };

        return $this->objectMapWithIndex($g);
    }

    /**
     * Assert this value is an object or null, then map $f over the values, returning the results as an associative
     * array, preserving the original keys, or return null.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Value -> a
     *
     * @return array [a] or null
     * @throws Exception\AssertionFailed If this value is not an object nor null. Also rethrows any exception thrown by
     *                                   $f.
     */
    public function ¿objectMap(callable $f): ?array
    {
        $g = function ($k, $v) use ($f) {
            return $f($v);
        };

        return $this->¿objectMapWithIndex($g);
    }

    /**
     * Assert this value is an object, then map $f over the values paired with their field names, returning the results
     * as an associative array, preserving the original keys.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f String -> Value -> a
     *
     * @return array [a]
     * @throws Exception\AssertionFailed If this value is not an object. Also rethrows any exception thrown by $f.
     */
    public function objectMapWithIndex(callable $f): array
    {
        if (!$this->isObject()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be an object, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        $buffer = [];

        foreach ($this->value as $key => $value) {
            $buffer[$key] = $f($key, new self($value, sprintf("%s.%s", $this->pointer, $key)));
        }

        return $buffer;
    }

    /**
     * Assert this value is an object or null, then map $f over the values paired with their field names, returning
     * the results as an associative array, preserving the original keys, or return null.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f String -> Value -> a
     *
     * @return null|array [a] or null
     * @throws Exception\AssertionFailed If this value is not an object nor null. Also rethrows any exception thrown by
     *                                   $f.
     */
    public function ¿objectMapWithIndex(callable $f): ?array
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->objectMapWithIndex($f);
    }

    /**
     * Assert this value is a number, then return that number as an int (casting floating point numbers).
     *
     * @return int
     * @throws Exception\AssertionFailed If this value is not a number.
     */
    public function int(): int
    {
        if (!$this->isNumber()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be a number, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return (int)$this->value;
    }

    /**
     * Assert this value is a number or null, then return that number as an int (casting floating point numbers), or
     * return null.
     *
     * @return null|int
     * @throws Exception\AssertionFailed If this value is not a number nor null.
     */
    public function ¿int(): ?int
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->int();
    }

    /**
     * Assert this value is a number, then return that number as a float.
     *
     * @return float
     * @throws Exception\AssertionFailed If this value is not a number.
     */
    public function float(): float
    {
        if (!$this->isNumber()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be a number, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return (float)$this->value;
    }

    /**
     * Assert this value is a number or null, then return that number as a float, or return null.
     *
     * @return null|float
     * @throws Exception\AssertionFailed If this value is not a number nor null.
     */
    public function ¿float(): ?float
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->float();
    }

    /**
     * Is this value a number?
     *
     * @return bool
     */
    public function isNumber(): bool
    {
        return is_int($this->value) || is_float($this->value);
    }

    /**
     * Assert this value is a string, then return that string.
     *
     * @return string
     * @throws Exception\AssertionFailed If this value is not a string.
     */
    public function string(): string
    {
        if (!$this->isString()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be a string, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return $this->value;
    }

    /**
     * Assert this value is a string or null, then return that string, or return null.
     *
     * @return string
     * @throws Exception\AssertionFailed If this value is not a string nor null.
     */
    public function ¿string(): ?string
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->string();
    }

    /**
     * Is this value a string?
     *
     * @return bool
     */
    public function isString(): bool
    {
        return is_string($this->value);
    }

    /**
     * Same as `dateTime()` but with a different default argument that only expects the date part of ISO 8601.
     *
     * @param string $format
     *
     * @return DateTimeImmutable
     * @throws Exception\AssertionFailed If this value is not a string, or does not conform to the given format.
     */
    public function date(string $format = 'Y-m-d|'): DateTimeImmutable
    {
        return $this->dateTime($format);
    }

    /**
     * Same as `¿dateTime()` but with a different default argument that only expects the date part of ISO 8601.
     *
     * @param string $format
     *
     * @return null|DateTimeImmutable
     * @throws Exception\AssertionFailed If this value is not a string, nor null, or does not conform to the given
     *                                   format.
     */
    public function ¿date(string $format = 'Y-m-d|'): ?DateTimeImmutable
    {
        return $this->¿dateTime($format);
    }

    /**
     * Assert this value is a string, assert it conforms to a given date time format, then return that date time.
     *
     * @param string $format
     *
     * @return DateTimeImmutable
     * @throws Exception\AssertionFailed If this value is not a string, or does not conform to the given format.
     */
    public function dateTime(string $format = DateTime::ATOM): DateTimeImmutable
    {
        $string = $this->string();
        $result = DateTimeImmutable::createFromFormat($format, $string);

        if ($result === false) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be a date time string according to format %s, '%s' given.",
                    $this->pointer,
                    $format,
                    $string
                )
            );
        }

        return $result;
    }

    /**
     * Assert this value is a string or null, assert it conforms to a given date time format, then return that
     * date time, or return null.
     *
     * @param string $format
     *
     * @return null|DateTimeImmutable
     * @throws Exception\AssertionFailed If this value is not a string, nor null, or does not conform to the given
     *                                   format.
     */
    public function ¿dateTime(string $format = DateTime::ATOM): ?DateTimeImmutable
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->dateTime($format);
    }

    /**
     * Assert this value is a string, assert it is any of the names of the given Enum, then return that Enum instance.
     *
     * @param string $className
     *
     * @return mixed|Enum
     * @throws Exception\AssertionFailed If this value is not a string, or it is not any of the Enum names from
     *                                   $className.
     * @throws Exception\Runtime         If $className does not exist, or does not extend Enum.
     */
    public function enum(string $className): Enum
    {
        if (!class_exists(Enum::class)) {
            // @codeCoverageIgnoreStart
            throw new Exception\Runtime(
                sprintf(
                    "Class %s does not exist. Did you include the library?",
                    Enum::class
                )
            );
            // @codeCoverageIgnoreEnd
        }

        if (!class_exists($className)) {
            throw new Exception\Runtime(sprintf("Class %s does not exist.", $className));
        }

        if (!is_subclass_of($className, Enum::class)) {
            throw new Exception\Runtime(sprintf("Class %s does not extend %s.", $className, Enum::class));
        }

        $string = $this->string();

        /** @var Enum $className */
        if ($className::getAllInstances() === []) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected field %s to by any of no enumeration constants, '%s' given.",
                    $this->pointer,
                    $string
                )
            );
        }

        try {
            return $className::byName($string);
        } catch (Enum\Exception\InvalidInstance $e) {
            $failedAssertions = array_map(
                function (Enum $enum) use ($string) {
                    return new Exception\AssertionFailed(
                        sprintf(
                            "Expected %s to be enumeration constant '%s', '%s' given.",
                            $this->getPointer(),
                            $enum->getName(),
                            $string
                        )
                    );
                },
                $e->getValidInstances()
            );

            throw Json\Exception\ManyAssertionsFailed::fromFailedAssertions(
                reset($failedAssertions),
                ...array_slice($failedAssertions, 1)
            );
        }
    }

    /**
     * Assert this value is a string or null, assert it is any of the names of the given Enum, then return that Enum
     * instance, or return null.
     *
     * @param string $className
     *
     * @return null|mixed|Enum
     * @throws Exception\AssertionFailed If this value is not a string, nor null, or it is not any of the Enum names
     *                                   from $className.
     * @throws Exception\Runtime         If $className does not exist, or does not extend Enum.
     */
    public function ¿enum(string $className): ?Enum
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->enum($className);
    }

    /**
     * Assert this value is a string, assert that string is a base64 encoding, then return the decoded binary string.
     *
     * @return string
     * @throws Exception\AssertionFailed If this value is not a string, or it contains characters outside the base64
     *                                   alphabet.
     */
    public function base64(): string
    {
        $string64 = $this->string();
        $string   = base64_decode($string64, true);

        if ($string === false) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to contain a base64 encoded string, " .
                    "it contained characters outside the base64 alphabet.",
                    $this->pointer
                )
            );
        }

        return $string;
    }

    /**
     * Assert this value is a string or null, assert that string is a base64 encoding, then return the decoded binary
     * string, or return null.
     *
     * @return string
     * @throws Exception\AssertionFailed If this value is not a string, nor null, or it contains characters outside the
     *                                   base64 alphabet.
     */
    public function ¿base64(): ?string
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->base64();
    }

    /**
     * Assert this value is a bool, then return that bool.
     *
     * @return bool
     * @throws Exception\AssertionFailed If this value is not true nor false.
     */
    public function bool(): bool
    {
        if (!$this->isBool()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be true or false, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return $this->value;
    }

    /**
     * Assert this value is a bool or null, then return that bool, or return null.
     *
     * @return null|bool
     * @throws Exception\AssertionFailed If this value is not true nor false nor null.
     */
    public function ¿bool(): ?bool
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->bool();
    }

    /**
     * Is this value true or false?
     *
     * @return bool
     */
    public function isBool(): bool
    {
        return is_bool($this->value);
    }

    /**
     * Assert this value is true, then return true.
     *
     * @return bool
     * @throws Exception\AssertionFailed If this value is not true.
     */
    public function true(): bool
    {
        if (!$this->isTrue()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be true, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return $this->value;
    }

    /**
     * Assert this value is true or null, then return true, or return null.
     *
     * @return null|bool
     * @throws Exception\AssertionFailed If this value is not true nor null.
     */
    public function ¿true(): ?bool
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->true();
    }

    /**
     * Is this value true?
     *
     * @return bool
     */
    public function isTrue(): bool
    {
        return $this->value === true;
    }

    /**
     * Assert this value is false, then return false.
     *
     * @return bool
     * @throws Exception\AssertionFailed If this value is not false.
     */
    public function false(): bool
    {
        if (!$this->isFalse()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be false, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return $this->value;
    }

    /**
     * Assert this value is false or null, then return false, or return null.
     *
     * @return null|bool
     * @throws Exception\AssertionFailed If this value is not false nor null.
     */
    public function ¿false(): ?bool
    {
        if ($this->isNull()) {
            return $this->value;
        }

        return $this->false();
    }

    /**
     * Is this value false?
     *
     * @return bool
     */
    public function isFalse(): bool
    {
        return $this->value === false;
    }

    /**
     * Assert this value is null, then return null.
     *
     * @return null
     * @throws Exception\AssertionFailed If this value is not null.
     */
    public function null()
    {
        if (!$this->isNull()) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "Expected %s to be null, %s given.",
                    $this->pointer,
                    Json::prettyPrintType($this->value)
                )
            );
        }

        return $this->value;
    }

    /**
     * Is this value null?
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->value === null;
    }

    /**
     * Apply a function to this value.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Value -> a
     *
     * @return mixed a
     * @throws Exception\AssertionFailed Or any other exception thrown by $f.
     */
    public function apply(callable $f)
    {
        try {
            return $f($this);
            // @codeCoverageIgnoreStart
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (Exception\AssertionFailed $e) {
            throw $e;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Apply a function to this value if it is not null, otherwise return null.
     *
     * <br/>
     *
     * Rethrows any exception thrown by $f.
     *
     * @param callable $f Value -> a
     *
     * @return null|mixed a or null
     * @throws Exception\AssertionFailed Or any other exception thrown by $f.
     */
    public function ¿apply(callable $f)
    {
        try {
            return $this->isNull() ? null : $f($this);
            // @codeCoverageIgnoreStart
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (Exception\AssertionFailed $e) {
            throw $e;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Apply any number of functions to this value, returning the first succesful result. Combines all AssertionFailed
     * exceptions into one ManyAssertionsFailed exception if no function returns any successful result.
     *
     * <br/>
     *
     * Immediately rethrows any other exception.
     *
     * @param callable   $f  Value -> a
     * @param callable   $g  Value -> a
     * @param callable[] $hs [Value -> a]
     *
     * @return mixed a
     * @throws Exception\ManyAssertionsFailed Or any other exception thrown by $f, $g or $hs.
     */
    public function either(callable $f, callable $g, callable ...$hs)
    {
        $failedAssertions = [];

        foreach (array_merge([$f], [$g], $hs) as $v) {
            try {
                return $v($this);
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (Exception\AssertionFailed $e) {
                $failedAssertions[] = $e;
            }
        }

        throw Exception\ManyAssertionsFailed::fromFailedAssertions(
            reset($failedAssertions),
            ...array_slice($failedAssertions, 1)
        );
    }

    /**
     * Apply any number of functions to this value if it is not null, returning the first succesful result, or returns
     * null. Rethrows the last AssertionFailed exception if no function returns any successful result.
     *
     * <br/>
     *
     * Immediately rethrows any other exception.
     *
     * @param callable   $f  Value -> a
     * @param callable   $g  Value -> a
     * @param callable[] $hs [Value -> a]
     *
     * @return null|mixed a or null
     * @throws Exception\AssertionFailed Or any other exception thrown by $f, $g or $hs.
     */
    public function ¿either(callable $f, callable $g, callable ...$hs)
    {
        return $this->isNull() ? null : $this->either($f, $g, ...$hs);
    }

    /**
     * Encode this value as a JSON string.
     *
     * @param int $options Bitmask as described per json_encode().
     * @param int $depth   Maximum recursion depth.
     *
     * @return string
     * @throws Exception\CantEncode If this object does not represent valid JSON (as a result of constructing it with
     *                              Json::pretend()), or if it can't be decoded for any other reason. See
     *                              json_last_error() for more information.
     *
     * @see json_encode()
     * @see json_last_error()
     */
    public function encode(int $options = 0, $depth = 512): string
    {
        $string = json_encode($this->value, $options, $depth);

        if ($string === false) {
            $message = (string)json_last_error_msg();
            $code    = (int)json_last_error();

            throw new Exception\CantEncode($message, $code);
        }

        return $string;
    }

    /**
     * Encode this value as a JSON string if it is not null otherwise return null.
     *
     * @param int $options Bitmask as described per json_encode().
     * @param int $depth   Maximum recursion depth.
     *
     * @return null|string
     * @throws Exception\CantEncode If this object does not represent valid JSON (as a result of constructing it with
     *                              Json::pretend()), or if it can't be decoded for any other reason. See
     *                              json_last_error() for more information.
     *
     * @see json_encode()
     * @see json_last_error()
     */
    public function ¿encode(int $options = 0, $depth = 512): ?string
    {
        return $this->isNull() ? null : $this->encode($options, $depth);
    }

    /**
     * Same as `encode(JSON_PRETTY_PRINT)`.
     *
     * @param int $depth Maximum recursion depth.
     *
     * @return string
     * @throws Exception\CantEncode
     */
    public function prettyPrint(int $depth = 512): string
    {
        return $this->encode(JSON_PRETTY_PRINT, $depth);
    }

    /**
     * Same as `¿encode(JSON_PRETTY_PRINT)`.
     *
     * @param int $depth Maximum recursion depth.
     *
     * @return null|string
     * @throws Exception\CantEncode
     */
    public function ¿prettyPrint(int $depth = 512): ?string
    {
        return $this->isNull() ? null : $this->prettyPrint($depth);
    }

    /**
     * Retrieve the underlying value, whatever it may be.
     *
     * @return mixed
     */
    public function mixed()
    {
        return $this->value;
    }

    /**
     * Run the given validator on this value, then return this value.
     *
     * @param Validator $validator
     *
     * @return Value
     * @throws Exception\AssertionFailed If this value is not valid according to the given validator.
     */
    public function validate(Validator $validator): Value
    {
        $validator->validate($this);

        return $this;
    }

    /**
     * A JSONPath pointer to where in the structure this value resides.
     *
     * @return string
     */
    public function getPointer(): string
    {
        return $this->pointer;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
