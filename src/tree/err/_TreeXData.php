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
            AlreadySetNodeidException::class              => 1001,
            AlreadySetRootException::class                => 1002,
            ChildCollectionException::class               => 1004,
            CircularGraphException::class                 => 1005,
            DeleteInteriorNodeException::class            => 1006,
            InvalidNodeIdException::class                 => 1008,
            InvalidValueException::class                  => 1009,
            InvalidParentNodeIdException::class           => 1010,
            InvalidTreeidException::class                 => 1011,
            NodeNotInTreeException::class                 => 1012,
            NoRootFoundException::class                   => 1013,
            RootCannotBeMovedException::class             => 1014,
            SetTreeException::class                       => 1015,
            NodeNotEmptyHydrationException::class         => 1019,
            TreeNotInitializedException::class            => 1021,
            TreenodeFactoryNotInitializedException::class => 1022,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            AlreadySetNodeidException::class              => 'nodeid ${nodeid} already exists in the tree.',
            AlreadySetRootException::class                => 'set root error - root of tree is already set.',
            ChildCollectionException::class               => 'Child collection supplied to the constructor must be empty.',
            CircularGraphException::class                 => 'circular graph error: nodeid ${nodeid} cannot be its own ancestor.',
            DeleteInteriorNodeException::class            => 'cannot delete nodeid ${nodeid} - must be a leaf.',
            InvalidNodeIdException::class                 => 'Invalid nodeid ${nodeid} - must be an integer >= 0.',
            InvalidValueException::class                  => 'Invalid node payload.',
            InvalidParentNodeIdException::class           => 'Parentid ${parentid} does not exist in the current tree.',
            InvalidTreeidException::class                 => 'Invalid treeid ${treeid} - must be an integer >= 0',
            NodeNotInTreeException::class                 => 'treeid ${treeid} does not contain nodeid ${nodeid}.',
            NoRootFoundException::class                   => 'no root node found in tree node payload object array.',
            RootCannotBeMovedException::class             => 'The root node cannot be moved to another place in the tree.',
            SetTreeException::class                       => 'either the treeid of node ${nodeId} does not match the one in the tree or the tree reference has already been set.',
            NodeNotEmptyHydrationException::class         => 'cannot hydrate a non-empty node - nodeid ${nodeId} is already set.',
            TreeNotInitializedException::class            => 'cannot use Tree until it has been initialized.',
            TreenodeFactoryNotInitializedException::class => 'cannot use TreeNodeFactory until it has been initialized.',
        ];
    }
}
