<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\factory\TreenodeAbstractFactory;
use pvc\struct\tree\node\TreenodeValueValidatorDefault;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\CollectionUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\NodeTypeUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeValueObjectUnorderedFactory;

class TreeUnorderedTest extends TestCase
{

    protected TreeUnordered $tree;

    protected TreenodeConfigurationsFixture $fixture;

    protected array $valueObjectArray;

    public function setUp(): void
    {
        $factory = new TreenodeValueObjectUnorderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory);

        $collectionFactory = new CollectionUnorderedFactory();
        $nodeTypeFactory = new NodeTypeUnorderedFactory();
        $treenodeFactory = new TreenodeAbstractFactory(
            $nodeTypeFactory,
            $collectionFactory
        );

        $this->tree = new TreeUnordered($this->fixture->getTreeId(), $treenodeFactory);

        $this->valueObjectArray = $this->fixture->makeValueObjectArray();
        $this->tree->hydrate($this->valueObjectArray);
    }

    /**
     * testHydration
     * @covers \pvc\struct\tree\tree\TreeAbstract::hydrate
     * @covers \pvc\struct\tree\tree\TreeAbstract::insertNodeRecurse
     * @covers \pvc\struct\tree\tree\TreeUnordered::sortChildValueObjects
     */
    public function testHydration(): void
    {
        self::assertEquals(count($this->valueObjectArray), count($this->tree->getNodes()));
    }

    /**
     * testDeleteNodeRecurse
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\tree\TreeAbstract::deleteNode
     * @covers \pvc\struct\tree\tree\TreeAbstract::deleteNodeRecurse
     */
    public function testDeleteNodeRecurse(): void
    {
        $expectedRemainingNodeIds = $this->fixture->makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively();
        $deleteBranchOK = true;
        $this->tree->deleteNode(1, $deleteBranchOK);
        $actualRemainingNodes = $this->tree->getNodes();
        $actualRemainingNodeIds = [];
        foreach ($actualRemainingNodes as $node) {
            $actualRemainingNodeIds[] = $node->getNodeId();
        }
        self::assertEqualsCanonicalizing($expectedRemainingNodeIds, $actualRemainingNodeIds);
    }
}
