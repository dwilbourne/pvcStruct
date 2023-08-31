<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\fixture;

use pvc\struct\tree\node\TreenodeOrdered;

/**
 * Class TreenodeTestingFixtureOrdered
 */
class TreenodeTestingFixtureOrdered extends TreenodeTestingFixtureAbstract
{


    public function createCollectionMocks(string $classString): void
    {
        parent::createCollectionMocks($classString);
        $this->setChildrenSetIndexExpectations();
    }

    public function setChildrenSetIndexExpectations(): void
    {
        $mockKey = 0;

        $this->rootSiblingsCollection->method('getKey')->willReturn($mockKey);
        $this->children->method('getKey')->willReturn($mockKey);
        $this->grandChildren->method('getKey')->willReturn($mockKey);

        $this->rootSiblingsCollection->expects($this->once())->method('setIndex');
        $this->children->expects($this->once())->method('setIndex');
        $this->grandChildren->expects($this->once())->method('setIndex');
    }

    public function makeNodes(): void
    {
        /**
         * make the nodes
         */
        $this->root = new TreenodeOrdered(
            $this->rootNodeId,
            null,
            $this->treeId,
            0,
            $this->mockTree,
            $this->children
        );

        $this->child = new TreenodeOrdered(
            $this->childNodeId,
            $this->rootNodeId,
            $this->treeId,
            0,
            $this->mockTree,
            $this->grandChildren
        );
        $this->grandChild = new TreenodeOrdered(
            $this->grandChildNodeid,
            $this->childNodeId,
            $this->treeId,
            0,
            $this->mockTree,
            $this->greatGrandChildren
        );
    }
}
