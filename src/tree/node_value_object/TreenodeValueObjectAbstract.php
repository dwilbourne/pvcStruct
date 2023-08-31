<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node_value_object;

use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;

/**
 * Class TreenodeValueObjectAbstract
 * @template ValueType
 * @implements TreenodeValueObjectInterface<ValueType>
 */
abstract class TreenodeValueObjectAbstract implements TreenodeValueObjectInterface
{

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
     * @var ValueType $value
     */
    protected mixed $value;

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

    /**
     * getValue
     * @return ValueType
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * setValue
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
