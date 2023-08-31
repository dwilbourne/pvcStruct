<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;

/**
 * Class CollectionTestingTrait
 */
trait CollectionTestingTrait
{
    /**
     * @var string
     */
    protected string $keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * addElements
     * @param int $n
     * @param CollectionAbstractInterface $collection
     * @param int $stringLength
     * @throws \Exception
     */
    protected function addToCollection(int $n, CollectionAbstractInterface $collection, int $stringLength = 10): void
    {
        for ($i = 0; $i < $n; $i++) {
            $this->arrStrings[$i] = $this->randomString($stringLength);
            $collection->push($this->arrStrings[$i]);
        }
    }

    /**
     * randomString
     * @param int $length
     * @return string
     * @throws \Exception
     */
    protected function randomString(int $length = 64): string
    {
        $pieces = [];
        $max = mb_strlen($this->keySpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces [] = $this->keySpace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
