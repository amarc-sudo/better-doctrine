<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Definition;

enum ParameterMode: string
{
    case IN = 'IN';
    case OUT = 'OUT';
    case INOUT = 'INOUT';
}
