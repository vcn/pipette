<?php

namespace tests\Vcn\Pipette\Json\Validators;

use Exception;
use JsonSchema\Validator;
use PhpSpec\ObjectBehavior;
use Vcn\Pipette\Json;

class JsonSchemaRepositorySpec extends ObjectBehavior
{
    /**
     * @test
     * @throws Exception
     */
    public function it_should_get(Validator $validator, Json\Value $json)
    {
        $baseUri = "file:///foo";
        $slug    = "/bar.schema.json";
        $ref     = "file:///foo/bar.schema.json";

        $this->beConstructedWith($validator, $baseUri);

        $schema = $this->get($slug);

        $schema->callOnWrappedObject('getValidator')->shouldBe($validator);
        $schema->callOnWrappedObject('getRef')->shouldBe($ref);
    }
}
