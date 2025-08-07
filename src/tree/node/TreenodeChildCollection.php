<?php

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeChildCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\collection\CollectionOrderedByIndex;

/**
 * @template TreenodeType of TreenodeInterface
 * @extends CollectionOrderedByIndex<TreenodeType>
 * @implements TreenodeChildCollectionInterface<TreenodeType>
 */
class TreenodeChildCollection extends CollectionOrderedByIndex implements TreenodeChildCollectionInterface
{

}