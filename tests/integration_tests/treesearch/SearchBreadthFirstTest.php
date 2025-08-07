<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\integration_tests\treesearch;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\treesearch\err\SetMaxSearchLevelsException;
use pvc\struct\treesearch\SearchBreadthFirst;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

/**
 * Class SearchBreadthFirstTest
 */
class SearchBreadthFirstTest extends TestCase
{
    protected int $treeId = 1;

    /**
     * @var TreeInterface
     */
    protected TreeInterface $tree;

    /**
     * @var SearchBreadthFirst
     */
    protected SearchBreadthFirst $search;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    protected TestUtils $testUtils;

    public function setUp(): void
    {
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->testUtils = new TestUtils($this->fixture);
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->tree = $this->testUtils->testTreeSetup($this->treeId);
        $inputArray = $this->testUtils->makeDtoArray();
        $this->tree->hydrate($inputArray);
        $this->search = new SearchBreadthFirst();
    }

    /**
     * testGetFullTree
     *
     * @covers \pvc\struct\treesearch\SearchBreadthFirst::rewind
     * @covers \pvc\struct\treesearch\SearchBreadthFirst::next
     * @covers \pvc\struct\treesearch\SearchBreadthFirst::getNextLevelOfNodes
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     */
    public function testGetFullTree(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult
            = $this->fixture->makeOrderedBreadthFirstArrayOfAllNodeIds();
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );
        /**
         * ordered search so do not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testMaxLevels
     *
     * @throws SetMaxSearchLevelsException
     * @covers \pvc\struct\treesearch\SearchAbstract::setMaxLevels
     * @covers \pvc\struct\treesearch\SearchAbstract::atMaxLevels
     * @covers \pvc\struct\treesearch\SearchAbstract::getCurrentLevel
     * @covers \pvc\struct\treesearch\SearchAbstract::setCurrentLevel
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     * @covers \pvc\struct\treesearch\SearchBreadthFirst::next
     */
    public function testMaxLevels(): void
    {
        $maxLevels = 3;
        $this->search->setStartNode($this->tree->getRoot());
        $this->search->setMaxLevels($maxLevels);

        $expectedResult
            = $this->fixture->makeOrderedBreadthFirstArrayThreeLevelsStartingAtRoot(
        );
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );

        /**
         * ordered search so do not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);
        self::assertEquals($maxLevels, $this->search->getMaxLevels());
    }

    /**
     * @return void
     * @covers \pvc\struct\treesearch\SearchBreadthFirst
     */
    public function testOtherStartNode(): void
    {
        $this->search->setStartNode($this->tree->getNode(1));
        $expectedResult
            = $this->fixture->makeOrderedBreadthFirstArrayStartingAtNodeid1(
        );
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );
        self::assertEquals($expectedResult, $actualResult);
    }
}
