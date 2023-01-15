<?php

/** @noinspection PhpCSValidationInspection */

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\err;

use pvc\err\ExceptionFactoryTrait;

class _ExceptionFactory
{
    use ExceptionFactoryTrait;

    public const LIBRARY_NAME = "pvcTree";

    public const CODES = [
        AddChildException::class => 1001,
        AlreadySetNodeidException::class => 1002,
        AlreadySetParentException::class => 1003,
        AlreadySetRootException::class => 1004,
	    BadTreesearchLevelsException::class => 1005,
        CircularGraphException::class => 1005,
        DeleteChildException::class => 1006,
        DeleteInteriorNodeException::class => 1007,
	    InvalidNodeArrayException::class => 1008,
	    InvalidNodeException::class => 1009,
        InvalidNodeIdException::class => 1010,
        InvalidNodeValueException::class => 1011,
        InvalidParentNodeException::class => 1012,
        InvalidTreeidException::class => 1013,
	    NodeHasInvalidTreeidException::class => 1013,
	    NodeIdAndParentIdCannotBeTheSameException::class => 1013,
        NodeNotInTreeException::class => 1014,
	    RootCountForTreeException::class => 1015,
	    SetChildrenException::class => 1016,
	    SetNodesException::class => 1016,
	    SetTreeIdException::class => 1016,
	    UnsetValueException::class => 1017,
    ];

    public const MESSAGES = [
        AddChildException::class => 'addChild error: unable to add child.',
        AlreadySetNodeidException::class => 'nodeid %s already exists in the tree.',
        AlreadySetParentException::class => 'addChild error: parent of node is already set.',
        AlreadySetRootException::class => 'set root error - root of tree is already set.',
	    BadTreesearchLevelsException::class => 'Max levels to search must be > 0, actual supplied = %s.',
        CircularGraphException::class => 'circular graph error: this nodeid %s cannot be its own ancestor.',
        DeleteChildException::class => 'deleteChild error:  Nodeid %s is not a child of nodeid = %s.',
        DeleteInteriorNodeException::class => 'cannot delete nodeid %s - must be a leaf.',
	    InvalidNodeArrayException::class => 'node array has key %s which does not match nodeid %s in the same element.',
	    InvalidNodeException::class => 'Node does not implement TreenodeInterface.',
        InvalidNodeIdException::class => 'Invalid nodeid %s - must be an integer >= 0.',
        InvalidNodeValueException::class => 'Invalid node value.',
        InvalidParentNodeException::class => 'Parentid %s does not exist in the current tree.',
	    InvalidTreeidException::class => 'Invalid treeid %s - must be an integer >= 0',
        NodeHasInvalidTreeidException::class => 'nodeid %s has treeid %s and that does not match containing treeid %s.',
	    NodeIdAndParentIdCannotBeTheSameException::class => 'nodeid and parentid cannot be the same: both are %s',
        NodeNotInTreeException::class => 'treeid %s does not contain nodeid = %s.',
	    RootCountForTreeException::class => 'counted %s number of roots supplied to tree - should be 1.',
	    SetChildrenException::class => 'child list must be empty in order to set it.',
	    SetNodesException::class => 'Cannot call setNodes on a tree which already has nodes in it.',
	    SetTreeIdException::class => 'cannot set the treeid on a tree which is not empty.',
	    UnsetValueException::class => "value of nodeid %s is not set",
    ];
}
