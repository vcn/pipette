<?php

namespace Vcn\Pipette;

use stdClass;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vcn\Pipette\Json\Exception;
use Vcn\Pipette\Json\Value;

class Json
{
    /**
     * Parse a JSON string to a JSON value.
     *
     * @param string $source  The JSON string.
     * @param int    $depth   Maximum recursion depth.
     * @param int    $options Pass <b>JSON_BIGINT_AS_STRING</b> to convert big ints to strings instead of floats.
     *
     * @return Value
     * @throws Exception\CantDecode If $source is not valid JSON, or if it can't be decoded for any other reason. See
     *                              json_last_error() for more information.
     *
     * @see json_encode()
     * @see json_last_error()
     */
    public static function parse(string $source, int $depth = 512, int $options = 0): Value
    {
        if (preg_match("/^\s*null\s*$/", $source)) {
            return Json::pretend(null);
        }

        $result = json_decode($source, false, $depth, $options);

        if ($result === null) {
            $message = (string)json_last_error_msg();
            $code    = (int)json_last_error();

            throw new Exception\CantDecode($message, $code);
        }

        return Json::pretend($result);
    }

    /**
     * Pretend that some variable has the same representation as the data type returned by `json_decode()`.
     *
     * <br/>
     *
     * <strong>Use only if `Json::parse(json_encode($value)) = Json::pretend($value)`, or if you like to program
     * dangerously.</strong>
     *
     * <br/>
     *
     * Useful if you have to deal with a library/extension that returns untyped values.
     *
     * @param mixed $value
     *
     * @return Value
     */
    public static function pretend($value): Value
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return new Value($value, '$');
    }

    /**
     * Print the type of a JSON value.
     *
     * <br/>
     *
     * "string", "null", "true", "false", "number", "array", or "object".
     *
     * @param mixed $var
     *
     * @return string
     */
    public static function prettyPrintType($var): string
    {
        if (is_string($var)) {
            return 'string';

        } elseif ($var === null) {
            return 'null';

        } elseif ($var === true) {
            return 'true';

        } elseif ($var === false) {
            return 'false';

        } elseif (is_int($var) || is_float($var)) {
            return 'number';

        } elseif (is_array($var)) {
            return 'array';

        } elseif ($var instanceof stdClass) {
            return 'object';

        } else {
            return 'unknown type';
        }
    }

    public static function prettyPrintValue($var): string
    {
        switch (self::prettyPrintType($var)) {
            case 'string':
                return sprintf('"%s"', mb_strlen($var) > 30 ? (mb_substr($var, 0, 30) . " ...") : $var);

            case 'null':
                return 'null';

            case 'true':
                return 'true';

            case 'false':
                return 'false';

            case 'number':
                return $var;

            case 'array':
                return 'array';

            case 'object':
                return 'object';

            case 'unknown type':
            default:
                return 'unknown type';
        }
    }

    /**
     * todo
     *
     * @param ValidatorInterface $validator
     *
     * @return ValidatingJson
     */
    public static function validating(ValidatorInterface $validator): ValidatingJson
    {
        return new ValidatingJson($validator);
    }
}
