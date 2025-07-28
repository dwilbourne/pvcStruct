<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class NodeNotInTreeException
 */
class NodeNotInTreeException extends LogicException
{
    public function __construct(
        ?int $treeid,
        int $nodeid,
        ?Throwable $prev = null
    ) {
        /**
         * it is possible for nodes to be created without having a treeid set
         */
        $treeidString = (is_null($treeid) ? '{treeid not set}'
            : (string)$treeid);
        parent::__construct($treeidString, $nodeid, $prev);
    }
}
