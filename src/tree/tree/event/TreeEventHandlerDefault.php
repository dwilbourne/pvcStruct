<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\tree\event;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class TreeEventHandlerDefault
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements TreeAbstractEventHandlerInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class TreeEventHandlerDefault implements TreeAbstractEventHandlerInterface
{

    /**
     * beforeDeleteNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     */
    public function beforeDeleteNode(TreenodeAbstractInterface $node): void
    {
    }

    /**
     * afterDeleteNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     */
    public function afterDeleteNode(TreenodeAbstractInterface $node): void
    {
    }

    /**
     * beforeAddNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     */
    public function beforeAddNode(TreenodeAbstractInterface $node): void
    {
    }

    /**
     * afterAddNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     */
    public function afterAddNode(TreenodeAbstractInterface $node): void
    {
    }
}
