<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

/**
 * @template PayloadType of HasPayloadInterface
 */
class TreeTest extends TestCase
{
    protected TreeInterface $tree;

    protected TreenodeConfigurationsFixture $fixture;

    public function treeSetup(bool $ordered): void
    {
        $this->fixture = new TreenodeConfigurationsFixture();
        $testUtils = new TestUtils($this->fixture);
        $this->tree = $testUtils->testTreeSetup($ordered);
    }

    /**
     * testHydration
     * @covers \pvc\struct\tree\tree\Tree::initialize
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     */
    public function testHydration(): void
    {
        $ordered = false;
        $this->treeSetup($ordered);
        self::assertEquals(count($this->fixture->getNodeData()), count($this->tree->getNodes()));
    }


    /**
     * testDeleteNodeRecurse
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     * @covers \pvc\struct\tree\tree\Tree::deleteNodeRecurse
     */
    public function testDeleteNodeRecurse(): void
    {
        $ordered = false;
        $this->treeSetup($ordered);
        $expectedRemainingNodeIds = $this->fixture->makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively();
        $deleteBranchOK = true;
        $this->tree->deleteNode(1, $deleteBranchOK);
        $actualRemainingNodeIds = TestUtils::getNodeIdsFromNodeArray($this->tree->getNodes());
        self::assertEqualsCanonicalizing($expectedRemainingNodeIds, $actualRemainingNodeIds);
    }

    /**
     * testHydrationOrder
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     * @covers \pvc\struct\tree\tree\TreeOrdered::__construct
     */
    public function testHydrationOrder(): void
    {
        $ordered = true;
        $this->treeSetup($ordered);
        $expectedResultArray = $this->fixture->makeOrderedDepthFirstArrayOfAllNodeIds();
        $actualResultArray = TestUtils::getNodeIdsFromNodeArray($this->tree->getNodes());
        self::assertEqualsCanonicalizing($expectedResultArray, $actualResultArray);
    }
}
