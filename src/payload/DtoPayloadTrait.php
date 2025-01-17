<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\payload;

use pvc\interfaces\struct\payload\HasPayloadInterface;

/**
 * Class DtoPayloadTrait
 * @template PayloadType of HasPayloadInterface
 */
trait DtoPayloadTrait
{
    /**
     * @var PayloadType|null
     */
    protected mixed $payload;

    /**
     * getPayload
     * @return PayloadType|null
     */
    public function getPayload()
    {
        return $this->payload ?? null;
    }

    /**
     * setPayload
     * @param PayloadType|null $payload
     */
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }
}
