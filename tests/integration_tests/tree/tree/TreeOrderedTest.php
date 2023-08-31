<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\factory\TreenodeAbstractFactory;
use pvc\struct\tree\node\TreenodeValueValidatorDefault;
use pvc\struct\tree\tree\TreeOrdered;
use pvcTests\struct\integration_tests\tree\fixture\CollectionOrderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\NodeTypeOrderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeValueObjectOrderedFactory;

class TreeOrderedTest extends TestCase
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
        $valueValidator = new TreenodeValueValidatorDefault();
        $treenodeFactory = new TreenodeAbstractFactory(
            $nodeTypeFactory,
            $collectionFactory,
            $valueValidator
        );

        $this->tree = new TreeOrdered($this->fixture->getTreeId(), $treenodeFactory);

        $this->valueObjectArray = $this->fixture->makeValueObjectArray();
        $this->tree->hydrate($this->valueObjectArray);
    }

    /**
     * testHydration
     * @covers \pvc\struct\tree\tree\TreeAbstract::hydrate
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
