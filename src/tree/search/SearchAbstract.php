<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\search\NodeInterface;
use pvc\interfaces\struct\tree\search\SearchInterface;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\StartNodeUnsetException;

/**
 * Class SearchStrategyAbstract
 * @template NodeType of NodeInterface
 * @implements SearchInterface<NodeType>
 */
abstract class SearchAbstract implements SearchInterface
{
    /**
     * @var ?callable
     */
    protected $nodeFilter;

    /**
     * @var NodeType
     */
    protected mixed $startNode = null;

    /**
     * @var NodeType
     */
    protected mixed $currentNode = null;

    /**
     * @var int
     *
     * maximum depth to which we are allowed to traverse the tree.
     */
    protected int $maxLevels = PHP_INT_MAX;

    /**
     * @var non-negative-int
     * the start node is on level 0
     */
    protected int $currentLevel = 0;

    /**
     * @var bool
     * flag indicating whether we can go to the next node or not.  Initialize to false.  It becomes true after
     * the rewind method is called.
     */
    protected bool $valid = false;


    /**
     * getNodeFilter
     * @return callable
     */
    public function getNodeFilter(): callable
    {
        /** @phpcs:ignore */
        return $this->nodeFilter ?? function ($node) {
            return true;
        };
    }

    /**
     * setNodeFilter
     * @param callable $nodeFilter
     */
    public function setNodeFilter(callable $nodeFilter): void
    {
        $this->nodeFilter = $nodeFilter;
    }

    /**
     * getStartNode
     * @return NodeType
     * startNode must be set before the class can do anything so throw an exception if it is not set
     */
    public function getStartNode(): mixed
    {
        if (!$this->startNode) {
            throw new StartNodeUnsetException();
        }
        return $this->startNode;
    }

    /**
     * setStartNode
     * @param NodeType $startNode
     */
    public function setStartNode($startNode): void
    {
        $this->startNode = $startNode;
    }

    /**
     * current
     * @return NodeType|null
     */
    public function current(): mixed
    {
        return $this->currentNode ?? null;
    }

    /**
     * setCurrent
     * @param NodeType|null $currentNode
     * nullable because you want to set the current node to null at the end of a search, after the last node has been
     * returned
     */
    public function setCurrent(mixed $currentNode): void
    {
        if ($currentNode) {
            $this->currentNode = $currentNode;
        } else {
            unset($this->currentNode);
            $this->valid = false;
        }
    }

    /**
     * getMaxLevels
     * @return int
     */
    public function getMaxLevels(): int
    {
        return $this->maxLevels;
    }

    /**
     * setMaxLevels
     * @param int $maxLevels
     * @throws BadSearchLevelsException
     */
    public function setMaxLevels(int $maxLevels): void
    {
        if ($maxLevels < 1) {
            throw new BadSearchLevelsException($maxLevels);
        } else {
            $this->maxLevels = $maxLevels;
        }
    }

    /**
     * getCurrentLevel
     * @return non-negative-int
     */
    public function getCurrentLevel(): int
    {
        return $this->currentLevel;
    }

    /**
     * key
     * @return non-negative-int
     */
    public function key(): int
    {
        return $this->currentNode->getNodeId();
    }


    /**
     * valid
     * @return bool
     */
    public function valid(): bool
    {
        return $this->valid;
    }

    /**
     * rewind
     * @throws StartNodeUnsetException
     */
    public function rewind(): void
    {
        $this->setCurrent($this->getStartNode());
        $this->valid = true;
        $this->currentLevel = 0;
    }

    abstract public function next(): void;

    /**
     * getNodeIds
     * @return array<NodeInterface>
     */
    public function getNodes(): array
    {
        $result = [];
        foreach ($this as $node) {
            $result[$node->getNodeId()] = $node;
        }
        return $result;
    }
}
