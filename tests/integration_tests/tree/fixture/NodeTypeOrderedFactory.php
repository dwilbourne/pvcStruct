<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\integration_tests\tree\fixture;

use pvc\interfaces\struct\collection\CollectionAbstractInterface as CollectionType;
use pvc\interfaces\struct\tree\factory\NodeTypeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\node\TreenodeOrdered;

/**
 * Class NodeTypeOrderedFactory
 */
class NodeTypeOrderedFactory implements NodeTypeFactoryInterface
{

    /**
     * makeNodeType
     * @param TreenodeValueObjectInterface $valueObject
     * @param TreeAbstractInterface $tree
     * @param CollectionType $collectionAbstract
     * @return TreenodeAbstractInterface
     */
    public function makeNodeType(
        TreenodeValueObjectInterface $valueObject,
        TreeAbstractInterface $tree,
        CollectionType $collectionAbstract
    ): TreenodeAbstractInterface {
        $nodeId = $valueObject->getNodeId();
        $parentId = $valueObject->getParentId();
        $treeId = $valueObject->getTreeId();
        $index = $valueObject->getIndex();
        return new TreenodeOrdered($nodeId, $parentId, $treeId, $index, $tree, $collectionAbstract);
    }
}
