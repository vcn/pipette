<?php

use Vcn\Pipette\Json;

require __DIR__ . '/../vendor/autoload.php';

// Hutton's Razor describes an abstract syntax tree where each value is either a leaf or the addition of two values.
// In this example the union is encoded with the "val" and "+" tags as object keys, where the "val" key points to an
// integer, and the "+" tag points to an object with two fields "a" and "b", that both recursively point to values.
$input = <<<JSON
{
  "+": {
    "a": {
      "val": 6
    },
    "b": {
      "+": {
        "a": {
          "val": 2
        },
        "b": {
          "val": 9
        }
      }
    }
  }
}
JSON;

try {
    // This parser translates it to a string "(6 + (2 + 9))".
    $parseExpr = function (Json\Value $json) use (&$parseExpr) {
        $parseAdd = function (Json\Value $value) use ($parseExpr) {
            return sprintf(
                "(%s + %s)",
                $value->field('+')->field('a')->apply($parseExpr),
                $value->field('+')->field('b')->apply($parseExpr)
            );
        };

        $parseVal = function (Json\Value $value) {
            return (string)$value->field('val')->int();
        };

        return $json->either($parseAdd, $parseVal);
    };

    $expr = Json::parse($input)->apply($parseExpr);

    print_r($expr);
} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    print_r($e->getMessage());
}
