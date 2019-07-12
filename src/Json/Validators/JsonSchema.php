<?php

namespace Vcn\Pipette\Json\Validators;

use Throwable;
use Vcn\Pipette\Json;
use Vcn\Pipette\Json\Exception;

class JsonSchema implements Validator
{
    use ValidatorTrait;

    /**
     * @var \JsonSchema\Validator
     */
    private $validator;

    /**
     * @var string
     */
    private $ref;

    /**
     * @param \JsonSchema\Validator $validator
     * @param string                $ref
     *
     * @internal
     */
    public function __construct(\JsonSchema\Validator $validator, string $ref)
    {
        $this->validator = $validator;
        $this->ref       = $ref;
    }

    /**
     * @inheritDoc
     */
    public function validate(Json\Value $json): void
    {
        try {
            $mixed = $json->mixed();

            $this->validator->reset();
            $this->validator->validate($mixed, (object)['$ref' => $this->ref]);

        } catch (Throwable $e) {
            throw new Exception\Runtime($e->getMessage(), 0, $e);
        }

        foreach ($this->validator->getErrors() as $error) {
            throw new Exception\AssertionFailed(
                sprintf(
                    "JSON Schema violation at $%s : %s.",
                    $error['property'] !== '' ? sprintf(".%s", $error['property']) : '',
                    $error['message']
                )
            );
        }
    }

    /**
     * @return \JsonSchema\Validator
     */
    public function getValidator(): \JsonSchema\Validator
    {
        return $this->validator;
    }

    /**
     * @return string
     */
    public function getRef(): string
    {
        return $this->ref;
    }
}
