# Pipette

Easily extract what you need out of JSON.

## Quickstart

`composer require vcn/pipette`

(From the [examples/](examples) directory)

```php
<?php

use Vcn\Pipette\Json;

try {
    $json = Json::parse($input);

    $parseInt = function (Json\Value $json) {
        return $json->int();
    };

    $parseColor = function (Json\Value $value) use ($parseInt) {
        $color    = $value->field('color')->string();
        $category = $value->field('category')->string();
        $type     = $value->¿field('type')->¿string();
        $codeRgba = $value->field('code')->field('rgba')->arrayMap($parseInt);
        $codeHex  = $value->field('code')->field('hex')->string();

        return [
            'name'     => $color,
            'category' => $category,
            'type'     => $type,
            'codeRgba' => $codeRgba,
            'codeHex'  => $codeHex,
        ];
    };

    $colors = $json->field('colors')->arrayMap($parseColor);

    print_r(['colors' => $colors]);
} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    print_r($e->getMessage());
}
```

## Motivation

Suppose you have some JSON document that say, represents a user object:

```json
{
  "id": 672,
  "name": "Jane Doe"
}
```

And you wanted to parse that object.
One way to do that is by installing the `php-json` extension and call `json_decode`:

```php
<?php

$input = <<<JSON
{
  "id": 672,
  "name": "Jane Doe"
}
JSON;

$json = json_decode($input);

if ($json === null) {
    $message = (string)json_last_error_msg();
    $code    = (int)json_last_error();

    throw new Exception($message, $code);
}

if (!$json instanceof stdClass) {
    throw new Exception(sprintf("Expected $ to be an object, %s given.", gettype($json)));
}

if (!property_exists($json, 'id')) {
    throw new Exception("Expected $.id to be present, none given.");
}

if (!is_int($json->id) || !is_float($json->id)) {
    throw new Exception(sprintf("Expected $.id to be a number, %s given.", gettype($json->id)));
}

if (!property_exists($json, 'name')) {
    throw new Exception("Expected $.name to be present, none given.");
}

if (!is_string($json->name)) {
    throw new Exception(sprintf("Expected $.id to be a string, %s given.", gettype($json->name)));
}

$id   = (int)$json['id'];
$name = $json['name'];
```

That's 23 lines of code in order to bind `$id` and `$name` such that they are well typed and any invalid document is rejected with a clear error message.

Most will omit all the conditionals and simply assume the correctness of the document.
If it works, it works, right?

If you want the same thorough structural validation without all the boilerplate, you should use this library!
Pipette takes care of all the conditionals for you:
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

    $id   = $json->field('id')->int();
    $name = $json->field('name')->string();
} catch (Json\Exception\CantDecode | Json\Exception\AssertionFailed $e) {
    error_log($e);
}
```

One line to validate the syntax.
One line to extract and bind the `$id` variable.
One line to extract and bind the `$name` variable.
One exception if something goes wrong.

Pipette allows you to specify *what* you expect and Pipette gets you exactly what you ask, checking those expectations along the way.

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

Provided by [phpspec](http://www.phpspec.net/en/stable/): `php vendor/bin/phpspec run`
