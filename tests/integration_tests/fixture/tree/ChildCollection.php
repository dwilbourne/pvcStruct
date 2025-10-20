<?php

namespace pvcTests\struct\integration_tests\fixture\tree;

use pvc\interfaces\struct\tree\node\TreenodeCollectionInterface;
use pvc\struct\collection\IndexedCollection;

/**
 * @extends IndexedCollection<non-negative-int, Treenode>
 * @implements TreenodeCollectionInterface<non-negative-int, Treenode>
 */
class ChildCollection extends IndexedCollection implements TreenodeCollectionInterface
{

}