<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node_value_object;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\struct\payload\ValueObjectPayloadTrait;

/**
 * Class TreenodeValueObjectAbstract
 * @template PayloadType of HasPayloadInterface
 * @implements TreenodeValueObjectInterface<PayloadType>
 */
abstract class TreenodeValueObjectAbstract implements TreenodeValueObjectInterface
{
    /**
     * @use ValueObjectPayloadTrait<PayloadType>
     */
    use ValueObjectPayloadTrait;

    /**
     * @var non-negative-int
     */
    protected int $nodeId;

    /**
     * @var non-negative-int|null
     */
    protected ?int $parentId;

    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * getNodeId
     * @return non-negative-int
     */
    public function getNodeId(): int
    {
        return $this->nodeId;
    }

    /**
     * setNodeId
     * @param non-negative-int $nodeId
     */
    public function setNodeId(int $nodeId): void
    {
        $this->nodeId = $nodeId;
    }

    /**
     * getParentId
     * @return non-negative-int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * setParentId
     * @param non-negative-int|null $parentId
     */
    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * getTreeId
     * @return non-negative-int
     */
    public function getTreeId(): int
    {
        return $this->treeId;
    }

    /**
     * setTreeId
     * @param non-negative-int $treeId
     */
    public function setTreeId(int $treeId): void
    {
        $this->treeId = $treeId;
    }
}
