<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;

/**
 * Class SearchFilterDefault
 * @template NodeType of TreenodeAbstractInterface
 * @implements SearchFilterInterface<NodeType>
 */
class SearchFilterDefault implements SearchFilterInterface
{

    /**
     * testNode
     * @param NodeType $node
     * @return bool
     */
    public function testNode(TreenodeAbstractInterface $node): bool
    {
        return true;
    }
}
