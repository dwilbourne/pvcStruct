<?php /** @noinspection SpellCheckingInspection */

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare(strict_types=1);

namespace pvc\struct\tree\err;

use pvc\err\XDataAbstract;

class _TreeXData extends XDataAbstract
{

    public function getLocalXCodes(): array
    {
        return [
            AddChildException::class => 1001,
            AlreadySetNodeidException::class => 1002,
            AlreadySetParentException::class => 1003,
            AlreadySetRootException::class => 1004,
            BadSearchLevelsException::class => 1005,
            CircularGraphException::class => 1006,
            DeleteChildException::class => 1007,
            DeleteInteriorNodeException::class => 1008,
            InvalidNodeArrayException::class => 1009,
            InvalidNodeException::class => 10010,
            InvalidNodeIdException::class => 1011,
            InvalidNodeValueException::class => 1012,
            InvalidParentNodeException::class => 1013,
            InvalidTreeidException::class => 1014,
            NodeHasInvalidTreeidException::class => 1015,
            NodeIdAndParentIdCannotBeTheSameException::class => 1016,
            NodeNotInTreeException::class => 1017,
            RootCountForTreeException::class => 1018,
            SetChildrenException::class => 1019,
            SetNodesException::class => 1020,
            SetTreeIdException::class => 1021,
            UnsetNodeValueException::class => 1022,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            AddChildException::class => 'addChild error: unable to add child.',
            AlreadySetNodeidException::class => 'nodeid ${nodeid} already exists in the tree.',
            AlreadySetParentException::class => 'addChild error: parent of node is already set.',
            AlreadySetRootException::class => 'set root error - root of tree is already set.',
            BadSearchLevelsException::class => 'Max levels to search must be > 0, actual supplied = ${badLevels}.',
            CircularGraphException::class => 'circular graph error: nodeid ${nodeid} cannot be its own ancestor.',
            DeleteChildException::class => 'deleteChild error:  Nodeid ${proposedChildNodeid} is not a child of nodeid = ${proposedParentNodeid}.',
            DeleteInteriorNodeException::class => 'cannot delete nodeid ${nodeid} - must be a leaf.',
            InvalidNodeArrayException::class => 'node array has key ${keyid} which does not match nodeid ${nodeid} in the same element.',
            InvalidNodeException::class => 'Node does not implement TreenodeInterface.',
            InvalidNodeIdException::class => 'Invalid nodeid ${nodeid} - must be an integer >= 0.',
            InvalidNodeValueException::class => 'Invalid node value.',
            InvalidParentNodeException::class => 'Parentid ${parentid} does not exist in the current tree.',
            InvalidTreeidException::class => 'Invalid treeid ${treeid} - must be an integer >= 0',
            NodeHasInvalidTreeidException::class => 'nodeid ${nodeid} has treeid ${treeidOfNode} and that does not match containing treeid ${treeidOfTree}.',
            NodeIdAndParentIdCannotBeTheSameException::class => 'nodeid and parentid cannot be the same: both are ${nodeid}',
            NodeNotInTreeException::class => 'treeid ${treeid} does not contain nodeid ${nodeid}.',
            RootCountForTreeException::class => 'counted ${rootCount} number of roots supplied to tree - should be 1.',
            SetChildrenException::class => 'child list must be empty in order to set it.',
            SetNodesException::class => 'Cannot call setNodes on a tree which already has nodes in it.',
            SetTreeIdException::class => 'cannot set the treeid on a tree which is not empty.',
            UnsetNodeValueException::class => 'value of nodeid ${nodeid} is not set',
        ];
    }
}
