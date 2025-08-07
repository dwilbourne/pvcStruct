<?php

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\collection\fixtures;

class IndexedElementFactory
{
    /**
     * @var non-negative-int
     */
    protected int $skewMultiplier = 3;

    /**
     * @var non-negative-int
     */
    protected int $skewStartValue = 2;

    /**
     * @param  non-negative-int  $n
     *
     * @return array<IndexedElement>
     * the indices are purposefully skewed to test that CollectionIndexed properly reindexes
     * the elements of the collection
     */
    public function makeElementArray(int $n): array
    {
        $result = [];
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < $n; $i++) {
            $pos = $i % strlen($letters);
            $value = $letters[$pos];
            $index = $this->skewStartValue + ($i * $this->skewMultiplier);
            $result[$i] = $this->makeIndexedElement($value, $index);
        }
        return $result;
    }

    public function makeIndexedElement(string $value, int $index): IndexedElement {
        $element = new IndexedElement();
        $element->setValue($value);
        $element->setIndex($index);
        return $element;
    }
}