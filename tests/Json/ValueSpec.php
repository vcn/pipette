<?php

namespace tests\Vcn\Pipette\Json;

use DateTimeImmutable;
use PhpSpec\ObjectBehavior;
use tests\res\Vcn\Pipette\EmptyEnum;
use tests\res\Vcn\Pipette\NonEmptyEnum;
use Vcn\Pipette\Json\Exception;
use Vcn\Pipette\Json\Value;
use Webmozart\Assert\Assert;

class ValueSpec extends ObjectBehavior
{
    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_value_accessing_an_existent_field()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $this->field('a')->int()->shouldBe(1);
        $this->field('a')->getPointer()->shouldBe('$.a');

        $this->¿field('a')->¿int()->shouldBe(1);
        $this->¿field('a')->getPointer()->shouldBe('$.a');
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_a_field_on_a_non_object()
    {
        $json = json_decode('true');

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be an object, true given.");

        $this->shouldThrow($e)->during('field', ['b']);
        $this->shouldThrow($e)->during('¿field', ['b']);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_fail_accessing_a_non_existent_field()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $.b to be present, none given.");

        $this->shouldThrow($e)->during('field', ['b']);

        $this->¿field('b')->¿int()->shouldBe(null);
        $this->¿field('b')->getPointer()->shouldBe('$.b');
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_value_accessing_an_existent_array_item()
    {
        $json = json_decode('["a"]');

        $this->beConstructedWith($json, '$');

        $this->nth(0)->string()->shouldBe('a');
        $this->nth(0)->getPointer()->shouldBe('$[0]');

        $this->¿nth(0)->¿string()->shouldBe('a');
        $this->¿nth(0)->getPointer()->shouldBe('$[0]');
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_an_array_item_on_a_non_array()
    {
        $json = json_decode('true');

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be an array, true given.");

        $this->shouldThrow($e)->during('nth', [0]);
        $this->shouldThrow($e)->during('¿nth', [0]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_fail_accessing_a_non_existent_array_item()
    {
        $json = json_decode('["a", 1, true]');

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $[3] to be present, none given.");

        $this->shouldThrow($e)->during('nth', [3]);

        $this->¿nth(3)->¿int()->shouldBe(null);
        $this->¿nth(3)->getPointer()->shouldBe('$[3]');
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_coerce_void_to_null()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $this->¿field('b')->¿int()->shouldBe(null);
        $this->¿field('b')->getPointer()->shouldBe('$.b');
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_array_map_over_arrays()
    {
        $json = [1, 2, 3];

        $this->beConstructedWith($json, '$');

        $f = function (int $k, Value $v) {
            return $k + $v->int();
        };

        $this->arrayMapWithIndex($f)->shouldBe([1, 3, 5]);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_not_array_map_over_non_arrays()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $f = function (int $k, Value $v) {
            return $k + $v->int();
        };
        $e = new Exception\AssertionFailed("Expected $ to be an array, object given.");

        $this->shouldThrow($e)->during('arrayMapWithIndex', [$f]);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_rethrow_exceptions_when_array_mapping()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be an array, object given.");
        $f = function (int $k, Value $v) use ($e) {
            throw $e;
        };

        $this->shouldThrow($e)->during('arrayMapWithIndex', [$f]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_also_array_map_without_indices()
    {
        $json = [1, 2, 3];

        $this->beConstructedWith($json, '$');

        $f = function (Value $v) {
            return $v->int() + 1;
        };

        $this->arrayMap($f)->shouldBe([2, 3, 4]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_array_map_on_a_non_null_optional_array()
    {
        $json = [1, 2, 3];

        $this->beConstructedWith($json, '$');

        $f = function (Value $v) {
            return $v->int() + 1;
        };

        $this->¿arrayMap($f)->shouldBe([2, 3, 4]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_array_map_on_a_null_optional_array()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $f = function (Value $v) {
            return $v->int() + 1;
        };

        $this->¿arrayMap($f)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_object_map_over_objects()
    {
        $json = json_decode('{"a": 1, "aa": 2, "aaa": 3}');

        $this->beConstructedWith($json, '$');

        $f = function (string $k, Value $v) {
            return strlen($k) + $v->int();
        };

        $this->objectMapWithIndex($f)->shouldBe(["a" => 2, "aa" => 4, "aaa" => 6]);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_not_object_map_over_non_objects()
    {
        $json = [1, 2, 3];

        $this->beConstructedWith($json, '$');


        $f = function (string $k, Value $v) {
            return strlen($k) + $v->int();
        };
        $e = new Exception\AssertionFailed("Expected $ to be an object, array given.");

        $this->shouldThrow($e)->during('objectMapWithIndex', [$f]);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_rethrow_exceptions_when_object_mapping()
    {
        $json = json_decode('{"a": 1, "aa": 2, "aaa": 3}');

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be an array, object given.");
        $f = function (string $k, Value $v) use ($e) {
            throw $e;
        };

        $this->shouldThrow($e)->during('objectMapWithIndex', [$f]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_also_object_map_without_indices()
    {
        $json = json_decode('{"a": 1, "aa": 2, "aaa": 3}');

        $this->beConstructedWith($json, '$');

        $f = function (Value $v) {
            return $v->int();
        };

        $this->objectMap($f)->shouldBe(["a" => 1, "aa" => 2, "aaa" => 3]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_object_map_on_a_non_null_optional_object()
    {
        $json = json_decode('{"a": 1, "aa": 2, "aaa": 3}');

        $this->beConstructedWith($json, '$');

        $f = function (Value $v) {
            return $v->int();
        };

        $this->¿objectMap($f)->shouldBe(["a" => 1, "aa" => 2, "aaa" => 3]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_object_map_on_a_null_optional_object()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $f = function (Value $v) {
            return $v->int();
        };

        $this->¿objectMap($f)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_an_int_if_accessing_an_int()
    {
        $json = 12;

        $this->beConstructedWith($json, '$');

        $this->int()->shouldBe(12);
    }

    /**
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_an_int_if_accessing_a_float()
    {
        $json = 12.;

        $this->beConstructedWith($json, '$');

        $this->int()->shouldBe(12);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_an_int_on_a_non_number()
    {
        $json = "12";

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be a number, string given.");

        $this->shouldThrow($e)->during('int', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_an_int_if_accessing_a_non_null_optional_int()
    {
        $json = 12;

        $this->beConstructedWith($json, '$');

        $this->¿int()->shouldBe(12);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_int()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿int()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_float_if_accessing_a_float()
    {
        $json = 12.;

        $this->beConstructedWith($json, '$');

        $this->float()->shouldBe(12.);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_float_if_accessing_an_int()
    {
        $json = 12;

        $this->beConstructedWith($json, '$');

        $this->float()->shouldBe(12.);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_an_float_on_a_non_number()
    {
        $json = "12";

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be a number, string given.");

        $this->shouldThrow($e)->during('float', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_float_if_accessing_a_non_null_optional_float()
    {
        $json = 12.;

        $this->beConstructedWith($json, '$');

        $this->¿float()->shouldBe(12.);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_float()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿float()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_string_if_accessing_a_string()
    {
        $json = "chocolate flakes";

        $this->beConstructedWith($json, '$');

        $this->string()->shouldBe("chocolate flakes");
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_a_string_on_a_non_string()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be a string, null given.");

        $this->shouldThrow($e)->during('string', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_string_if_accessing_a_non_null_optional_string()
    {
        $json = "chocolate flakes";

        $this->beConstructedWith($json, '$');

        $this->¿string()->shouldBe("chocolate flakes");
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_string()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿string()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_date_if_accessing_a_date()
    {
        $json   = "2018-01-01";
        $format = 'Y-m-d';

        $this->beConstructedWith($json, '$');

        $this->date($format)->shouldBeAnInstanceOf(DateTimeImmutable::class);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_date_with_no_time_component()
    {
        $json = "2018-01-01";

        $this->beConstructedWith($json, '$');

        $this->date()->format('Y-m-d\TH:i:s')->shouldBe('2018-01-01T00:00:00');
    }

    /**
     * @test
     */
    public function it_should_failed_accessing_a_date_if_accessing_a_non_date()
    {
        $json   = "2018-01-";
        $format = 'Y-m-d';

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed(
            "Expected $ to be a date time string according to format Y-m-d, '2018-01-' given."
        );

        $this->shouldThrow($e)->during('date', [$format]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_date_if_accessing_a_non_null_optional_date()
    {
        $json   = "2018-01-01";
        $format = 'Y-m-d';

        $this->beConstructedWith($json, '$');

        $this->¿date($format)->shouldBeAnInstanceOf(DateTimeImmutable::class);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_date()
    {
        $json   = null;
        $format = 'Y-m-d';

        $this->beConstructedWith($json, '$');

        $this->¿date($format)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_an_enum_if_accessing_an_enum()
    {
        $json = "A";

        $this->beConstructedWith($json, '$');

        $this->enum(NonEmptyEnum::class)->shouldBe(NonEmptyEnum::A());
    }

    /**
     * @test
     */
    public function it_should_fail_accessing_an_enum_if_the_instance_is_not_known()
    {
        $json = "D";

        $this->beConstructedWith($json, '$');

        // phpspec vomits if you actually build the nested exception.
        $e = new \Exception(
            "Expected any of the following:\n" .
            "    - Expected $ to be enumeration constant 'A', 'D' given.\n" .
            "    - Expected $ to be enumeration constant 'B', 'D' given.\n" .
            "    - Expected $ to be enumeration constant 'C', 'D' given."
        );


        $this->shouldThrow($e)->during('enum', [NonEmptyEnum::class]);
    }

    /**
     * @test
     */
    public function it_should_fail_accessing_an_enum_if_the_enum_is_empty()
    {
        $json = "A";

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected field $ to by any of no enumeration constants, 'A' given.");

        $this->shouldThrow($e)->during('enum', [EmptyEnum::class]);
    }

    /**
     * @test
     */
    public function it_should_fail_accessing_an_enum_if_the_enum_does_not_exist()
    {
        $json = "A";

        $this->beConstructedWith($json, '$');

        $e1 = new Exception\Runtime("Class NotAnEnum does not exist.");
        $e2 = new Exception\Runtime("Class Vcn\Pipette\Json does not extend Vcn\Lib\Enum.");

        $this->shouldThrow($e1)->during('enum', ['NotAnEnum']);
        $this->shouldThrow($e2)->during('enum', ['Vcn\Pipette\Json']);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_an_enum_if_accessing_a_non_null_optional_enum()
    {
        $json = "A";

        $this->beConstructedWith($json, '$');

        $this->¿enum(NonEmptyEnum::class)->shouldBe(NonEmptyEnum::A());
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_enum()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿enum(NonEmptyEnum::class)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_base64_string_if_accessing_a_base64_string()
    {
        $json = base64_encode("chocolate flakes");

        $this->beConstructedWith($json, '$');

        $this->base64()->shouldBe("chocolate flakes");
    }

    /**
     * @test
     */
    public function it_should_fail_accessing_a_base64_string_if_accessing_a_non_string()
    {
        $json = 12;

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be a string, number given.");

        $this->shouldThrow($e)->during('base64', []);
    }

    /**
     * @test
     */
    public function it_should_fail_accessing_a_base64_string_if_accessing_a_non_base64_string()
    {
        $json = "½ chocolate flakes";

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed(
            "Expected $ to contain a base64 encoded string, it contained characters outside the base64 alphabet."
        );

        $this->shouldThrow($e)->during('base64', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_base64_string_if_accessing_a_non_null_optional_base64_string()
    {
        $json = base64_encode("chocolate flakes");

        $this->beConstructedWith($json, '$');

        $this->¿base64()->shouldBe("chocolate flakes");
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_base64_string()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿base64()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_bool_if_accessing_a_bool()
    {
        $json = true;

        $this->beConstructedWith($json, '$');

        $this->bool()->shouldBe(true);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_a_bool_on_a_non_bool()
    {
        $json = "chocolate flakes";

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be true or false, string given.");

        $this->shouldThrow($e)->during('bool', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_bool_if_accessing_a_non_null_optional_bool()
    {
        $json = false;

        $this->beConstructedWith($json, '$');

        $this->¿bool()->shouldBe(false);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_bool()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿bool()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_true_if_accessing_true()
    {
        $json = true;

        $this->beConstructedWith($json, '$');

        $this->true()->shouldBe(true);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_true_on_false()
    {
        $json = false;

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be true, false given.");

        $this->shouldThrow($e)->during('true', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_true_if_accessing_a_non_null_optional_true()
    {
        $json = true;

        $this->beConstructedWith($json, '$');

        $this->¿true()->shouldBe(true);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_true()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿true()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_false_if_accessing_false()
    {
        $json = false;

        $this->beConstructedWith($json, '$');

        $this->false()->shouldBe(false);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_false_on_true()
    {
        $json = true;

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be false, true given.");

        $this->shouldThrow($e)->during('false', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_false_if_accessing_a_non_null_optional_false()
    {
        $json = false;

        $this->beConstructedWith($json, '$');

        $this->¿false()->shouldBe(false);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_false()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->¿false()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_null()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $this->null()->shouldBe(null);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_fail_accessing_null_on_non_null()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $e = new Exception\AssertionFailed("Expected $ to be null, object given.");

        $this->shouldThrow($e)->during('null', []);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_apply_functions()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $f = function (Value $value) {
            return 22;
        };

        $this->apply($f)->shouldBe(22);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_apply_functions_to_a_non_null_optional_value()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $f = function (Value $value) {
            return 22;
        };

        $this->¿apply($f)->shouldBe(22);
    }

    /**
     * @test
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @throws \ReflectionException
     */
    public function it_should_return_null_applying_to_a_null_optional_value()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $f = function (Value $value) {
            return 22;
        };

        $this->¿apply($f)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_either_two_functions()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $thrownException   = new Exception\AssertionFailed("This is a failure.");
        $expectedException = Exception\ManyAssertionsFailed::fromFailedAssertions($thrownException, $thrownException);

        $r = new \Exception();
        $f = function (Value $value) {
            return 22;
        };
        $g = function (Value $value) {
            return 44;
        };
        $h = function (Value $value) use ($thrownException) {
            throw $thrownException;
        };
        $v = function (Value $value) use ($r) {
            throw $r;
        };

        $this->either($f, $g)->shouldBe(22);
        $this->either($g, $f)->shouldBe(44);
        $this->either($f, $h)->shouldBe(22);
        $this->either($h, $f)->shouldBe(22);
        $this->either($f, $v)->shouldBe(22);

        $this->shouldThrow($expectedException)->during('either', [$h, $h]);
        $this->shouldThrow($r)->during('either', [$v, $f]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_either_two_functions_on_a_non_null_optional_value()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith($json, '$');

        $thrownException   = new Exception\AssertionFailed("This is a failure.");
        $expectedException = Exception\ManyAssertionsFailed::fromFailedAssertions($thrownException, $thrownException);

        $r = new \Exception();
        $f = function (Value $value) {
            return 22;
        };
        $g = function (Value $value) {
            return 44;
        };
        $h = function (Value $value) use ($thrownException) {
            throw $thrownException;
        };
        $v = function (Value $value) use ($r) {
            throw $r;
        };

        $this->¿either($f, $g)->shouldBe(22);
        $this->¿either($g, $f)->shouldBe(44);
        $this->¿either($f, $h)->shouldBe(22);
        $this->¿either($h, $f)->shouldBe(22);
        $this->¿either($f, $v)->shouldBe(22);

        $this->shouldThrow($expectedException)->during('either', [$h, $h]);
        $this->shouldThrow($r)->during('either', [$v, $f]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_either_two_functions_on_a_null_optional_value()
    {
        $json = null;

        $this->beConstructedWith($json, '$');

        $f = function (Value $value) {
            return 22;
        };
        $g = function (Value $value) {
            return 44;
        };

        $this->¿either($f, $g)->shouldBe(null);
    }

    /**
     * @test
     */
    public function it_should_encode()
    {
        $json    = (object)[
            "1" => "a",
            "2" => "b",
            "3" => "c",
        ];
        $encoded = json_encode($json);
        $pretty  = json_encode($json, JSON_PRETTY_PRINT);

        $this->beConstructedWith($json, '$');

        $this->encode()->shouldBe($encoded);
        $this->¿encode()->shouldBe($encoded);
        $this->prettyPrint()->shouldBe($pretty);
        $this->¿prettyPrint()->shouldBe($pretty);
    }

    /**
     * @test
     */
    public function it_should_encode_null()
    {
        $json    = null;
        $encoded = json_encode($json);
        $pretty  = json_encode($json, JSON_PRETTY_PRINT);

        $this->beConstructedWith($json, '$');

        $this->encode()->shouldBe($encoded);
        $this->¿encode()->shouldBe(null);
        $this->prettyPrint()->shouldBe($pretty);
        $this->¿prettyPrint()->shouldBe(null);
    }

    /**
     * @test
     */
    public function it_should_fail_encoding_non_json()
    {
        $json = fopen('php://stdin', 'r');

        $this->beConstructedWith($json, '$');

        $this->shouldThrow(Exception\Runtime::class)->during('encode', []);

        fclose($json);
    }

    public function it_should_json_serialize()
    {
        $json = (object)[
            "1" => "a",
            "2" => "b",
            "3" => "c",
        ];

        Assert::same(json_encode(new Value($json, '$')), '{"1":"a","2":"b","3":"c"}');
    }
}
