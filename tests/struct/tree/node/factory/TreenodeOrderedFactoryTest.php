<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\node\factory;

use Mockery;
use pvc\struct\lists\factory\ListOrderedFactory;
use pvc\struct\lists\ListOrdered;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;
use PHPUnit\Framework\TestCase;
use pvc\struct\tree\node\TreenodeOrdered;

class TreenodeOrderedFactoryTest extends TestCase
{
    protected TreenodeOrderedFactory $treenodeFactory;

    /** @phpstan-ignore-next-line */
    protected $listFactory;

    public function setUp(): void
    {
        $this->treenodeFactory = new TreenodeOrderedFactory();
        $this->listFactory = Mockery::mock(ListOrderedFactory::class);
        $orderedList = new ListOrdered();
        $this->listFactory->shouldReceive('makeList')->withNoArgs()->andReturn($orderedList);
        $this->treenodeFactory->setListFactory($this->listFactory);
    }

    public function testSetGetListFactory() : void
    {
        self::assertEquals($this->listFactory, $this->treenodeFactory->getListFactory());
    }

    public function testMakeTreenodeOrdered() : void
    {
        $node = $this->treenodeFactory->makeTreenode(1);
        self::assertTrue(TreenodeOrdered::class == get_class($node));
    }
}
