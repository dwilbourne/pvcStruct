<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\payload;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;

/**
 * Class PayloadValidatorTrait
 * @template PayloadType of HasPayloadInterface
 */
trait PayloadTesterTrait
{
    /**
     * @var PayloadTesterInterface<PayloadType>
     */
    protected PayloadTesterInterface $tester;

    /**
     * setPayloadTester
     * @param PayloadTesterInterface<PayloadType>|null $tester
     */
    public function setPayloadTester(PayloadTesterInterface|null $tester): void
    {
        if ($tester) {
            $this->tester = $tester;
        }
    }

    /**
     * getPayloadTester
     * @return PayloadTesterInterface<PayloadType>|null
     */
    public function getPayloadTester(): ?PayloadTesterInterface
    {
        return $this->tester ?? null;
    }
}
