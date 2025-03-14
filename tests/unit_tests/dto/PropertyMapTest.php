<?php

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\dto;

use PHPUnit\Framework\TestCase;
use pvc\struct\dto\PropertyMap;

class PropertyMapTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\struct\dto\PropertyMap::__construct
     */
    public function testConstruct(): void
    {
        $map = new PropertyMap('x', 'y', 'z');
        self::assertInstanceOf(PropertyMap::class, $map);
    }
}
