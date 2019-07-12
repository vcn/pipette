# Pipette

Easily extract what you need out of JSON.

## Quickstart

`composer require vcn/pipette`

(From the [examples/](examples) directory)

```php
<?php

use Vcn\Pipette\Json;

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

        return [$name, $category, $type, $codeRgba, $codeHex];
    };

    $source = file_get_contents(__DIR__ . '/colors.json');

    $colors = Json::parse($source)->field('colors')->arrayMap($parseColor);

    print_r(['colors' => $colors]);
} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    print_r($e->getMessage());
}
```

## Usage

Pipette expects you to have some string of which you have the strong suspicion that it represents JSON.
You can ask Pipette to parse it:

```php
<?php

use Vcn\Pipette\Json;

$input = <<<JSON
{
  "id": 672,
  "name": "Jane Doe"
}
JSON;

try {
    $json = Json::parse($input);
} catch (Json\Exception\CantDecode $e) {
    error_log($e);
}
```

Parsing might fail, but if it succeeds you are left with a `Json\Value`.
It represents any of the possible values it could have parsed to and allows you to then query that value:

```php
<?php

use Vcn\Pipette\Json;

$input = <<<JSON
{
  "a": [1,2,3],
  "b": [4,5,6]
}
JSON;

try {
    $json = Json::parse($input);

    $a = $json->field('a'); // Assert this is an object, assert the field 'a' is present, then retrieve it.
    $b = $json->field('b');

    $as = $a->arrayMap( // Assert the 'a' field is an array.
        function (Json\Value $value) {
            return $value->int(); // Assert each value in that array is a number and return those numbers as an array of ints.
        }
    );
    $bs = $b->arrayMap(
        function (Json\Value $value) {
            return $value->int();
        }
    );

    print_r(array_sum(array_merge($as, $bs))); // 21

} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    error_log($e);
}
```

Note that if any of those assertions are false, an exception is thrown.

## Optional values

Many methods can be prefixed with an upside-down question mark (¿).
This causes them to also return `null` in the case of `null` or the absence of a field in an object.

In the case of `¿field` the returned `Json\OptionalValue` is a nullsafe variant where all subsequent calls will return null if the original field was `null` or absent.

```php
<?php

use Vcn\Pipette\Json;

$input = <<<JSON
{
  "a": "some string"
}
JSON;

try {
    $json = Json::parse($input);

    // Expect $ to be an object, expect field $.a to be present and expect it to be a string or null:
    $foo = $json->field('a')->¿string();

    // Expect $ to be an object, if $.a is not present return null, otherwise expect field $.a to be a string or null:
    $bar = $json->¿field('a')->¿string();

    print_r([$foo, $bar]);

    // $.a is present but $.a is not an object, therefore this fails:
    $baz = $json->¿field('a')->¿field('b')->¿string();

    print_r($baz);
} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    error_log($e);
}
```

## Unions

If you expect your JSON to match any of more than one structures, you can use `either`:

```php
<?php

use Vcn\Pipette\Json;

$inputA = <<<JSON
{
  "a": "some string"
}
JSON;
$inputB = <<<JSON
{
  "b": 42
}
JSON;

try {
    foreach ([$inputA, $inputB] as $input) {
        $json = Json::parse($input);

        // With the first input this parser will match its first composite,
        // with the second input the first composite parser fails and falls back to the second.
        // In practice you will either coerce these different types or construct members of a sealed trait.
        $foo = $json->either(
            function (Json\Value $json) {
                return $json->field('a')->string();
            },
            function (Json\Value $json) {
                return $json->field('b')->int();
            }
        );

        print_r($foo);
    }
} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    error_log($e);
}
```

See [examples/huttonsrazor.php](examples/huttonsrazor.php) for another example.

## Validation beyond the types

Pipette also provides a basic interface to hook in stronger validation methods.
Currently it supports JSON Schema validations through [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema).

The idea is that you can define a `JsonSchemaRepository` as a dependency, that then contains references to `JsonSchemas`.
These schemas can then validate JSON after it has been parsed, but before you use pipette to transform it into typed data.

```php
<?php

namespace Vcn\Pipette\Examples;

use JsonSchema\Validator;
use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Validators\JsonSchemaRepository;

// Define a dependency.
// This looks for schema files inside the current directory.
// See the json-schema library to tailor this behaviour.
$validator = new Validator();
$baseUri   = "file://" . __DIR__;
$schemas   = new JsonSchemaRepository($validator, $baseUri);

try {
    // Somewhere that has access to this dependency.
    $parseInt = function (Json\Value $json) {
        return $json->int();
    };

    $parseColor = function (Json\Value $json) use ($parseInt) {
        $name     = $json->field('name')->string();
        $category = $json->field('category')->string();
        $type     = $json->¿field('type')->¿string();
        $codeRgba = $json->field('code')->field('rgba')->arrayMap($parseInt);
        $codeHex  = $json->field('code')->field('hex')->string();

        return [$name, $category, $type, $codeRgba, $codeHex];
    };

    $input  = file_get_contents(__DIR__ . '/colors.json');
    $json   = $schemas->get('/colors.schema.json')->parse($input); // Use the colors.schema.json schema to first validate the JSON before returning.
    $colors = $json->apply($parseColor);

    print_r($colors);

} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    echo $e->getMessage() . "\n";
}
```

See the [examples/](examples) directory for a typical request parser setup.

## Using Pipette for JSON-like data

Under the hood Pipette simply wraps the data returned by `json_decode()` and performs ad-hoc validations based on your queries.
That means that if you have data whose type is isomorphic to that of `json_decode()` you can use this library for that data as well:

```php
<?php

use Vcn\Pipette\Json;

try {
    $data = (object)[
        'foo'       => 'bar',
        'baz'       => 123,
        'seventeen' => (object)[
            'eighteen',
            'nineteen',
        ]
    ];

    // The object casts are necessary since json_decode produces an stdClass for JSON objects.
    $json = Json::pretend($data);

    $bar = $json->field('foo')->string();

    print_r($bar);
} catch (Json\Exception\AssertionFailed $e) {
    error_log($e);
}
```

What data does Pipette support? (or how does `php-json` represent JSON?)

- `string`
- `int` / `float` (JSON numbers)
- `bool`
- `null`
- `array` (Non-associative, JSON arrays)
- `stdClass` (JSON objects)

Pipette's behaviour for any other type is undefined.

**Careful!**

## Tests

Powered by [phpspec](http://www.phpspec.net/en/stable/): `php vendor/bin/phpspec run`
