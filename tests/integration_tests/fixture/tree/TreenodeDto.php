<?php

namespace pvcTests\struct\integration_tests\fixture\tree;

use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;

/**
 * @implements TreenodeDtoInterface<non-negative-int, non-negative-int>
 */
class TreenodeDto implements TreenodeDtoInterface
{
    /**
     * @var non-negative-int
     */
    protected int $nodeId;

    /**
     * @var non-negative-int|null
     */
    protected int|null $parentId;

    /**
     * @var non-negative-int|null
     */
    protected int $treeId;

    /**
     * @var non-negative-int
     */
    protected int $index;

    public function getNodeId()
    {
        return $this->nodeId;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function getTreeId()
    {
        return $this->treeId;
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}