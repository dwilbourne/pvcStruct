<?php

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\collection\fixtures;

class CollectionElementFactory
{
    protected int $skewMultiplier = 3;
    protected int $skewStartValue = 2;
    protected int $valueOffset = 10;

    /**
     * @param non-negative-int $n
     * @return array<CollectionElement>
     * the indices are purposefully skewed to test that CollectionIndexed properly reindexes
     * the elements of the collection
     */
    public function makeCollectionElementArray(int $n): array
    {
        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[$i] = $this->makeElement($i);
        }
        return $result;
    }

    public function makeElement(int $key): CollectionElement
    {
        $element = new CollectionElement();
        $element->setValue($this->calculateValueFromKey($key));
        /**
         * start at 2 and ascend in increments of $skewMultiplier
         */
        $element->setIndex($key  * $this->skewMultiplier + $this->skewStartValue);
        return $element;
    }

    public function calculateValueFromKey(int $key): int
    {
        return $key + $this->valueOffset;
    }
}