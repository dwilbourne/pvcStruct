<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\treesearch;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\treesearch\err\SetMaxSearchLevelsException;
use pvc\struct\treesearch\SearchDepthFirstPostorder;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

class SearchDepthFirstPostorderTest extends TestCase
{
    /**
     * @var TreeInterface
     */
    protected TreeInterface $tree;

    /**
     * @var SearchDepthFirstPostorder
     */
    protected SearchDepthFirstPostorder $search;

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
        $this->search = new SearchDepthFirstPostorder();
    }

    /**
     * testIteratorPostOrder
     *
     * @covers \pvc\struct\treesearch\SearchDepthFirstPostorder
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     */
    public function testIteratorPostOrder(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult
            = $this->fixture->makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds(
        );
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testMaxLevelsPostOrder
     *
     * @throws SetMaxSearchLevelsException
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     * @covers \pvc\struct\treesearch\SearchDepthFirstPostorder
     */
    public function testMaxLevelsPostOrder(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult
            = $this->fixture->makePostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(
        );
        $this->search->setMaxLevels(3);
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );
        self::assertEquals($expectedResult, $actualResult);
    }
}
