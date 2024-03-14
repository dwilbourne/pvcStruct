<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\payload;

use pvc\interfaces\struct\payload\HasPayloadInterface;

/**
 * Class ValueObjectPayloadTrait
 * @template PayloadType of HasPayloadInterface
 */
trait ValueObjectPayloadTrait
{
    /**
     * @var PayloadType|null
     */
    protected $payload;

    /**
     * getPayload
     * @return PayloadType|null
     */
    public function getPayload()
    {
        return $this->payload;
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
