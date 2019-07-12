<?php

namespace Vcn\Pipette\Json\Validators;

class JsonSchemaRepository
{
    /**
     * @var \JsonSchema\Validator
     */
    private $validator;

    /**
     * @var string
     */
    private $baseUri;

    /**
     * @param \JsonSchema\Validator $validator A validator that schemas will use when performing validation.
     * @param string                $baseUri   A base URI to use. E.g. file:///foo/bar will yield a schema reference to
     *                                         file:///foo/bar/qux.schema.json if get("qux.schema.json") is called.
     */
    public function __construct(\JsonSchema\Validator $validator, string $baseUri)
    {
        $this->validator = $validator;
        $this->baseUri   = $baseUri;
    }

    /**
     * Obtain a schema referenced relative to the base URI.
     *
     * @param string $slug
     *
     * @return JsonSchema
     */
    public function get(string $slug): JsonSchema
    {
        return new JsonSchema($this->validator, sprintf("%s%s", $this->baseUri, $slug));
    }
}
