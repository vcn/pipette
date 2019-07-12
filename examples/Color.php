<?php

namespace Vcn\Pipette\Examples;

class Color
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $category;

    /**
     * @var null|string
     */
    private $type;

    /**
     * @var int[]
     */
    private $codeRgba;

    /**
     * @var string
     */
    private $codeHex;

    /**
     * @param string      $name
     * @param string      $category
     * @param null|string $type
     * @param int[]       $codeRgba
     * @param string      $codeHex
     */
    public function __construct(string $name, string $category, ?string $type, array $codeRgba, string $codeHex)
    {
        $this->name     = $name;
        $this->category = $category;
        $this->type     = $type;
        $this->codeRgba = $codeRgba;
        $this->codeHex  = $codeHex;
    }
}
