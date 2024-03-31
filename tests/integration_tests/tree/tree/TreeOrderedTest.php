<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;
use pvc\struct\tree\node_value_object\factory\TreenodeValueObjectOrderedFactory;
use pvc\struct\tree\search\NodeDepthMap;
use pvc\struct\tree\tree\TreeOrdered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class TreeOrderedTest extends TestCase
{
    protected TreeOrdered $tree;

    protected TreenodeConfigurationsFixture $fixture;

    protected array $valueObjectArray;


    public function setUp(): void
    {
        $depthMap = $this->createMock(NodeDepthMap::class);
        $payloadTester = $this->createStub(PayloadTesterInterface::class);
        $payloadTester->method('testValue')->willReturn(true);

        $factory = new TreenodeValueObjectOrderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory, $depthMap);

        /** @var CollectionFactoryInterface $collectionFactory */
        $collectionFactory = new CollectionOrderedFactory();
        $treenodeFactory = new TreenodeOrderedFactory($collectionFactory, $payloadTester);
        $handler = $this->createMock(TreeAbstractEventHandlerInterface::class);

        $this->tree = new TreeOrdered($this->fixture->getTreeId(), $treenodeFactory, $handler);

        $this->valueObjectArray = $this->fixture->makeValueObjectArray();
        $this->tree->hydrate($this->valueObjectArray);
    }

    /**
     * testHydration
     * @covers \pvc\struct\tree\tree\TreeAbstract::hydrate
     * @covers \pvc\struct\tree\node\TreenodeOrdered::hydrate()
     * @covers \pvc\struct\tree\tree\TreeAbstract::insertNodeRecurse
     * @covers \pvc\struct\tree\tree\TreeOrdered::sortChildValueObjects
     */
    public function testHydrationCount(): void
    {
        self::assertEquals(count($this->valueObjectArray), count($this->tree->getNodes()));
    }

    /**
     * testHydrationOrder
     * @covers \pvc\struct\tree\tree\TreeAbstract::hydrate
     * @covers \pvc\struct\tree\tree\TreeOrdered::hydrate
     * @covers \pvc\struct\tree\tree\TreeAbstract::insertNodeRecurse
     * @covers \pvc\struct\tree\tree\TreeOrdered::sortChildValueObjects
     */
    public function testHydrationOrder(): void
    {
        $expectedResultArray = $this->fixture->makeOrderedDepthFirstArrayOfAllNodeIds();
        $actualResultArray = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($this->tree->getNodes());
        self::assertEqualsCanonicalizing($expectedResultArray, $actualResultArray);
    }
}
