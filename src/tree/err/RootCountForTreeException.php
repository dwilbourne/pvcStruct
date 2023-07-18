<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\tree\err;


use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class RootCountForTreeException
 */
class RootCountForTreeException extends LogicException
{
    public function __construct(int $rootCount, Throwable $prev = null)
    {
        parent::__construct($rootCount, $prev);
    }

}
