<?php

namespace tests\Vcn\Pipette\Json;

use DateTimeImmutable;
use PhpSpec\ObjectBehavior;
use tests\res\Vcn\Pipette\NonEmptyEnum;
use tests\res\Vcn\Pipette\NonEmptyVcnEnum;
use Vcn\Pipette\Json\Exception;
use Vcn\Pipette\Json\OptionalValue;
use Vcn\Pipette\Json\Value;
use Webmozart\Assert\Assert;

class OptionalValueSpec extends ObjectBehavior
{
    /**
     * @test
     */
    public function it_should_say_it_is_null_if_it_is_null()
    {
        $this->beConstructedWith(null, '$');

        $this->isNull()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_null_if_it_is_null2()
    {
        $this->beConstructedWith(new Value(null, '$'), '$');

        $this->isNull()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_false_if_it_is_false()
    {
        $this->beConstructedWith(new Value(false, '$'), '$');

        $this->isFalse()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_true_if_it_is_true()
    {
        $this->beConstructedWith(new Value(true, '$'), '$');

        $this->isTrue()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_a_number_if_it_is_a_number()
    {
        $this->beConstructedWith(new Value(27.4, '$'), '$');

        $this->isNumber()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_a_string_if_it_is_a_string()
    {
        $this->beConstructedWith(new Value("chocolate flakes", '$'), '$');

        $this->isString()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_a_bool_if_it_is_a_bool()
    {
        $this->beConstructedWith(new Value(true, '$'), '$');

        $this->isBool()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_an_object_if_it_is_an_object()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->isObject()->shouldBe(true);
    }

    /**
     * @test
     */
    public function it_should_say_it_is_an_array_if_it_is_an_array()
    {
        $json = json_decode('[1,2,3]');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->isArray()->shouldBe(true);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_value_accessing_an_existent_field()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿field('a')->¿int()->shouldBe(1);
        $this->¿field('a')->getPointer()->shouldBe('$.a');
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_value_accessing_an_existent_array_item()
    {
        $json = json_decode('["a"]');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿nth(0)->¿string()->shouldBe('a');
        $this->¿nth(0)->getPointer()->shouldBe('$[0]');
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_push_the_pointer_forward_when_chaining_fields()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿field('b')->¿nth(2)->¿int()->shouldBe(null);
        $this->¿field('b')->¿nth(2)->getPointer()->shouldBe("$.b[2]");
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_push_the_pointer_forward_when_chaining_fields2()
    {
        $this->beConstructedWith(null, '$');

        $this->¿field('b')->¿nth(2)->¿int()->shouldBe(null);
        $this->¿field('b')->¿nth(2)->getPointer()->shouldBe("$.b[2]");
    }


    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_array_map_on_a_non_null_optional_array()
    {
        $json = [1, 2, 3];

        $this->beConstructedWith(new Value($json, '$'), '$');

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

        $this->beConstructedWith(new Value($json, '$'), '$');

        $f = function (Value $v) {
            return $v->int() + 1;
        };

        $this->¿arrayMap($f)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_object_map_on_a_non_null_optional_object()
    {
        $json = json_decode('{"a": 1, "aa": 2, "aaa": 3}');

        $this->beConstructedWith(new Value($json, '$'), '$');

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

        $this->beConstructedWith(new Value($json, '$'), '$');

        $f = function (Value $v) {
            return $v->int();
        };

        $this->¿objectMap($f)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_an_int_if_accessing_a_non_null_optional_int()
    {
        $json = 12;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿int()->shouldBe(12);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_int()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿int()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_float_if_accessing_a_non_null_optional_float()
    {
        $json = 12.;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿float()->shouldBe(12.);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_float()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿float()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_string_if_accessing_a_non_null_optional_string()
    {
        $json = "chocolate flakes";

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿string()->shouldBe("chocolate flakes");
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_string()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿string()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_date_if_accessing_a_non_null_optional_timestamp()
    {
        $json = 1;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿timestamp()->shouldBeAnInstanceOf(DateTimeImmutable::class);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_timestamp()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿timestamp()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_date_if_accessing_a_non_null_optional_date()
    {
        $json   = "2018-01-01";
        $format = 'Y-m-d';

        $this->beConstructedWith(new Value($json, '$'), '$');

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

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿date($format)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_vcn_enum_if_accessing_a_non_null_optional_vcn_enum(): void
    {
        $json = "A";

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿enum(NonEmptyVcnEnum::class)->shouldBe(NonEmptyVcnEnum::A());
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_vcn_enum(): void
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿enum(NonEmptyVcnEnum::class)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_native_enum_if_accessing_a_non_null_optional_native_enum(): void
    {
        $json = "A";

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿enum(NonEmptyEnum::class)->shouldBe(NonEmptyEnum::A);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_native_enum(): void
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿enum(NonEmptyEnum::class)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_base64_string_if_accessing_a_non_null_optional_base64_string()
    {
        $json = base64_encode("chocolate flakes");

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿base64()->shouldBe("chocolate flakes");
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_base64_string()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿base64()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_a_bool_if_accessing_a_non_null_optional_bool()
    {
        $json = false;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿bool()->shouldBe(false);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_bool()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿bool()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_true_if_accessing_a_non_null_optional_true()
    {
        $json = true;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿true()->shouldBe(true);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_true()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿true()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_false_if_accessing_a_non_null_optional_false()
    {
        $json = false;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿false()->shouldBe(false);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_a_null_optional_false()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->¿false()->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_provide_null_if_accessing_null()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $this->null()->shouldBe(null);
    }

    /**
     * @test
     */
    public function it_should_fail_accessing_null_on_non_null()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $e = new Exception\AssertionFailed("Expected $ to be null, object given.");

        $this->shouldThrow($e)->during('null', []);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_apply_functions_to_a_non_null_optional_value()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $f = function (Value $value) {
            return 22;
        };

        $this->¿apply($f)->shouldBe(22);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_return_null_applying_to_a_null_optional_value()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

        $f = function (Value $value) {
            return 22;
        };

        $this->¿apply($f)->shouldBe(null);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_either_two_functions_on_a_non_null_optional_value()
    {
        $json = json_decode('{"a": 1}');

        $this->beConstructedWith(new Value($json, '$'), '$');

        $thrownException   = new Exception\AssertionFailed();
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

        $this->shouldThrow($expectedException)->during('¿either', [$h, $h]);
        $this->shouldThrow($r)->during('¿either', [$v, $f]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_either_two_functions_on_a_null_optional_value()
    {
        $json = null;

        $this->beConstructedWith(new Value($json, '$'), '$');

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

        $this->beConstructedWith(new Value($json, '$'), '$');
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

        $this->beConstructedWith(new Value($json, '$'), '$');
        $this->encode()->shouldBe($encoded);
        $this->¿encode()->shouldBe(null);
        $this->prettyPrint()->shouldBe($pretty);
        $this->¿prettyPrint()->shouldBe(null);
    }

    public function it_should_json_serialize()
    {
        $json = (object)[
            "1" => "a",
            "2" => "b",
            "3" => "c",
        ];

        Assert::same(json_encode(new OptionalValue(new Value($json, '$'), '$')), '{"1":"a","2":"b","3":"c"}');
    }

    public function it_should_json_serialize_a_null_value()
    {
        $json = null;

        Assert::same(json_encode(new OptionalValue(null, '$')), 'null');
    }
}
