<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\fixture;

use pvc\struct\tree\node\TreenodeUnordered;

/**
 * Class TreenodeTestingFixtureUnordered
 */
class TreenodeTestingFixtureUnordered extends TreenodeTestingFixtureAbstract
{
    public function makeNodes(): void
    {
        /**
         * make the nodes
         */
        $this->root = new TreenodeUnordered(
            $this->rootNodeId,
            null,
            $this->treeId,
            $this->mockTree,
            $this->children
        );

        $this->child = new TreenodeUnordered(
            $this->childNodeId,
            $this->rootNodeId,
            $this->treeId,
            $this->mockTree,
            $this->grandChildren
        );
        $this->grandChild = new TreenodeUnordered(
            $this->grandChildNodeid,
            $this->childNodeId,
            $this->treeId,
            $this->mockTree,
            $this->greatGrandChildren
        );
    }

}