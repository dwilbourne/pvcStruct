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
     * @use PayloadValidatorTrait<PayloadType>
     */
    use PayloadValidatorTrait;

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
    public function setPayload($payload): void
    {
        if ($this->getPayloadValidator() && !$this->getPayloadValidator()->validate($payload)) {
            throw new InvalidValueException();
        }
        $this->payload = $payload;
    }
}
