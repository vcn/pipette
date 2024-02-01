<?php

namespace tests\res\Vcn\Pipette;

use Vcn\Lib\Enum;

/**
 * @method static NonEmptyVcnEnum A()
 * @method static NonEmptyVcnEnum B()
 * @method static NonEmptyVcnEnum C()
 */
class NonEmptyVcnEnum extends Enum
{
    protected const A = 0;
    protected const B = 0;
    protected const C = 0;
}
