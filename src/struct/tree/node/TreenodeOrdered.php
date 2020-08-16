<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\node;

use pvc\struct\lists\ListOrderedInterface;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\iface\node\TreenodeOrderedInterface;
use pvc\struct\tree\iface\tree\TreeOrderedInterface;
use pvc\struct\tree\node\err\InvalidNodeIdException;
use pvc\struct\tree\node\err\InvalidNodeIndexException;
use pvc\validator\numeric\ValidatorIntegerNonNegative;

/**
 * Class TreenodeOrdered
 */
class TreenodeOrdered implements TreenodeOrderedInterface
{
    use TreenodeTrait;

    /**
     * reference to the containing tree
     * @var TreeOrderedInterface|null
     */
    protected ?TreeOrderedInterface $tree;

    /**
     * reference to parent node
     * @var TreenodeOrderedInterface|null
     */
    protected ?TreenodeOrderedInterface $parent;

    /**
     * list of the children of this node
     * @var ListOrderedInterface
     */
    protected ListOrderedInterface $children;

    /**
     * property used for hydrating and dehydrating nodes.
     * @var int|null
     */
    protected ?int $hydrationIndex;

    /**
     * TreenodeOrdered constructor.
     * @param int $nodeid
     * @param ListOrderedInterface $list
     * @throws InvalidNodeIdException
     */
    public function __construct(int $nodeid, ListOrderedInterface $list)
    {
        $this->setNodeId($nodeid);
        $this->children = $list;
    }

    /**
     * this setter is only called from tree dehydration (TreeOrdered->dehydrate()) and is used
     * to temporarily store the index before it is pushed into an array when the node dehydrates
     * (the array is typically used by a dao for persistence).
     *
     * @function setHydrationIndex
     * @param int $index
     */
    public function setHydrationIndex(int $index): void
    {
        $this->hydrationIndex = $index;
    }

    /**
     * this getter is only called from tree hydration (Treeordered->hydrate()) and is used to
     * temporarily store the index before being inserted into the tree structure (used during
     * the loading of the tree from a data store).
     *
     * @function getHydrationIndex
     * @return int|null
     */
    public function getHydrationIndex(): ?int
    {
        return $this->hydrationIndex ?? null;
    }

    /**
     * @function dehydrate
     * @return array
     */
    public function dehydrate(): array
    {
        return [
            'nodeid' => $this->getNodeId(),
            'parentid' => $this->getParentId(),
            'treeid' => $this->getTreeId(),
            'value' => $this->getValue(),
            'index' => $this->getHydrationIndex()
        ];
    }

    /**
     * @function hydrate
     * @param array $row
     * @throws \pvc\struct\tree\err\InvalidParentNodeException
     * @throws err\InvalidNodeIdException
     * @throws err\InvalidNodeValueException
     */
    public function hydrate(array $row): void
    {
        // nodeid set in constructor
        // $this->setNodeId($row['nodeid']);
        $this->setParentId($row['parentid']);
        $this->setTreeId($row['treeid']);
        $this->setValue($row['value']);
        $this->setHydrationIndex($row['index']);
    }

    /**
     * this getter is only used after the tree has been hydrated
     * @function getIndex
     * @return int
     */
    public function getIndex(): int
    {
        if (!isset($this->tree)) {
            throw new NodeNotInTreeException($this->getNodeId());
        }
        if (is_null($siblings = $this->getSiblings())) {
            return 0;
        }
        /* phpstan does not see that $siblings cannot be null at this point */
        /* @phpstan-ignore-next-line */
        foreach ($siblings as $index => $node) {
            if ($this === $node) {
                $result = $index;
            }
        }
        // phpstan complains that result might not be set so we need a default (bogus) value
        return $result ?? -1;
    }

    /**
     * this setter is only used after the tree has been hydrated when you want to change the index
     * of an existing element.
     * @function setIndex
     * @param int $n
     * @throws NodeNotInTreeException
     */
    public function setIndex(int $n): void
    {
        if (!isset($this->tree)) {
            throw new NodeNotInTreeException($this->getNodeId());
        }
        if (is_null($siblings = $this->getSiblings())) {
            // cannot change index
            return;
        }
        /* php cannot see that $siublings cannot be null at this point */
        /** @phpstan-ignore-next-line */
        $siblings->changeIndex($this->getIndex(), $n);
    }

    /**
     * @function setReferences
     * @param TreeOrderedInterface $tree
     * @throws NodeNotInTreeException
     */
    public function setReferences(TreeOrderedInterface $tree): void
    {
        if (!$tree->getNode($this->nodeid)) {
            throw new NodeNotInTreeException($this->nodeid);
        }
        $this->tree = $tree;
        if (!is_null($this->getParentId())) {
            $parent = $tree->getNode($this->getParentId());
            /* phpstan does not see that parent cannot be null at this point */
            /* @phpstan-ignore-next-line */
            $parent->getChildren()->push($this);
            $this->parent = $parent;
        }
    }

    /**
     * @function unsetReferences
     */
    public function unsetReferences(): void
    {
        if (isset($this->parent)) {
            /* phpstan does not see that parent cannot be null at this point */
            /* @phpstan-ignore-next-line */
            $this->getParent()->getChildren()->delete($this->getIndex());
        }
        unset($this->parent);
        unset($this->parentid);
        unset($this->tree);
        unset($this->treeid);
        unset($this->value);
        unset($this->children);
        // do not unset nodeid - remains immutable
        // unset($this->nodeid);
    }

    /**
     * @function hasReferencesSet
     * @return bool
     */
    public function hasReferencesSet(): bool
    {
        return isset($this->tree);
    }

    /**
     * @function getTree
     * @return TreeOrderedInterface|null
     */
    public function getTree(): ?TreeOrderedInterface
    {
        return $this->tree ?? null;
    }

    /**
     * @function getParent
     * @return TreenodeOrderedInterface|null
     */
    public function getParent(): ?TreenodeOrderedInterface
    {
        return $this->parent ?? null;
    }

    /**
     * @function getChild
     * @param int $nodeid
     * @return TreenodeOrderedInterface|null
     */
    public function getChild(int $nodeid): ?TreenodeOrderedInterface
    {
        foreach ($this->children as $child) {
            if ($nodeid == $child->getNodeId()) {
                return $child;
            }
        }
        return null;
    }

    /**
     * @function getChildren
     * @return ListOrderedInterface
     */
    public function getChildren(): ListOrderedInterface
    {
        return $this->children;
    }

    public function getChildrenArray() : array
    {
        return $this->children->getElements();
    }

    /**
     * @function getSiblings
     * @return ListOrderedInterface|null
     */
    public function getSiblings(): ?ListOrderedInterface
    {
        return (isset($this->parent) ? $this->parent->getChildren() : null);
    }

    /**
     * @function isLeaf
     * @return bool
     */
    public function isLeaf(): bool
    {
        return (0 == count($this->children));
    }

    /**
     * @function isInteriorNode
     * @return bool
     */
    public function isInteriorNode(): bool
    {
        return (0 < count($this->children));
    }

    /**
     * @function isRoot
     * @return bool
     */
    public function isRoot(): bool
    {
        if (!isset($this->tree)) {
            return false;
        } else {
            return ($this === $this->tree->getRoot());
        }
    }

    /**
     * @function isDescendantOf
     * @param TreenodeOrderedInterface $node
     * @return bool
     */
    public function isDescendantOf(TreenodeOrderedInterface $node): bool
    {
        if ($this->getParent() === $node) {
            return true;
        }
        if (is_null($this->getParent())) {
            return false;
        } else {
            return $this->getParent()->isDescendantOf($node);
        }
    }

    /**
     * @function isAncestorOf
     * @param TreenodeOrderedInterface $node
     * @return bool
     */
    public function isAncestorOf(TreenodeOrderedInterface $node): bool
    {
        return $node->isDescendantOf($this);
    }
}
