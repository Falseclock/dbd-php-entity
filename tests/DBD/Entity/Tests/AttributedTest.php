<?php
declare(strict_types=1);

namespace DBD\Entity\Tests;

use DBD\Entity\Tests\Entities\Attributed;
use PHPUnit\Framework\TestCase;

class AttributedTest extends TestCase
{
    public function testSome(): void
    {
        new Attributed();

        self::assertTrue(true);
    }

    public function testAttribute(): void
    {
        self::assertEquals('name', Attributed::map()->name->name);
    }
}
