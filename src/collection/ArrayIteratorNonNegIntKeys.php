<?php

namespace pvc\struct\collection;

use ArrayIterator;

/**
 * @template ElementType
 * @extends ArrayIterator<non-negative-int, ElementType>
 */
class ArrayIteratorNonNegIntKeys extends ArrayIterator
{
    /**
     * @param  array<non-negative-int, ElementType>  $array
     */
    public function __construct(array $array)
    {
        parent::__construct($array);
    }
}