<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\lists\factory;

use pvc\struct\lists\factory\ListUnorderedFactory;
use PHPUnit\Framework\TestCase;
use pvc\struct\lists\ListUnordered;
use pvc\testingTraits\MockeryPositiveIntegerValidatorTrait;
use pvc\validator\base\Validator;

class ListUnorderedFactoryTest extends TestCase
{

    protected ListUnorderedFactory $factory;

    public function setUp(): void
    {
        $this->factory = new ListUnorderedFactory();
    }


    public function testMakeListUnordered() : void
    {
        $list = $this->factory->makeList();
        static::assertTrue($list instanceof ListUnordered);
    }
}
