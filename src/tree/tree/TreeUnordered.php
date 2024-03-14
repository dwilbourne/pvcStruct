<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;

/**
 * Class TreeUnordered
 *
 * @template PayloadType of HasPayloadInterface
 * @phpcs:ignore
 * @extends TreeAbstract<PayloadType, TreenodeUnorderedInterface, TreeUnorderedInterface, TreenodeValueObjectUnorderedInterface, CollectionUnorderedInterface>
 * @implements TreeUnorderedInterface<PayloadType>
 */
class TreeUnordered extends TreeAbstract implements TreeUnorderedInterface
{
    /**
     * sortChildValueObjects
     * @param array<TreenodeValueObjectUnorderedInterface<PayloadType>> $childValueObjects
     * @return bool
     */
    protected function sortChildValueObjects(array &$childValueObjects): bool
    {
        return true;
    }
}
