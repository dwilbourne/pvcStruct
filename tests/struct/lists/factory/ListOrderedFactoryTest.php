<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\lists\factory;

use pvc\struct\lists\factory\ListOrderedFactory;
use PHPUnit\Framework\TestCase;
use pvc\struct\lists\ListOrdered;

class ListOrderedFactoryTest extends TestCase
{
    public function testMakeList() : void
    {
        $factory = new ListOrderedFactory();
        $list = $factory->makeList();
        self::assertTrue($list instanceof ListOrdered);
    }
}
