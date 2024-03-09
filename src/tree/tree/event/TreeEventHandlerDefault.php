<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 * skipping the phpCS inspection allows us to keep the opening and closing braces of the (empty) methods on the same
 * line so that phpunit counts them in the coverage data
 */

declare(strict_types=1);

namespace pvc\struct\tree\tree\event;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class TreeEventHandlerDefault
 * @template ValueType
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @implements TreeAbstractEventHandlerInterface<ValueType, NodeType, TreeType, CollectionType>
 */
class TreeEventHandlerDefault implements TreeAbstractEventHandlerInterface
{

    /**
     * beforeDeleteNode
     * @param TreenodeAbstractInterface<ValueType, NodeType, TreeType, CollectionType> $node
     */
    public function beforeDeleteNode(TreenodeAbstractInterface $node): void
    {
    }

    /**
     * afterDeleteNode
     * @param TreenodeAbstractInterface<ValueType, NodeType, TreeType, CollectionType> $node
     */
    public function afterDeleteNode(TreenodeAbstractInterface $node): void
    {
    }

    /**
     * beforeAddNode
     * @param TreenodeAbstractInterface<ValueType, NodeType, TreeType, CollectionType> $node
     */
    public function beforeAddNode(TreenodeAbstractInterface $node): void
    {
    }

    /**
     * afterAddNode
     * @param TreenodeAbstractInterface<ValueType, NodeType, TreeType, CollectionType> $node
     */
    public function afterAddNode(TreenodeAbstractInterface $node): void
    {
    }
}
