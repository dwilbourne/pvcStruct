<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\dto\factory\TreenodeDTOOrderedFactory;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;
use pvc\struct\tree\tree\TreeOrdered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class TreenodeOrderedTest extends TestCase
{
    protected TreeOrdered $tree;
    protected TreenodeConfigurationsFixture $fixture;
    protected array $DTOArray;


    public function setUp(): void
    {
        $factory = new TreenodeDTOOrderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory);

        $collectionFactory = new CollectionOrderedFactory();

        $payloadTester = $this->createStub(PayloadTesterInterface::class);
        $payloadTester->method('testValue')->willReturn(true);

        $treenodeFactory = new TreenodeOrderedFactory($collectionFactory, $payloadTester);

        $this->tree = new TreeOrdered($this->fixture->getTreeId(), $treenodeFactory);

        $this->DTOArray = $this->fixture->makeDTOArray();
        $this->tree->hydrate($this->DTOArray);
    }

    /**
     * testGetIndexReturnsZeroWithRootAsArgument
     *
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getIndex
     */
    public function testGetIndexOnRootNode(): void
    {
        self::assertEquals(0, $this->tree->getRoot()->getIndex());
    }

    /**
     * testGetIndex
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getIndex
     */
    public function testGetIndex(): void
    {
        /**
         * nodeid == 11 has siblings 9, 10 and 12.  In the ordered tree fixture, they appear in reverse order, e.g. 12,
         * 11, 10, 9
         */
        $node = $this->tree->getNode(11);
        self::assertEquals(1, $node->getIndex());
    }

    /**
     * testSetIndexOnRootDoesNothing
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setIndex
     */
    public function testSetIndexOnRootDoesNothing(): void
    {
        $this->tree->getRoot()->setIndex(5);
        self::assertEquals(0, $this->tree->getRoot()->getIndex());
    }

    /**
     * testSetIndex
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setIndex
     */
    public function testSetIndex(): void
    {
        /**
         * nodeid == 11 has siblings 9, 10 and 12.  In the ordered tree fixture, they appear in reverse order, e.g. 12,
         * 11, 10, 9
         */
        $node = $this->tree->getNode(11);
        $node->setIndex(3);
        self::assertEquals(0, $this->tree->getNode(12)->getIndex());
        self::assertEquals(1, $this->tree->getNode(10)->getIndex());
        self::assertEquals(2, $this->tree->getNode(9)->getIndex());
        self::assertEquals(3, $this->tree->getNode(11)->getIndex());
    }
}
