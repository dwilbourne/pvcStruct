<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto\factory;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\factory\TreenodeDTOOrderedFactoryInterface;
use pvc\struct\tree\dto\TreenodeDTOOrdered;

/**
 * Class TreenodeDTOOrderedFactory
 * @template PayloadType of HasPayloadInterface
 * @implements TreenodeDTOOrderedFactoryInterface<PayloadType>
 */
class TreenodeDTOOrderedFactory implements TreenodeDTOOrderedFactoryInterface
{
    /**
     * makeDTO
     * @return TreenodeDTOOrdered<PayloadType>
     */
    public function makeDTO(): TreenodeDTOOrdered
    {
        return new TreenodeDTOOrdered();
    }
}
