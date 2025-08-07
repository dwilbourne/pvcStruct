<?php

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\collection\fixtures;

class ElementFactory
{
    /**
     * @param  non-negative-int  $n
     *
     * @return array<Element>
     */
    public function makeElementArray(int $n): array
    {
        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[$i] = $this->makeElement($i);
        }
        return $result;
    }

    public function makeElement(int $key): Element
    {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $element = new Element();
        $pos = $key % strlen($letters);
        $element->setValue($letters[$pos]);
        return $element;
    }
}