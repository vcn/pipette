<?php

namespace tests\res\Vcn\Pipette;

use Vcn\Lib\Enum;

/**
 * @method static NonEmptyEnum A()
 * @method static NonEmptyEnum B()
 * @method static NonEmptyEnum C()
 */
class NonEmptyEnum extends Enum
{
    protected const A = 0;
    protected const B = 0;
    protected const C = 0;
}
