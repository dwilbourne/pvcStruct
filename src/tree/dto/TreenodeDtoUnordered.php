<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto;

use pvc\interfaces\struct\dto\DtoInterface;

/**
 * @template PayloadType
 */
readonly class TreenodeDtoUnordered implements DtoInterface
{
    public function __construct(
        /**
         * @var non-negative-int
         */
        public int   $nodeId,

        /**
         * @var non-negative-int|null
         */
        public ?int  $parentId,

        /**
         * @var non-negative-int|null
         * dto is allowed to have a null treeId.  The node hydration method takes two arguments: the first is this dto
         * and the second is the containing tree (because the node keeps a reference to its containing tree).  If this
         * dto has a non-null treeid, then the treeid value of this dto is compared to the treeid of the containing
         * tree to ensure they are the same.  But if this dto's treeid is null, the node hydration method will use the
         * treeid from the containing tree.
         */
        public ?int  $treeId,

        /**
         * @var mixed
         */
        public mixed $payload,
    )
    {
    }
}
