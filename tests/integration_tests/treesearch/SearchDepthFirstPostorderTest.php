<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\treesearch;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\tree\Tree;
use pvc\struct\treesearch\err\InvalidDepthFirstSearchOrderingException;
use pvc\struct\treesearch\err\SetMaxSearchLevelsException;
use pvc\struct\treesearch\NodeMap;
use pvc\struct\treesearch\SearchDepthFirstPostorder;
use pvcTests\struct\integration_tests\tree\fixture\TestUtils;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class SearchDepthFirstPostorderTest extends TestCase
{
    /**
     * @var SearchDepthFirstPostorder
     */
    protected SearchDepthFirstPostorder $search;

    /**
     * @var Tree
     */
    protected Tree $tree;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    public function setUp(): void
    {
        $testUtils = new TestUtils();
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->tree = $testUtils->testTreeSetup($this->fixture);
        $this->search = new SearchDepthFirstPostorder(new NodeMap());
    }

    /**
     * testIteratorPostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\treesearch\SearchDepthFirstPostorder
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     */
    public function testIteratorPostOrder(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds();
        $actualResult = TestUtils::getNodeIdsFromNodeArray($this->search->getNodes());
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testMaxLevelsPostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @throws SetMaxSearchLevelsException
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     * @covers \pvc\struct\treesearch\SearchDepthFirstPostorder
     */
    public function testMaxLevelsPostOrder(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makePostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot();
        $this->search->setMaxLevels(3);
        $actualResult = TestUtils::getNodeIdsFromNodeArray($this->search->getNodes());
        self::assertEquals($expectedResult, $actualResult);
    }
}
