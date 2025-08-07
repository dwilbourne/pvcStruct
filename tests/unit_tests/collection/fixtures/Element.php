<?php

namespace pvcTests\struct\unit_tests\collection\fixtures;

class Element
{
    protected string $value;

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param  string  $value
     *
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

}