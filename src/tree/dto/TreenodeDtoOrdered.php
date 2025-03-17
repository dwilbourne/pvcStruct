<?php

declare(strict_types=1);

namespace pvc\struct\tree\dto;

/**
 * @template PayloadType
 * @extends TreenodeDtoUnordered<PayloadType>
 */
readonly class TreenodeDtoOrdered extends TreenodeDtoUnordered
{
    /**
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @param non-negative-int|null $treeId
     * @param mixed $payload
     * @param int $index
     */
    public function __construct(
        int   $nodeId,
        ?int  $parentId,
        ?int  $treeId,
        mixed $payload,

        /**
         * @var non-negative-int
         */
        public int   $index,
    )
    {
        parent::__construct($nodeId, $parentId, $treeId, $payload);
    }
}
