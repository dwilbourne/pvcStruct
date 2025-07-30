<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

class TreeTest extends TestCase
{
    protected TreeInterface $tree;

    protected TreenodeConfigurationsFixture $fixture;

    /**
     * testHydration
     *
     * @covers \pvc\struct\tree\tree\Tree::initialize
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     */
    public function testHydrationWithDtos(): void
    {
        $ordered = false;
        $makeNodes = false;
        $this->treeSetup($ordered, $makeNodes);
        self::assertEquals(
            count($this->fixture->getNodeData()),
            count($this->tree->getNodes())
        );
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\tree\Tree::initialize
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     */
    public function testHydrationWithNodes(): void
    {
        $ordered = false;
        $makeNodes = true;
        $this->treeSetup($ordered, $makeNodes);
        self::assertEquals(
            count($this->fixture->getNodeData()),
            count($this->tree->getNodes())
        );
    }

    public function treeSetup(bool $ordered, bool $makeNodes = false): void
    {
        $this->fixture = new TreenodeConfigurationsFixture();
        $testUtils = new TestUtils($this->fixture);
        $this->tree = $testUtils->testTreeSetup($ordered, $makeNodes);
    }

    /**
     * testDeleteNodeRecurse
     *
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     * @covers \pvc\struct\tree\tree\Tree::deleteNodeRecurse
     */
    public function testDeleteNodeRecurse(): void
    {
        $ordered = false;
        $this->treeSetup($ordered);
        $expectedRemainingNodeIds
            = $this->fixture->makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively(
        );
        $deleteBranchOK = true;
        $this->tree->deleteNode(1, $deleteBranchOK);
        $actualRemainingNodeIds = TestUtils::getNodeIdsFromNodeArray(
            $this->tree->getNodes()
        );
        self::assertEqualsCanonicalizing(
            $expectedRemainingNodeIds,
            $actualRemainingNodeIds
        );
    }

    /**
     * testHydrationOrdered
     *
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     * @covers \pvcExamples\struct\ordered\TreeOrdered::__construct
     */
    public function testHydrationOrdered(): void
    {
        $ordered = true;
        $this->treeSetup($ordered);
        $expectedResultArray
            = $this->fixture->makeOrderedDepthFirstArrayOfAllNodeIds();
        $actualResultArray = TestUtils::getNodeIdsFromNodeArray(
            $this->tree->getNodes()
        );
        self::assertEqualsCanonicalizing(
            $expectedResultArray,
            $actualResultArray
        );
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     * @covers \pvcExamples\struct\unordered\TreeUnordered::__construct
     */
    public function testhydrationUnordered(): void
    {
        $ordered = false;
        $this->treeSetup($ordered);
        $expectedResultArray
            = $this->fixture->makeUnorderedDepthFirstArrayOfAllNodeIds();
        $actualResultArray = TestUtils::getNodeIdsFromNodeArray(
            $this->tree->getNodes()
        );
        self::assertEqualsCanonicalizing(
            $expectedResultArray,
            $actualResultArray
        );
    }
}
