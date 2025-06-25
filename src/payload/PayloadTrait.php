<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\payload;

use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\tree\err\InvalidValueException;

/**
 * Class PayloadTrait
 * @template PayloadType
 */
trait PayloadTrait
{
    /**
     * @var PayloadType
     */
    protected mixed $payload;

    /**
     * @var ValTesterInterface<PayloadType>
     */
    protected ValTesterInterface $tester;

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
     * @param PayloadType $payload
     * @throws InvalidValueException
     */
    public function setPayload(mixed $payload): void
    {
        if (!$this->getPayloadTester()->testValue($payload)) {
            throw new InvalidValueException();
        }
        $this->payload = $payload;
    }

    /**
     * setPayloadTester
     * @param ValTesterInterface<PayloadType> $tester
     */
    public function setPayloadTester(ValTesterInterface $tester): void
    {
        $this->tester = $tester;
    }

    /**
     * getPayloadTester
     * @return ValTesterInterface<PayloadType>
     */
    public function getPayloadTester(): ValTesterInterface
    {
        $alwaysTrue = new class implements ValTesterInterface {
            public function testValue(mixed $value): bool
            {
                return true;
            }
        };
        return $this->tester ?? $alwaysTrue;
    }
}
