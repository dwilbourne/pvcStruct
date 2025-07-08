<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\IndexedElementInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidValueException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeIdException;

/**
 *  The nodeid property is immutable - the only way to set the nodeid is at hydration.  The same applies to the tree property,
 *  which is set at construction time.
 *
 *  This means that there are no setters for these properties.  Together, these two design points ensure that nodes
 *  cannot be hydrated except in the context of belonging to a tree.  That in turn makes it a bit easier to enforce the
 *  design point that all nodeids in a tree must be unique.
 *
 *  The same is almost true for the parent property, but the difference is that the nodes are allowed to move around
 *  within the same tree, e.g. you can change a node's parent as long as the new parent is in the same tree. It is
 *  important to know that not only does a node keep a reference to its parent, but it also keeps a list of its
 *  children.  So the setParent method is responsible not only for setting the parent property, but it also takes
 *  the parent and adds a node to its child list.
 *
 * @extends Treenode<TreenodeOrdered, CollectionOrdered>
 * @phpstan-import-type TreenodeDtoShape from TreenodeInterface
 */
class TreenodeOrdered extends Treenode implements IndexedElementInterface
{
    /**
     * @var non-negative-int
     */
    protected int $index;

    /**
     * @param TreenodeDtoShape $dto
     * @return void
     * @throws AlreadySetNodeidException
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeIdException
     * @throws InvalidValueException
     */
    public function hydrate($dto): void
    {
        assert(isset($dto->index));
        $this->setIndex($dto->index);
        parent::hydrate($dto);
    }

    /**
     * @function getIndex returns the ordinal position of this node in its list of siblings
     * @return non-negative-int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * sets this node's index property.  This method should never be called
     * by any other class than the collection which contains the node.  The collection bears the responsibility
     * of rationalizing/ordering the indices within the collection.  Nodes are unaware of their siblings in
     * a direct way - they need to ask the parent for the sibling collection
     *
     * @function setIndex
     * @param non-negative-int $index
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }
}
