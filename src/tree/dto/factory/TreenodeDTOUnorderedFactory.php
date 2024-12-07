<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto\factory;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\factory\TreenodeDTOUnorderedFactoryInterface;
use pvc\struct\tree\dto\TreenodeDTOUnordered;

/**
 * Class TreenodeDTOUnorderedFactory
 * @template PayloadType of HasPayloadInterface
 * @implements TreenodeDTOUnorderedFactoryInterface<PayloadType>
 */
class TreenodeDTOUnorderedFactory implements TreenodeDTOUnorderedFactoryInterface
{

    /**
     * makeDTO
     * @return TreenodeDTOUnordered<PayloadType>
     */
    public function makeDTO(): TreenodeDTOUnordered
    {
        return new TreenodeDTOUnordered();
    }
}
