<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\tree\Tree;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

class TreeTest extends TestCase
{
    protected int $treeId = 1;

    protected Tree $tree;

    protected TreenodeConfigurationsFixture $fixture;

    protected TestUtils $testUtils;

    public function setUp(): void
    {
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->testUtils = new TestUtils($this->fixture);
        $this->tree = $this->testUtils->testTreeSetup($this->treeId);
    }

    /**
     * testHydration
     *
     * @covers \pvc\struct\tree\tree\Tree::initialize
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     */
    public function testHydration(): void
    {
        $inputArray = $this->testUtils->makeDtoArray();
        $this->tree->hydrate($inputArray);
        self::assertEquals(
            count($this->fixture->getNodeData()),
            count($this->tree->getNodeCollection())
        );
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \pvc\struct\tree\err\TreeNotInitializedException
     * @covers \pvc\struct\tree\tree\Tree::dehydrate
     */
    public function testDehydration(): void
    {
        $inputArray = $this->testUtils->makeDtoArray();
        $this->tree->hydrate($inputArray);
        $dehydrated = $this->tree->dehydrate();
        self::assertEqualsCanonicalizing($this->testUtils->makeDtoArray(), $dehydrated);
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
        $inputArray = $this->testUtils->makeDtoArray();
        $this->tree->hydrate($inputArray);

        $expectedRemainingNodeIds
            = $this->fixture->makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively(
        );
        $deleteBranchOK = true;
        $this->tree->deleteNode(1, $deleteBranchOK);
        $actualRemainingNodeIds = TestUtils::getNodeIdsFromNodeArray(
            $this->tree->getNodeCollection()->getElements()
        );
        self::assertEqualsCanonicalizing(
            $expectedRemainingNodeIds,
            $actualRemainingNodeIds
        );
    }
}
