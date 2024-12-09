<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\search\NodeMapInterface;
use pvc\interfaces\struct\tree\search\NodeVisitableInterface;

/**
 * Class NodeMap
 * This is generated as a by-product of a search and as a result of iteration.  It is initialized in the rewind
 * method of the search search. The start node of the search is at depth = 0
 */
class NodeMap implements NodeMapInterface
{
    /**
     * @var array<non-negative-int, array{parentId:non-negative-int|null, node:NodeVisitableInterface}>
     */
    protected ?array $nodes;

    /**
     * initialize
     */
    public function initialize(): void
    {
        $this->nodes = null;
    }

    /**
     * isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->nodes);
    }

    /**
     * getNode
     * @param int $nodeId
     * @return NodeVisitableInterface|null
     */
    public function getNode(int $nodeId): ?NodeVisitableInterface
    {
        $array = $this->nodes[$nodeId] ?? null;
        /** @var NodeVisitableInterface|null $node */
        $node = $array ? $array['node'] : null;
        return $node;
    }

    /**
     * setNode
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @param NodeVisitableInterface $node
     */
    public function setNode(int $nodeId, ?int $parentId, NodeVisitableInterface $node): void
    {
        $this->nodes[$nodeId] = ['parentId' => $parentId, 'node' => $node];
    }

    /**
     * getParentId
     * @param int $nodeId
     * @return non-negative-int|null
     */
    public function getParentId(int $nodeId): ?int
    {
        $array = $this->nodes[$nodeId] ?? null;
        /** @var non-negative-int|null $node */
        $node = $array ? $array['parentId'] : null;
        return $node;
    }

    public function getParent(int $nodeId): ?NodeVisitableInterface
    {
        $parentId = $this->getParentId($nodeId);
        return !is_null($parentId) ? $this->getNode($parentId) : null;
    }
}
