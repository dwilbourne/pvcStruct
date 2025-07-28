<?php

namespace pvcTests\struct\unit_tests\collection\fixtures;

class CollectionElement
{
    protected int $value;

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param  int  $value
     *
     * @return void
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }

}