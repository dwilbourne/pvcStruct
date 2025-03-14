<?php

namespace pvcTests\struct\unit_tests\collection\fixtures;

use pvc\interfaces\struct\collection\CollectionElementInterface;

class CollectionElement implements CollectionElementInterface
{
    /**
     * @var non-negative-int
     */
    protected int $index = 0;

    protected int $value;

    /**
     * @return non-negative-int
     */
    public function getIndex(): int {
        return $this->index;
    }

    /**
     * @param non-negative-int $index
     * @return void
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return void
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }


}