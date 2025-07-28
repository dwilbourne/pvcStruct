<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\treesearch;

use pvc\interfaces\struct\treesearch\NodeSearchableInterface;
use pvc\interfaces\struct\treesearch\SearchInterface;
use pvc\struct\treesearch\err\SetMaxSearchLevelsException;
use pvc\struct\treesearch\err\StartNodeUnsetException;

/**
 * Class SearchAbstract
 * @template NodeType of NodeSearchableInterface
 *
 * @implements SearchInterface<NodeType>
 */
abstract class SearchAbstract implements SearchInterface
{
    /**
     * @var NodeType
     */
    protected mixed $startNode;

    /**
     * @var NodeType|null
     */
    protected mixed $currentNode = null;

    /**
     * @var int
     *
     * maximum depth to which we are allowed to traverse the tree.
     */
    private int $maxLevels = PHP_INT_MAX;

    /**
     * @var non-negative-int int
     */
    private int $currentLevel = 0;

    /**
     * key
     *
     * @return non-negative-int|null
     */
    public function key(): int|null
    {
        return $this->current()?->getNodeId();
    }

    /**
     * current
     *
     * @return NodeType|null
     */
    public function current(): mixed
    {
        return $this->currentNode;
    }

    /**
     * valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return !is_null($this->currentNode);
    }

    /**
     * rewind
     *
     * @throws StartNodeUnsetException
     */
    public function rewind(): void
    {
        $this->setCurrent($this->getStartNode());
        $this->currentLevel = 0;
    }

    /**
     * setCurrent
     *
     * @param  NodeType|null  $currentNode
     * nullable because you want to set the current node to null at the end of a search, after the last node has been
     * returned and have it initialized as null
     */
    protected function setCurrent(mixed $currentNode): void
    {
        $this->currentNode = $currentNode;
    }

    /**
     * getStartNode
     *
     * @return NodeType
     * startNode must be set before the class can do anything so throw an exception if it is not set
     * @throws StartNodeUnsetException
     */
    public function getStartNode(): mixed
    {
        if (!isset($this->startNode)) {
            throw new StartNodeUnsetException();
        }
        return $this->startNode;
    }

    /**
     * setStartNode
     *
     * @param  NodeType  $startNode
     */
    public function setStartNode($startNode): void
    {
        $this->startNode = $startNode;
    }

    /**
     * @return void
     */
    abstract public function next(): void;

    /**
     * @return array<NodeType>
     */
    public function getNodes(): array
    {
        $result = [];
        foreach ($this as $node) {
            $result[$node->getNodeId()] = $node;
        }
        return $result;
    }

    /**
     * @return bool
     *
     *  as an example, max levels of 2 means the first level (containing the start node) is at level 0 and the level
     *  below that is on level 1.  So if the current level goes to 1 then we are at the max-levels
     *  threshold.
     */
    protected function atMaxLevels(): bool
    {
        return ($this->getCurrentLevel() == $this->getMaxLevels() - 1);
    }

    /**
     * getCurrentLevel
     *
     * @return int<-1, max>
     *
     * it is conceivable someone could want to know what level of the nodes the search is currently on while
     * in the middle of iteration so keep this method public
     */
    public function getCurrentLevel(): int
    {
        return $this->currentLevel;
    }

    /**
     * @param  Direction  $direction
     *
     * @return void
     * we only want subclasses to be able to modify the current level of the search
     */
    protected function setCurrentLevel(Direction $direction): void
    {
        /**
         * feels backwards but moving down in the search increases the level.  Because Direction::DOWN is defined as
         * -1, we want to subtract it from, not add it to, the current level.
         *
         * Type checker cannot know that the logic in the searches does not permit a current level of
         * -1
         */
        $newLevel = $this->currentLevel - $direction->value;
        assert($newLevel >= 0);
        $this->currentLevel = $newLevel;
    }

    /**
     * getMaxLevels
     *
     * @return int
     */
    public function getMaxLevels(): int
    {
        return $this->maxLevels;
    }

    /**
     * setMaxLevels
     *
     * @param  int  $maxLevels
     *
     * @throws SetMaxSearchLevelsException
     *
     * it is easy to get confused about this, but startNode is at level 0, meaning that current level uses
     * zero-based counting.  BUT, max levels is one-based.  So if you set max levels = 3, you will get three levels
     * of nodes which are at levels 0, 1 and 2.
     */
    public function setMaxLevels(int $maxLevels): void
    {
        if ($maxLevels < 1) {
            throw new SetMaxSearchLevelsException($maxLevels);
        } else {
            $this->maxLevels = $maxLevels;
        }
    }

    protected function invalidate(): void
    {
        $this->currentNode = null;
        $this->currentLevel = 0;
    }
}
