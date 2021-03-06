<?php

namespace Vcn\Pipette\Json\Validators;

use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Exception;

/**
 * Automatically derive parse() from validate().
 */
trait ValidatorTrait
{
    /**
     * Validate a JSON value, throwing an AssertionFailed exception if the JSON is not valid.
     *
     * @param Json\Value $json
     *
     * @throws Exception\AssertionFailed If the value is not valid according to this validator.
     */
    public abstract function validate(Json\Value $json): void;

    /**
     * Parse a JSON value, then validate it, throwing an AssertionFailed exception if the JSON is not valid.
     *
     * @param string $source  The JSON string.
     * @param int    $depth   Maximum recursion depth.
     * @param int    $options Pass <b>JSON_BIGINT_AS_STRING</b> to convert big ints to strings instead of floats.
     *
     * @return Json\Value
     * @throws Exception\AssertionFailed If $source is syntactically valid JSON, but not valid according to this
     *                                   validator.
     * @throws Exception\CantDecode      If $source is not syntactically valid JSON, or if it can't be decoded for any
     *                                   other reason. See json_last_error() for more information.
     */
    public function parse(string $source, int $depth = 512, int $options = 0): Json\Value
    {
        $json = Json::parse($source, $depth, $options);

        $this->validate($json);

        return $json;
    }
}
