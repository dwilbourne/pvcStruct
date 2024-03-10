<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node_value_object;

use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectUnorderedInterface;

/**
 * Class TreenodeValueObjectUnordered
 * @template ValueType
 * @extends TreenodeValueObjectAbstract<ValueType>
 * @implements TreenodeValueObjectUnorderedInterface<ValueType>
 */
class TreenodeValueObjectUnordered extends TreenodeValueObjectAbstract implements TreenodeValueObjectUnorderedInterface
{
    /**
     * hydrateFromNode
     * @param TreenodeUnorderedInterface<ValueType> $node
     */
    public function hydrateFromNode(TreenodeUnorderedInterface $node): void
    {
        $this->setNodeId($node->getNodeId());
        $this->setParentId($node->getParentId());
        $this->setTreeId($node->getTreeId());
        $this->setValue($node->getValue());
    }
}
