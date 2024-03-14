<?php
/** @noinspection SpellCheckingInspection */

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
            AlreadySetNodeidException::class => 1001,
            AlreadySetRootException::class => 1002,
            BadSearchLevelsException::class => 1003,
            ChildCollectionException::class => 1004,
            CircularGraphException::class => 1005,
            DeleteInteriorNodeException::class => 1006,
            InvalidDepthFirstSearchOrderingException::class => 1007,
            InvalidNodeIdException::class => 1008,
            InvalidValueException::class => 1009,
            InvalidParentNodeException::class => 1010,
            InvalidTreeidException::class => 1011,
            NodeNotInTreeException::class => 1012,
            NoRootFoundException::class => 1013,
            RootCannotBeMovedException::class => 1014,
            SetTreeIdException::class => 1015,
            StartNodeUnsetException::class => 1016,
            TreeNotEmptyHydrationException::class => 1017,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            AlreadySetNodeidException::class => 'nodeid ${nodeid} already exists in the tree.',
            AlreadySetRootException::class => 'set root error - root of tree is already set.',
            BadSearchLevelsException::class => 'Max levels to search must be > 0, actual supplied = ${badLevels}.',
            ChildCollectionException::class => 'Child collection supplied to the constructor must be empty.',
            CircularGraphException::class => 'circular graph error: nodeid ${nodeid} cannot be its own ancestor.',
            DeleteInteriorNodeException::class => 'cannot delete nodeid ${nodeid} - must be a leaf.',
            InvalidDepthFirstSearchOrderingException::class => 'oredering payload must be the PREORDER or POSTORDER class consant.',
            InvalidNodeIdException::class => 'Invalid nodeid ${nodeid} - must be an integer >= 0.',
            InvalidValueException::class => 'Invalid node payload.',
            InvalidParentNodeException::class => 'Parentid ${parentid} does not exist in the current tree.',
            InvalidTreeidException::class => 'Invalid treeid ${treeid} - must be an integer >= 0',
            NodeNotInTreeException::class => 'treeid ${treeid} does not contain nodeid ${nodeid}.',
            NoRootFoundException::class => 'no root node found in tree node payload object array.',
            RootCannotBeMovedException::class => 'The root node cannot be moved to another place in the tree.',
            SetTreeIdException::class => 'cannot set the treeid on a tree which is not empty.',
            StartNodeUnsetException::class => 'start node must be set before serarching or resetting the search.',
            TreeNotEmptyHydrationException::class => 'cannot hydrate a non empty tree.',
        ];
    }
}
