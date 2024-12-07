<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\storage\dto\DTOTrait;

/**
 * Class TreenodeDTOOrdered
 * @template PayloadType of HasPayloadInterface
 * @implements TreenodeDTOOrderedInterface<PayloadType>
 */
readonly class TreenodeDTOOrdered implements TreenodeDTOOrderedInterface
{
    use DTOTrait;

    public int $nodeId;
    public ?int $parentId;
    public int $treeId;
    public mixed $payload;
    public int $index;

    /**
     * hydrateFromNode
     * @phpcs:ignore-next-line
     * @param TreenodeAbstractInterface<PayloadType, TreenodeOrderedInterface, TreenodeOrderedInterface, CollectionOrderedInterface, TreenodeDTOOrderedInterface> $node
     */
    public function hydrateFromNode(TreenodeAbstractInterface $node): void
    {
        $this->nodeId = $node->getNodeId();
        $this->parentId = $node->getParentId();
        $this->treeId = $node->getTreeId();
        $this->payload = $node->getPayload();
        $this->index = $node->getIndex();
    }
}
