<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\payload;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\tree\err\InvalidValueException;

/**
 * Class PayloadTrait
 * @template PayloadType of HasPayloadInterface
 */
trait PayloadTrait
{
    /**
     * @use PayloadTesterTrait<PayloadType>
     */
    use PayloadTesterTrait;

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
        return $this->payload ?? null;
    }

    /**
     * setPayload
     * @param PayloadType|null $payload
     * @throws InvalidValueException
     */
    public function setPayload(mixed $payload): void
    {
        if ($this->getPayloadTester() && !is_null($payload) && !$this->getPayloadTester()->testValue($payload)) {
            throw new InvalidValueException();
        }
        $this->payload = $payload;
    }
}
