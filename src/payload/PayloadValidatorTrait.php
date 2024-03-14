<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\payload;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;

/**
 * Class PayloadValidatorTrait
 * @template PayloadType of HasPayloadInterface
 */
trait PayloadValidatorTrait
{
    /**
     * @var ValidatorPayloadInterface<PayloadType>
     */
    protected ValidatorPayloadInterface $validator;

    /**
     * setPayloadValidator
     * @param ValidatorPayloadInterface<PayloadType> $validator
     */
    public function setPayloadValidator(ValidatorPayloadInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * getPayloadValidator
     * @return ValidatorPayloadInterface<PayloadType>|null
     */
    public function getPayloadValidator(): ?ValidatorPayloadInterface
    {
        return $this->validator ?? null;
    }
}
