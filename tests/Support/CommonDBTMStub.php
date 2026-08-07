<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Support;

class CommonDBTMStub extends CommonGLPIStub
{
    /** @return array<int, string> */
    public function getRights($interface = 'central'): array
    {
        return [];
    }
}
