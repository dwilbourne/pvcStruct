<?php

declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeInterface;

/**
 * class Treenode
 * @template NodeValueType
 * @extends TreenodeAbstract<TreenodeInterface, NodeValueType>
 * @implements TreenodeInterface<NodeValueType>
 */
class Treenode extends TreenodeAbstract implements TreenodeInterface
{
}
