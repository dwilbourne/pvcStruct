<?php

namespace pvcTests\struct\unit_tests\collection\fixtures;

use pvc\interfaces\struct\collection\IndexedElementInterface;

class IndexedElement extends Element
    implements IndexedElementInterface
{
    /**
     * @var non-negative-int
     */
    protected int $index = 0;

    /**
     * @return non-negative-int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @param  non-negative-int  $index
     *
     * @return void
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }
}