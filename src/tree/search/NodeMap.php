<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\search\NodeMapInterface;
use pvc\interfaces\struct\tree\search\NodeSearchableInterface;

/**
 * Class NodeMap
 * This is generated as a by-product of a search and as a result of iteration.  It is initialized in the rewind
 * method of the search. The start node of the search is at depth = 0
 * @template NodeType of NodeSearchableInterface
 */
class NodeMap implements NodeMapInterface
{
    /**
     * @var array<non-negative-int, array{parentId:non-negative-int|null, node:NodeType}>
     */
    protected array $nodes = [];

    /**
     * initialize
     */
    public function initialize(): void
    {
        $this->nodes = [];
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
     * @return NodeType|null
     */
    public function getNode(int $nodeId): ?NodeSearchableInterface
    {
        $array = $this->nodes[$nodeId] ?? [];
        /** @var NodeType|null $node */
        return $array['node'] ?? null;
    }

    /**
     * setNode
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @param NodeType $node
     */
    public function setNode(int $nodeId, ?int $parentId, NodeSearchableInterface $node): void
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
        $array = $this->nodes[$nodeId] ?? [];
        /** @var non-negative-int|null $node */
        return $array['parentId'] ?? null;
    }

    /**
     * getParent
     * @param int $nodeId
     * @return NodeType|null
     */
    public function getParent(int $nodeId): ?NodeSearchableInterface
    {
        $parentId = $this->getParentId($nodeId);
        return !is_null($parentId) ? $this->getNode($parentId) : null;
    }

    public function getNodeMapAsArray(): array
    {
        return $this->nodes;
    }
}
