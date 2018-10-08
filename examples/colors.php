<?php

use Vcn\Pipette\Json;

require __DIR__ . '/../vendor/autoload.php';

// In this example we expect some JSON to contain a "colors" array, each with a color inside. Each color has a "color",
// "category", an optional "type", a "code" field containing "rgba" with an array of integers describing the rgba
// encoding and "hex" describing the hex encoding.
$input = <<<JSON
{
  "colors": [
    {
      "color": "black",
      "category": "hue",
      "type": "primary",
      "code": {
        "rgba": [255,255,255,1],
        "hex": "#000"
      }
    },
    {
      "color": "white",
      "category": "value",
      "code": {
        "rgba": [0,0,0,1],
        "hex": "#FFF"
      }
    },
    {
      "color": "red",
      "category": "hue",
      "type": "primary",
      "code": {
        "rgba": [255,0,0,1],
        "hex": "#FF0"
      }
    },
    {
      "color": "blue",
      "category": "hue",
      "type": "primary",
      "code": {
        "rgba": [0,0,255,1],
        "hex": "#00F"
      }
    },
    {
      "color": "yellow",
      "category": "hue",
      "type": "primary",
      "code": {
        "rgba": [255,255,0,1],
        "hex": "#FF0"
      }
    },
    {
      "color": "green",
      "category": "hue",
      "type": "secondary",
      "code": {
        "rgba": [0,255,0,1],
        "hex": "#0F0"
      }
    }
  ]
}
JSON;

try {
    $json = Json::parse($input);

    // A parser for integers.
    $parseInt = function (Json\Value $json) {
        return $json->int();
    };

    // A parser for colors:
    $parseColor = function (Json\Value $json) use ($parseInt) {
        $color    = $json->field('color')->string();
        $category = $json->field('category')->string();
        $type     = $json->¿field('type')->¿string(); // Perform nullsafe navigation.
        $codeRgba = $json->field('code')->field('rgba')->arrayMap($parseInt); // An array of integers.
        $codeHex  = $json->field('code')->field('hex')->string();

        // You may have some Color class with these fields instead.
        return [
            'name'     => $color,
            'category' => $category,
            'type'     => $type,
            'codeRgba' => $codeRgba,
            'codeHex'  => $codeHex,
        ];
    };

    // Expect the JSON to represent an array of colors:
    $colors = $json->field('colors')->arrayMap($parseColor);

    print_r(['colors' => $colors]);


    // An incorrect parser for colors. This expects the $.color field to be called $.name instead:
    $parseColorIncorrectly = function (Json\Value $json) use ($parseInt) {
        $name     = $json->field('name')->string(); // Oops!
        $category = $json->field('category')->string();
        $type     = $json->¿field('type')->¿string();
        $codeRgba = $json->field('code')->field('rgba')->arrayMap($parseInt);
        $codeHex  = $json->field('code')->field('hex')->string();

        return [
            'name'     => $name,
            'category' => $category,
            'type'     => $type,
            'codeRgba' => $codeRgba,
            'codeHex'  => $codeHex,
        ];
    };

    // This throws a clean exception with a clear cause of error:
    $colors = $json->field('colors')->arrayMap($parseColorIncorrectly);
} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    print_r($e->getMessage());
}
