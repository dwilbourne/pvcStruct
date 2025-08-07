<?php

namespace pvcTests\struct\integration_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\tree\Tree;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

class TreenodeTest extends TestCase
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
    public function testHydrationWithDtos(): void
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
     * see the diagram of the tree in TreenodeConfigurationFixture for an
     * easy visual of the node relationships
     * @covers \pvc\struct\tree\node\Treenode::isAncestorOf
     */
    public function testIsAncestorOf(): void
    {
        $inputArray = $this->testUtils->makeDtoArray();
        $this->tree->hydrate($inputArray);

        self::assertTrue($this->tree->getRoot()->isAncestorOf($this->tree->getNode(1)));
        self::assertTrue($this->tree->getRoot()->isAncestorOf($this->tree->getNode(5)));
        self::assertFalse($this->tree->getNode(5)->isAncestorOf($this->tree->getRoot()));
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\Treenode::isDescendantOf
     */
    public function testIsDescendantOf(): void
    {
        $inputArray = $this->testUtils->makeDtoArray();
        $this->tree->hydrate($inputArray);

        self::assertFalse($this->tree->getRoot()->isDescendantOf($this->tree->getNode(1)));
        self::assertTrue($this->tree->getNode(5)->isDescendantOf($this->tree->getRoot()));
    }
}
