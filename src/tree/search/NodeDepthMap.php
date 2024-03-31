<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use ArrayIterator;
use Iterator;
use pvc\interfaces\struct\tree\search\NodeDepthMapInterface;

/**
 * Class NodeDepthMap
 */
class NodeDepthMap implements NodeDepthMapInterface
{
    /**
     * @var array<non-negative-int, non-negative-int>
     *
     * key is the nodeid, value is the depth in the tree at which the node appears.  This is generated as a by-product
     * of a search and as a result of iteration.  It is initialized in the rewind method of the search strategy. The
     * start node of the search is at depth = 0
     */
    protected array $nodeDepths;

    /**
     * initialize
     */
    public function initialize(): void
    {
        $this->nodeDepths = [];
    }

    /**
     * isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->nodeDepths);
    }

    /**
     * getNodeDepth
     * @param int $nodeId
     * @return int|null
     */
    public function getNodeDepth(int $nodeId): ?int
    {
        return $this->nodeDepths[$nodeId] ?? null;
    }

    /**
     * setNodeDepth
     * @param non-negative-int $nodeId
     * @param non-negative-int $depth
     */
    public function setNodeDepth(int $nodeId, int $depth): void
    {
        $this->nodeDepths[$nodeId] = $depth;
    }

    /**
     * getIterator
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->nodeDepths);
    }
}
