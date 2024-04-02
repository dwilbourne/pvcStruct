<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\search\NodeFilterInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class SearchFilterDefault
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements NodeFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class NodeFilterDefault implements NodeFilterInterface
{

    /**
     * testNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     * @return bool
     */
    public function testNode(TreenodeAbstractInterface $node): bool
    {
        return true;
    }
}
