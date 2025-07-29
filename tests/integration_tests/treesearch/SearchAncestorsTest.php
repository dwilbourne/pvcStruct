<?php

namespace pvcTests\struct\integration_tests\treesearch;

use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\treesearch\SearchAncestors;
use PHPUnit\Framework\TestCase;
use pvc\struct\treesearch\SearchBreadthFirst;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

class SearchAncestorsTest extends TestCase
{
    /**
     * @var TreeInterface
     */
    protected TreeInterface $tree;

    /**
     * @var SearchAncestors
     */
    protected SearchAncestors $search;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    public function setUp(): void
    {
        $ordered = false;
        $this->fixture = new TreenodeConfigurationsFixture();
        $testUtils = new TestUtils($this->fixture);
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->tree = $testUtils->testTreeSetup($ordered);
        $this->search = new SearchAncestors();
    }

    /**
     * @return void
     * @covers \pvc\struct\treesearch\SearchAncestors::next
     */
    public function testGetAncestors(): void
    {
        $this->search->setStartNode($this->tree->getNode(9));
        $expectedResult = $this->fixture->makeArrayOfAncestorsOfNodeWithNodeIdNine();
        $actualResult = TestUtils::getNodeIdsFromNodeArray($this->search->getNodes());
        $this->assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * @return void
     * @covers \pvc\struct\treesearch\SearchAncestors::next
     */
    public function testGetAncestorsMaxLevels(): void
    {
        $this->search->setStartNode($this->tree->getNode(9));
        $this->search->setMaxLevels(2);
        $expectedResult = $this->fixture->makeArrayOfAncestorsOfNodeWithNodeIdNineMaxLevelsTwo();
        $actualResult = TestUtils::getNodeIdsFromNodeArray($this->search->getNodes());
        $this->assertEqualsCanonicalizing($expectedResult, $actualResult);
    }


}
