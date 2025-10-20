<?php

namespace pvcTests\struct\integration_tests\fixture\tree;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\tree\Treenode as GenericTreenode;

/**
 * @extends GenericTreenode<non-negative-int, Tree, Treenode, TreenodeCollection>
 * @implements TreenodeInterface<non-negative-int, Tree, Treenode, TreenodeCollection>
 */
class Treenode extends GenericTreenode implements TreenodeInterface
{

}