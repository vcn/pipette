<?php

namespace tests\Vcn\Pipette\Json\Validators;

use JsonSchema\Validator;
use PhpSpec\ObjectBehavior;
use RuntimeException;
use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Exception;

class JsonSchemaSpec extends ObjectBehavior
{
    /**
     * @test
     * @throws Exception\AssertionFailed
     */
    public function it_should_succesfully_validate(Validator $validator, Json\Value $json)
    {
        $ref   = "file:///foo/bar.schema.json";
        $mixed = true;

        $this->beConstructedWith($validator, $ref);

        $json->mixed()->willReturn($mixed);

        $validator->reset()->shouldBeCalled();
        $validator->validate($mixed, (object)['$ref' => $ref])->shouldBeCalled();
        $validator->getErrors()->willReturn([]);

        $this->validate($json);
    }

    /**
     * @test
     */
    public function it_should_fail_to_validate(Validator $validator, Json\Value $json)
    {
        $ref   = "file:///foo/bar.schema.json";
        $mixed = true;
        $error = ['property' => '', 'message' => 'Expected an object, true given'];
        $e     = new Exception\AssertionFailed("JSON Schema violation at $ : Expected an object, true given.");

        $this->beConstructedWith($validator, $ref);

        $json->mixed()->willReturn($mixed);

        $validator->reset()->shouldBeCalled();
        $validator->validate($mixed, (object)['$ref' => $ref])->shouldBeCalled();
        $validator->getErrors()->willReturn([$error]);

        $this->shouldThrow($e)->during('validate', [$json]);
    }

    /**
     * @test
     */
    public function it_should_collapse_multiple_errors(Validator $validator, Json\Value $json)
    {
        $ref    = "file:///foo/bar.schema.json";
        $mixed  = true;
        $errors = [
            ['property' => '', 'message' => 'Expected an object, true given'],
            ['property' => '', 'message' => 'Expected a string, true given'],
            ['property' => '', 'message' => 'Expected a donkey, true given'],
        ];
        $e      = new Exception\AssertionFailed(
            "JSON Schema violations:\n" .
            "    $ : Expected an object, true given.\n" .
            "    $ : Expected a string, true given.\n" .
            "    $ : Expected a donkey, true given."
        );

        $this->beConstructedWith($validator, $ref);

        $json->mixed()->willReturn($mixed);

        $validator->reset()->shouldBeCalled();
        $validator->validate($mixed, (object)['$ref' => $ref])->shouldBeCalled();
        $validator->getErrors()->willReturn($errors);

        $this->shouldThrow($e)->during('validate', [$json]);
    }

    /**
     * @test
     */
    public function it_should_gracefully_go_pants_up(Validator $validator, Json\Value $json)
    {
        $ref   = "file:///foo/bar";
        $mixed = true;
        $e     = new RuntimeException("Failed to reticulate splines.");
        $e2    = new Exception\Runtime("Failed to reticulate splines.", 0, $e);

        $this->beConstructedWith($validator, $ref);

        $json->mixed()->willReturn($mixed);

        $validator->reset()->shouldBeCalled();
        $validator->validate($mixed, (object)['$ref' => $ref])->willThrow($e)->shouldBeCalled();

        $this->shouldThrow($e2)->during('validate', [$json]);
    }

    /**
     * @test
     * @throws Exception\AssertionFailed
     * @throws Exception\CantDecode
     */
    public function it_should_validate_after_parsing(Validator $validator, Json\Value $json)
    {
        $ref   = "file:///foo/bar.schema.json";
        $json  = 'true';
        $mixed = true;

        $this->beConstructedWith($validator, $ref);

        $validator->reset()->shouldBeCalled();
        $validator->validate($mixed, (object)['$ref' => $ref])->shouldBeCalled();
        $validator->getErrors()->willReturn([]);

        $this->parse($json);
    }
}
