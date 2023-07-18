<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\range_collection;

use pvc\interfaces\struct\range_collection\RangeCollectionInterface;
use pvc\interfaces\struct\range_collection\RangeCollectionTypeManagerInterface;

/**
 * Class RangeCollection
 * @template RangeDataType
 * @implements RangeCollectionInterface<RangeDataType>
 */
class RangeCollection implements RangeCollectionInterface
{
    /**
     * @var RangeCollectionTypeManagerInterface<RangeDataType>
     */
    protected RangeCollectionTypeManagerInterface $typeManager;

    /**
     * @var array<array<RangeDataType>>
     */
    protected array $rangeElements = [];

	/**
	 * @param RangeCollectionTypeManagerInterface<RangeDataType> $typeManager
	 */
    public function __construct(RangeCollectionTypeManagerInterface $typeManager)
    {
        $this->setTypeManager($typeManager);
    }

    /**
     * @param RangeCollectionTypeManagerInterface<RangeDataType> $typeManager
     */
    public function setTypeManager(RangeCollectionTypeManagerInterface $typeManager): void
    {
        $this->typeManager = $typeManager;
    }

    /**
     * @return RangeCollectionTypeManagerInterface<RangeDataType>
     */
    public function getTypeManager(): RangeCollectionTypeManagerInterface
    {
        return $this->typeManager;
    }

    /**
     * addRange
     * @param RangeDataType $x
     * @param RangeDataType $y
     * @return bool
     */
    public function addRange($x, $y = null) : bool
    {
        $y ??= $x;

        if ($this->typeManager->validateDataType($x) && $this->typeManager->validateDataType($y)) {
	        /**
	         * -1 if $x < $y, 0 if $x == $y, 1 if $x > $y
	         */
			$comparison = $this->typeManager->compareData($x, $y);
            $this->rangeElements[] = ($comparison <= 0) ? [$x, $y] : [$y, $x];
            return true;
        } else {
            return false;
        }
    }

    /**
     * getRanges
     * @return array<array<RangeDataType>>
     */
    public function getRanges() : array
    {
        $sortFunction = function ($x, $y) {
            return $this->typeManager->compareData($x[0], $y[0]);
        };
        usort($this->rangeElements, $sortFunction);
        return $this->rangeElements;
    }
}
