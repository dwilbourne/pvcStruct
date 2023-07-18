<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\lists\ListOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidNodeValueException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\SetChildrenException;

/**
 * Class TreenodeOrdered
 *
 * This implementation keeps references to actual objects in the tree (e.g. the parent attribute is an object, not an
 * id which can be used to retrieve the object). An alternative approach would be to set the tree object reference
 * and then keep ids of the other things (parents & children).  When you need those actual objects, you
 * would use the tree to retrieve them.  My thought is that the latter approach would make the code in this class more
 * cumbersome. Getting a parent would be "this->tree->getNode($this->>parentid) in there in order to obtain the object
 * first before manipulating it.
 *
 * However, the approach of actually keeping references to objects in the attributes comes with some additional cost
 * when initializing the tree.  First, the tree itself needs to be hydrated with all the nodes.  At that point, the
 * nodes only contain the ids necessary to set the references to the actual objects.  Only after all the
 * nodes are in the tree can you then set the references in each node.
 *
 * Lastly, the index property of each node deserves some commentary.  This property initializes the node's position
 * relative to its siblings in the child list of its parent.  So if its parent has 5 children and this node is second
 * in that list, then this node's index is 1 (0 based ordination).  When the tree is being hydrated from a data
 * store, this property is populated and then the children of each parent are put into the child list in the
 * appropriate order.  If you add a node to the tree (via the add method), you also specify the index at which you
 * want the child to be inserted in the list of children.  It is also possible to change a child's index after it has
 * been put into the tree.  This class has a setIndex method which calls the changeIndex method on the list object
 * holding the children.  It is very important to understand that the property itself is only used when the tree is
 * hydrated.  In fact, to clarify that, the property is nulled out during the process of hydrating the tree.  The
 * reason is that it is possible to change a node's index relative to its siblings.  Doing so typically requires
 * changing the indices of other children in the list as well, which is why ListOrdered is used as the object to
 * handle this job.  So the getIndex method does NOT look at the value of the property.  It asks the childlist of the
 * parent in order to get an index.  Similarly, when dehyrating a node, the index is not gotten from the property, it
 * is gotten from the parent's list of children.
 *
 * @template NodeValueType
 * @extends TreenodeAbstract<TreenodeOrderedInterface, NodeValueType>
 * @implements TreenodeOrderedInterface<NodeValueType>
 */
class TreenodeOrdered extends TreenodeAbstract implements TreenodeOrderedInterface
{
    /**
     * reference to the containing tree
     * @var TreeOrderedInterface<NodeValueType>|null
     */
    protected ?TreeOrderedInterface $tree;

    /**
     * reference to parent node
     * @var TreenodeOrderedInterface<NodeValueType>|null
     */
    protected ?TreenodeOrderedInterface $parent;

    /**
     * list of the children of this node
     * @var ListOrderedInterface<TreenodeOrderedInterface<NodeValueType>>
     */
    protected ListOrderedInterface $children;

    /**
     * used to establish the position of this node relative to its siblings in the list of children that the parent
     * keeps.  It is not used after the tree is hydrated.
     * @var int
     */
    protected int $hydrationIndex;

    /**
     * use this method to initialize the children property with an empty list.
     *
     * The list must be empty because trees and nodes set up all their own pointers as the tree is hydrated.  The
     * most usual way to create an ordered node is via a factory / dependency injection.
     *
     * setChildren
     * @param ListOrderedInterface<TreenodeOrderedInterface<NodeValueType>> $list
     */
    public function setChildList(ListOrderedInterface $list) : void
    {
        if (!$list->isEmpty()) {
            throw new SetChildrenException();
        }
        $this->children = $list;
    }

    /**
     * sets the order in which this node is added to the parent's child list relative to its siblings.
     *
     * setHydrationIndex
     * @param int $index
     */
    public function setHydrationIndex(int $index) : void
    {
        $this->hydrationIndex = $index;
    }

    /**
     * used only to verify that the hydration index was set and if so, what it's value is.
     *
     * getHydrationIndex
     * @return int|null
     */
    public function getHydrationIndex() : ? int
    {
        return $this->hydrationIndex ?? null;
    }

    /**
     * @function dehydrate
     * @return array<mixed>
     */
    public function dehydrate(): array
    {
        $array = parent::dehydrate();
        $array['index'] = $this->getIndex();
        return $array;
    }

    /**
     * @function hydrate
     * @param array<mixed> $nodeData
     * @throws InvalidParentNodeException
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     */
    public function hydrate(array $nodeData): void
    {
        parent::hydrate($nodeData);
        /** @var int $index */
        $index = $nodeData['index'];
        $this->setHydrationIndex($index);
    }

    /**
     * @function setReferences
     * @param TreeOrderedInterface<NodeValueType> $tree
     * @throws NodeNotInTreeException
     */
    public function setReferences(TreeOrderedInterface $tree): void
    {
        /**
         * if the node is not in the tree already, then throw an exception. See the class documentation at the top of
         * this file for more details on why
         */
        if (is_null($tree->getNode($this->getNodeId()))) {
            throw new NodeNotInTreeException($tree->getTreeId(), $this->getNodeId());
        }
        $this->tree = $tree;

        if (!is_null($parentId = $this->getParentId())) {
            /** @var TreenodeOrderedInterface<NodeValueType> $parent */
            $parent = $this->tree->getNode($parentId);
            $this->parent = $parent;
            $parent->getChildren()->push($this);
        }
    }

    /**
     * returns the node's position relative to its siblings, 0 if it has no siblings and null if it is not in the tree.
     *
     * @function getIndex
     * @return int | null
     */
    public function getIndex(): ? int
    {
        /** if the tree object is not set, return null */
        if (is_null($this->getTree())) {
            return null;
        }

        $result = 0;

        /** if the node is the root then it cannot have siblings and its index is 0 */
        if (is_null($this->getParent())) {
            return $result;
        }

        /** iterate through the list to find the node - the key is the index */
        /** @var TreenodeOrderedInterface<NodeValueType>[] $siblings */
        $siblings = $this->getSiblings();
        foreach ($siblings as $index => $node) {
            if ($this === $node) {
                $result = $index;
            }
        }
        return $result;
    }

    /**
     * changes this node's position in the child list of the parent.
     *
     * This method throws an exception if the node is not part of the tree yet.
     * @function setIndex
     * @param int $index
     * @throws NodeNotInTreeException
     */
    public function setIndex(int $index): void
    {
        if (!isset($this->tree)) {
            throw new NodeNotInTreeException($this->getTreeId(), $this->getNodeId());
        }
        if (is_null($siblings = $this->getSiblings())) {
            /** cannot change index */
            return;
        }
            /**  phpstan wants getIndex to return type int, not int|null */
            $siblings->changeIndex($this->getIndex() ?? 0, $index);
    }


    /**
     * @function unsetReferences
     *
     * This method finishes removing a node from the tree and is called from the tree::deleteNode method.  It is
     * here as extra insurance that a node is not used after having been deleted from the tree's list of nodes.
     *
     * The deleteNode method accepts a parameter called deleteBranch which controls whether it is OK to delete an
     * entire branch of the tree.  In that case, the code does a depth-first traversal so that children are removed
     * before parents.  Because this class is designed to work only on a single node, this routine throws an
     * exception if this node has children when you try to unset its references.
     *
     */
    public function unsetReferences(): void
    {
        if (0 < count($this->getChildren())) {
            throw new DeleteInteriorNodeException($this->nodeid);
        }

        /** getIndex returns null if this node is not in a tree */
        if (!is_null($index = $this->getIndex())) {
            /**
             * be careful as you read the next block of code.  It is natural to want to say that the delete method
             * should take the nodeid of the thing being deleted.  But children is an ordered list and knows nothing
             * about nodeids. All it knows is the indices of its elements.
             *
             * also make sure getParent does not return null
             */
            if (!is_null($parent = $this->getParent())) {
                $parent->getChildren()->delete($index);
            }
        }

        unset($this->parent);
        unset($this->parentid);
        unset($this->tree);
        unset($this->treeid);
    }

    /**
     * @function getTree
     * @return TreeOrderedInterface<NodeValueType>|null
     */
    public function getTree(): ?TreeOrderedInterface
    {
        return $this->tree ?? null;
    }

    /**
     * @function getParent
     * @return TreenodeOrderedInterface<NodeValueType>|null
     */
    public function getParent(): ?TreenodeOrderedInterface
    {
        return $this->parent ?? null;
    }

    /**
     * @function getChild
     * @param int $nodeid
     * @return TreenodeOrderedInterface<NodeValueType>|null
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
     * @return ListOrderedInterface<TreenodeOrderedInterface<NodeValueType>>
     */
    public function getChildren(): ListOrderedInterface
    {
        return $this->children;
    }

    public function getChildrenArray(): array
    {
        return $this->children->getElements();
    }

    /**
     * @function getSiblings
     * @return ListOrderedInterface<TreenodeOrderedInterface<NodeValueType>>|null
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
     * @param TreenodeOrderedInterface<NodeValueType> $node
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
     * @param TreenodeOrderedInterface<NodeValueType> $node
     * @return bool
     */
    public function isAncestorOf(TreenodeOrderedInterface $node): bool
    {
        return $node->isDescendantOf($this);
    }
}
