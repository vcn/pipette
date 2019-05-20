<?php

namespace Vcn\Pipette\Json;

use DateTime;
use DateTimeImmutable;
use Vcn\Lib\Enum;

/**
 * A JSON value that is either not present or null.
 */
class OptionalValue
{
    /**
     * Absent, null or a non-null value
     *
     * @var null|Value
     */
    private $value;

    /**
     * @var string
     */
    private $pointer;

    /**
     * @param null|Value $value
     * @param string     $pointer
     *
     * @internal
     */
    public function __construct(?Value $value, string $pointer)
    {
        $this->value   = $value === null || $value->isNull() ? null : $value;
        $this->pointer = $pointer;
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
        if ($this->isNull()) {
            $newPointer = sprintf("%s.%s", $this->pointer, $name);

            return new self(null, $newPointer);
        }

        return $this->value->¿field($name);
    }

    /**
     * Assert this value is an array, if no value exists in the array at the given index or the value is null, have any
     * chain of operations result in null, otherwise return the value at given index.
     *
     * @param int $n
     *
     * @return OptionalValue
     * @throws Exception\AssertionFailed If this value is not an object nor null.
     */
    public function ¿nth(int $n): OptionalValue
    {
        if ($this->isNull()) {
            $newPointer = sprintf("%s[%d]", $this->pointer, $n);

            return new self(null, $newPointer);
        }

        return $this->value->¿nth($n);
    }

    /**
     * Is this value an object?
     *
     * @return bool
     */
    public function isObject(): bool
    {
        return !$this->isNull() && $this->value->isObject();
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
            return null;
        }

        return $this->value->¿arrayMapWithIndex($f);
    }

    /**
     * Is this value an array?
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return !$this->isNull() && $this->value->isArray();
    }

    /**
     * Assert this value is an object or null, then map $f over the values, returning the results as an array, or return
     * null.
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
     * as an array.
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
            return null;
        }

        return $this->value->¿objectMapWithIndex($f);
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
            return null;
        }

        return $this->value->int();
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
            return null;
        }

        return $this->value->float();
    }

    /**
     * Is this value a number?
     *
     * @return bool
     */
    public function isNumber(): bool
    {
        return !$this->isNull() && $this->value->isNumber();
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
            return null;
        }

        return $this->value->string();
    }

    /**
     * Is this value a string?
     *
     * @return bool
     */
    public function isString(): bool
    {
        return !$this->isNull() && $this->value->isString();
    }

    /**
     * Same as `¿dateTime()` but with a different default argument that only expects the date part of ISO 8601.
     *
     * @param string $format
     *
     * @return DateTimeImmutable
     * @throws Exception\AssertionFailed If this value is not a string, nor null, or does not conform to the given
     *                                   format.
     */
    public function ¿date(string $format = 'Y-m-d|'): ?DateTimeImmutable
    {
        return $this->¿dateTime($format);
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
            return null;
        }

        return $this->value->dateTime($format);
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
            return null;
        }

        return $this->value->enum($className);
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
            return null;
        }

        return $this->value->base64();
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
            return null;
        }

        return $this->value->bool();
    }

    /**
     * Is this value true or false?
     *
     * @return bool
     */
    public function isBool(): bool
    {
        return !$this->isNull() && $this->value->isBool();
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
            return null;
        }

        return $this->value->true();
    }

    /**
     * Is this value true?
     *
     * @return bool
     */
    public function isTrue(): bool
    {
        return !$this->isNull() && $this->value->isTrue();
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
            return null;
        }

        return $this->value->false();
    }

    /**
     * Is this value false?
     *
     * @return bool
     */
    public function isFalse(): bool
    {
        return !$this->isNull() && $this->value->isFalse();
    }

    /**
     * Assert this value is null, then return null.
     *
     * @return null
     * @throws Exception\AssertionFailed If this value is not null.
     */
    public function null()
    {
        if ($this->isNull()) {
            return null;
        }

        return $this->value->null();
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
            return $this->isNull() ? null : $f($this->value);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (Exception\AssertionFailed $e) {
            throw $e;
        }
    }

    /**
     * Apply any number of functions to this value if it is not null, returning the first succesful result, or returns
     * null. Combines all AssertionFailed exceptions into one ManyAssertionsFailed exception if no function returns any
     * successful result.
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
        return $this->isNull() ? null : $this->value->either($f, $g, ...$hs);
    }

    /**
     * Encode this value as a JSON string. An absent value is encoded as "null".
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
        return $this->isNull() ? 'null' : $this->value->encode($options, $depth);
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
        return $this->isNull() ? 'null' : $this->value->prettyPrint($depth);
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
     * A JSONPath pointer to where in the structure this value resides.
     *
     * @return string
     */
    public function getPointer(): string
    {
        return $this->pointer;
    }
}
