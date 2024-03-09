<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\struct\tree\factory\TreenodeAbstractFactory;
use pvc\struct\tree\tree\TreeOrdered;
use pvcTests\struct\integration_tests\tree\fixture\CollectionOrderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\NodeTypeOrderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeValueObjectOrderedFactory;

class TreenodeOrderedTest extends TestCase
{
    protected TreeOrdered $tree;

    protected TreenodeConfigurationsFixture $fixture;

    protected array $valueObjectArray;


    public function setUp(): void
    {
        $factory = new TreenodeValueObjectOrderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory);


        $collectionFactory = new CollectionOrderedFactory();
        $nodeTypeFactory = new NodeTypeOrderedFactory();
        $treenodeFactory = new TreenodeAbstractFactory(
            $nodeTypeFactory,
            $collectionFactory
        );

        $handler = $this->createMock(TreeAbstractEventHandlerInterface::class);

        $this->tree = new TreeOrdered($this->fixture->getTreeId(), $treenodeFactory, $handler);

        $this->valueObjectArray = $this->fixture->makeValueObjectArray();
        $this->tree->hydrate($this->valueObjectArray);
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
