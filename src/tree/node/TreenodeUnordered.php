<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\dto\DtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\collection\Collection;
use pvc\struct\payload\PayloadTrait;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidValueException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\node\Treenode as Treenode;
use pvc\struct\tree\tree\Tree;
use pvc\struct\treesearch\VisitationTrait;

/**
 *
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
 * @template PayloadType
 * @extends Treenode<PayloadType, TreenodeUnordered, Collection>
 */
class TreenodeUnordered extends Treenode
{
}
