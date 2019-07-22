<?php

namespace Vcn\Pipette\Examples;

use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Validators\JsonSchemaRepository;

class RequestParser
{
    /**
     * @var JsonSchemaRepository
     */
    private $schemas;

    /**
     * @param JsonSchemaRepository $schemas
     */
    public function __construct(JsonSchemaRepository $schemas)
    {
        $this->schemas = $schemas;
    }

    /**
     * @param string $body
     *
     * @return Color[]
     * @throws Json\Exception\AssertionFailed
     * @throws Json\Exception\CantDecode
     */
    public function parseRequest(string $body)
    {
        try {
            $parseInt = function (Json\Value $json) {
                return $json->int();
            };

            $parseColor = function (Json\Value $json) use ($parseInt) {
                $name     = $json->field('name')->string();
                $category = $json->field('category')->string();
                $type     = $json->¿field('type')->¿string();
                $codeRgba = $json->field('code')->field('rgba')->arrayMap($parseInt);
                $codeHex  = $json->field('code')->field('hex')->string();

                return new Color($name, $category, $type, $codeRgba, $codeHex);
            };

            $json = $this->schemas->get('/colors.schema.json')->parse($body);

            return $json->arrayMap($parseColor);

        } catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
            throw $e;
        }
    }
}
