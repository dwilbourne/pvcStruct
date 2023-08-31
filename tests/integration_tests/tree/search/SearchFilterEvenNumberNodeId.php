<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;

/**
 * Class SearchFilterEvenNumberNodeId
 */
class SearchFilterEvenNumberNodeId implements SearchFilterInterface
{

    /**
     * testNode
     * returns true if the nodeId is an even number
     * @param TreenodeAbstractInterface $node
     * @return bool
     */
    public function testNode(TreenodeAbstractInterface $node): bool
    {
        return ($node->getNodeId() % 2 == 0);
    }
}
