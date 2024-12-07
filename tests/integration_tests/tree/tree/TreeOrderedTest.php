<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\dto\factory\TreenodeDTOOrderedFactory;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;
use pvc\struct\tree\tree\TreeOrdered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class TreeOrderedTest extends TestCase
{
    protected TreeOrdered $tree;

    protected TreenodeConfigurationsFixture $fixture;

    protected array $valueObjectArray;


    public function setUp(): void
    {
        $payloadTester = $this->createStub(PayloadTesterInterface::class);
        $payloadTester->method('testValue')->willReturn(true);

        $factory = new TreenodeDTOOrderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory);

        /** @var CollectionFactoryInterface $collectionFactory */
        $collectionFactory = new CollectionOrderedFactory();
        $treenodeFactory = new TreenodeOrderedFactory($collectionFactory, $payloadTester);

        $this->tree = new TreeOrdered($this->fixture->getTreeId(), $treenodeFactory);

        $this->valueObjectArray = $this->fixture->makeDTOArray();
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
