<?php

declare(strict_types=1);

namespace pvc\struct\tree\dto;

readonly class TreenodeDtoOrdered extends TreenodeDto
{
    /**
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @param non-negative-int|null $treeId
     * @param non-negative-int $index
     */
    public function __construct(
        int   $nodeId,
        ?int  $parentId,
        ?int  $treeId,
        public int   $index,
    )
    {
        parent::__construct($nodeId, $parentId, $treeId);
    }
}
