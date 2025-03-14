<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\integration_tests\treesearch;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\tree\tree\Tree;
use pvc\struct\treesearch\err\SetMaxSearchLevelsException;
use pvc\struct\treesearch\SearchBreadthFirst;
use pvcTests\struct\integration_tests\tree\fixture\TestUtils;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

/**
 * Class SearchBreadthFirstTest
 * @template PayloadType of HasPayloadInterface
 */
class SearchBreadthFirstTest extends TestCase
{
    /**
     * @var Tree<PayloadType>
     */
    protected Tree $tree;

    /**
     * @var SearchBreadthFirst
     */
    protected SearchBreadthFirst $search;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    public function setUp(): void
    {
        $ordered = true;
        $testUtils = new TestUtils($ordered);
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->tree = $testUtils->testTreeSetup($this->fixture);
        $this->search = new SearchBreadthFirst();
    }

    /**
     * testGetFullTree
     * @covers \pvc\struct\treesearch\SearchBreadthFirst::rewind
     * @covers \pvc\struct\treesearch\SearchBreadthFirst::next
     * @covers \pvc\struct\treesearch\SearchBreadthFirst::getNextLevelOfNodes
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     */
    public function testGetFullTree(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayOfAllNodeIds();
        $actualResult = TestUtils::getNodeIdsFromNodeArray($this->search->getNodes());
        /**
         * ordered search so not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testMaxLevels
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

        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayThreeLevelsStartingAtRoot();
        $actualResult = TestUtils::getNodeIdsFromNodeArray($this->search->getNodes());

        /**
         * ordered search so do not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);
        self::assertEquals($maxLevels, $this->search->getMaxLevels());
    }
}
