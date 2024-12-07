<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto\factory;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\tree\dto\TreenodeDTOUnordered;

/**
 * Class TreenodeDTOUnorderedFactory
 * @template PayloadType of HasPayloadInterface
 */
class TreenodeDTOUnorderedFactory
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
