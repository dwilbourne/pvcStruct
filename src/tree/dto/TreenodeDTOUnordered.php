<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOUnorderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;
use pvc\storage\dto\DTOTrait;

/**
 * Class TreenodeDTOUnordered
 * @template PayloadType of HasPayloadInterface
 * @implements TreenodeDTOUnorderedInterface<PayloadType>
 */
readonly class TreenodeDTOUnordered implements TreenodeDTOUnorderedInterface
{
    use DTOTrait;

    public int $nodeId;
    public ?int $parentId;
    /**
     * @var int|null
     * dto is allowed to have a null treeId.  The node hydration method takes two arguments: the first is this dto
     * and the second is the containing tree (because the node keeps a reference to its containing tree).  If this
     * dto has a non-null treeid, then the treeid value of this dto is compared to the treeid of the containing
     * tree to ensure they are the same.  But if this dto's treeid is null, the node hydration method will use the
     * treeid from the containing tree.
     */
    public ?int $treeId;
    public mixed $payload;

    /**
     * hydrateFromNode
     * @phpcs:ignore
     * @param TreenodeAbstractInterface<PayloadType, TreenodeUnorderedInterface, TreeUnorderedInterface, CollectionUnorderedInterface, TreenodeDTOUnorderedInterface> $node
     */
    public function hydrateFromNode(TreenodeAbstractInterface $node): void
    {
        $this->nodeId = $node->getNodeId();
        $this->parentId = $node->getParentId();
        $this->treeId = $node->getTreeId();
        $this->payload = $node->getPayload();
    }
}
