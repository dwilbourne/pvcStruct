<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\treesearch;


use pvc\interfaces\struct\treesearch\NodeMapInterface;
use pvc\interfaces\struct\treesearch\NodeVisitableInterface;

/**
 * Class NodeMap
 *
 * This is generated as a by-product of a depth first search and as a result of iteration.  It is initialized in the
 * rewind method of the search. The start node of the search is at depth = 0
 *
 * @phpstan-import-type NodeMapRow from NodeMapInterface
 * @template NodeType of NodeVisitableInterface
 * @implements NodeMapInterface<NodeType>
 */
class NodeMap implements NodeMapInterface
{
    /**
     * @var array<NodeMapRow>
     */
    protected array $nodes = [];

    /**
     * initialize by putting the start node of the search into the map
     */
    public function initialize(NodeVisitableInterface $node): void
    {
        $this->nodes = [];
        $this->setNode($node, null);
    }

    /**
     * setNode
     * @param NodeType $node
     * @param non-negative-int|null $parentId
     */
    public function setNode(NodeVisitableInterface $node, ?int $parentId): void
    {
        $this->nodes[$node->getNodeId()] = ['parentId' => $parentId, 'node' => $node];
    }

    /**
     * getParent
     * @param int $nodeId
     * @return NodeType|null
     */
    public function getParent(int $nodeId): ?NodeVisitableInterface
    {
        return $this->getNode($this->getParentId($nodeId));
    }

    /**
     * getNode
     * @param ?int $nodeId
     * @return NodeType|null
     */
    public function getNode(?int $nodeId): ?NodeVisitableInterface
    {
        $array = $this->nodes[$nodeId] ?? [];
        return $array['node'] ?? null;
    }

    /**
     * getParentId
     * @param int $nodeId
     * @return non-negative-int|null
     */
    public function getParentId(int $nodeId): ?int
    {
        $array = $this->nodes[$nodeId] ?? [];
        return $array['parentId'] ?? null;
    }

    /**
     * @return array<NodeMapRow>
     */
    public function getNodeMapArray(): array
    {
        /** @phpstan-ignore-next-line  */
        return $this->nodes;
    }
}
