<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\search\NodeMapInterface;
use pvc\struct\tree\search\SearchDepthFirst;

class SearchDepthFirstTest extends TestCase
{
    protected NodeMapInterface $nodeMap;

    protected SearchDepthFirst $search;

    public function setUp(): void
    {
        $this->nodeMap = $this->createMock(NodeMapInterface::class);
        $this->search = $this->getMockBuilder(SearchDepthFirst::class)
                             ->setConstructorArgs([$this->nodeMap])
                             ->getMockForAbstractClass();
    }

    /**
     * testSetGetNodeMap
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::setNodeMap
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodeMap
     */
    public function testSetGetNodeMap(): void
    {
        self::assertEquals($this->nodeMap, $this->search->getNodeMap());
    }
}
