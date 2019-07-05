<?php

namespace Vcn\Pipette;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vcn\Pipette\Json\Exception;
use Vcn\Pipette\Json\Value;

class ValidatingJson
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param ValidatorInterface $validator
     *
     * @internal
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

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
    public function parse(string $source, int $depth = 512, int $options = 0): Value
    {
        $value = Json::parse($source, $depth, $options)->jsonSerialize();

        return $this->pretend($value);
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
    public function pretend($value): Value
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return new Value($value, '$', $this->validator);
    }
}
