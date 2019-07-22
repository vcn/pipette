<?php

namespace Vcn\Pipette\Examples;

use JsonSchema\Validator;
use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Validators\JsonSchemaRepository;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Color.php';
require __DIR__ . '/RequestParser.php';

try {
    $validator     = new Validator();
    $baseUri       = "file://" . __DIR__;
    $schemas       = new JsonSchemaRepository($validator, $baseUri);
    $requestParser = new RequestParser($schemas);

    $json   = file_get_contents(__DIR__ . '/colors.json');
    $colors = $requestParser->parseRequest($json);

    print_r($colors);

} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    echo $e->getMessage() . "\n";
}
